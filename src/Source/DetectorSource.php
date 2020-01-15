<?php
/**
 * This file is part of the browscap-helper-source package.
 *
 * Copyright (c) 2016-2019, Thomas Mueller <mimmi20@live.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);
namespace BrowscapHelper\Source;

use BrowscapHelper\Source\Ua\UserAgent;
use ExceptionalJSON\DecodeErrorException;
use JsonClass\Json;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

final class DetectorSource implements SourceInterface
{
    use GetUserAgentsTrait;

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @param OutputInterface $output
     */
    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'mimmi20/browser-detector';
    }

    /**
     * @throws \LogicException
     * @throws \RuntimeException
     *
     * @return array[]|iterable
     */
    public function getHeaders(): iterable
    {
        foreach ($this->loadFromPath() as $test) {
            $ua    = UserAgent::fromHeaderArray($test['headers']);
            $agent = (string) $ua;

            if (empty($agent)) {
                continue;
            }

            yield $ua->getHeaders();
        }
    }

    /**
     * @throws \LogicException
     * @throws \RuntimeException
     *
     * @return array[]|iterable
     */
    public function getProperties(): iterable
    {
        foreach ($this->loadFromPath() as $test) {
            $ua    = UserAgent::fromHeaderArray($test['headers']);
            $agent = (string) $ua;

            if (empty($agent)) {
                continue;
            }

            yield $agent => [
                'device' => [
                    'deviceName' => $test['result']['device']['deviceName'],
                    'marketingName' => $test['result']['device']['marketingName'],
                    'manufacturer' => $test['result']['device']['manufacturer'],
                    'brand' => $test['result']['device']['brand'],
                    'display' => [
                        'width' => $test['result']['device']['display']['width'],
                        'height' => $test['result']['device']['display']['height'],
                        'touch' => $test['result']['device']['display']['touch'],
                        'type' => $test['result']['device']['display']['type'] ?? null,
                        'size' => $test['result']['device']['display']['size'],
                    ],
                    'dualOrientation' => $test['result']['device']['dualOrientation'] ?? null,
                    'type' => $test['result']['device']['type'],
                    'simCount' => $test['result']['device']['simCount'] ?? null,
                    'market' => [
                        'regions' => $test['result']['device']['market']['regions'] ?? null,
                        'countries' => $test['result']['device']['market']['countries'] ?? null,
                        'vendors' => $test['result']['device']['market']['vendors'] ?? null,
                    ],
                    'connections' => $test['result']['device']['connections'] ?? null,
                    'ismobile' => (new \UaDeviceType\TypeLoader())->load($test['result']['device']['type'])->isMobile(),
                ],
                'browser' => [
                    'name' => $test['result']['browser']['name'],
                    'modus' => $test['result']['browser']['modus'],
                    'version' => ('0.0.0' === $test['result']['browser']['version'] ? null : $test['result']['browser']['version']),
                    'manufacturer' => $test['result']['browser']['manufacturer'],
                    'bits' => $test['result']['browser']['bits'],
                    'type' => $test['result']['browser']['type'],
                    'isbot' => (new \UaBrowserType\TypeLoader())->load($test['result']['browser']['type'])->isBot(),
                ],
                'platform' => [
                    'name' => $test['result']['os']['name'],
                    'marketingName' => $test['result']['os']['marketingName'],
                    'version' => ('0.0.0' === $test['result']['os']['version'] ? null : $test['result']['os']['version']),
                    'manufacturer' => $test['result']['os']['manufacturer'],
                    'bits' => $test['result']['os']['bits'],
                ],
                'engine' => [
                    'name' => $test['result']['engine']['name'],
                    'version' => $test['result']['engine']['version'],
                    'manufacturer' => $test['result']['engine']['manufacturer'],
                ],
            ];
        }
    }

    /**
     * @throws \LogicException
     * @throws \RuntimeException
     *
     * @return array[]|iterable
     */
    private function loadFromPath(): iterable
    {
        $path = 'vendor/mimmi20/browser-detector/tests/data';

        if (!file_exists($path)) {
            $this->output->writeln('', OutputInterface::VERBOSITY_VERBOSE);
            $this->output->writeln(sprintf('<error>path %s not found</error>', $path), OutputInterface::VERBOSITY_NORMAL);

            return;
        }

        $messageLength = 0;

        $message = sprintf('reading path %s', $path);

        if (mb_strlen($message) > $messageLength) {
            $messageLength = mb_strlen($message);
        }

        $this->output->write("\r" . '<info>' . str_pad($message, $messageLength, ' ', STR_PAD_RIGHT) . '</info>', false, OutputInterface::VERBOSITY_VERBOSE);

        $finder = new Finder();
        $finder->files();
        $finder->name('*.json');
        $finder->ignoreDotFiles(true);
        $finder->ignoreVCS(true);
        $finder->sortByName();
        $finder->ignoreUnreadableDirs();
        $finder->in($path);

        foreach ($finder as $file) {
            /** @var \Symfony\Component\Finder\SplFileInfo $file */
            $filepath = $file->getPathname();

            $message = sprintf('reading file %s', $filepath);

            if (mb_strlen($message) > $messageLength) {
                $messageLength = mb_strlen($message);
            }

            $this->output->write("\r" . '<info>' . str_pad($message, $messageLength, ' ', STR_PAD_RIGHT) . '</info>', false, OutputInterface::VERBOSITY_VERY_VERBOSE);

            $content = $file->getContents();

            if ('' === $content || PHP_EOL === $content) {
                unlink($filepath);

                continue;
            }

            try {
                $data = (new Json())->decode(
                    $content,
                    true
                );
            } catch (DecodeErrorException $e) {
                $this->output->writeln('', OutputInterface::VERBOSITY_VERBOSE);
                $this->output->writeln('    <error>parsing file content [' . $filepath . '] failed</error>', OutputInterface::VERBOSITY_NORMAL);

                continue;
            }

            if (!is_array($data)) {
                continue;
            }

            foreach ($data as $test) {
                yield $test;
            }
        }

        $this->output->writeln('', OutputInterface::VERBOSITY_VERBOSE);
        $this->output->writeln("\r" . '<info>' . str_pad('done', $messageLength, ' ', STR_PAD_RIGHT) . '</info>', OutputInterface::VERBOSITY_VERBOSE);
    }
}
