<?php
/**
 * This file is part of the browscap-helper package.
 *
 * Copyright (c) 2015-2020, Thomas Mueller <mimmi20@live.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);
namespace BrowscapHelper\Source;

use BrowscapHelper\Source\Ua\UserAgent;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Yaml;

final class WootheeSource implements SourceInterface
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
        return 'woothee/woothee-testset';
    }

    /**
     * @throws \LogicException
     * @throws \RuntimeException
     *
     * @return array[]|iterable
     */
    public function getHeaders(): iterable
    {
        foreach ($this->loadFromPath() as $agent) {
            $ua    = UserAgent::fromUseragent($agent);
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
        foreach ($this->loadFromPath() as $agent) {
            $ua    = UserAgent::fromUseragent($agent);
            $agent = (string) $ua;

            if (empty($agent)) {
                continue;
            }

            yield $agent => [
                'device' => [
                    'deviceName' => null,
                    'marketingName' => null,
                    'manufacturer' => null,
                    'brand' => null,
                    'display' => [
                        'width' => null,
                        'height' => null,
                        'touch' => null,
                        'type' => null,
                        'size' => null,
                    ],
                    'dualOrientation' => null,
                    'type' => $row['category'] ?? null,
                    'simCount' => null,
                    'market' => [
                        'regions' => null,
                        'countries' => null,
                        'vendors' => null,
                    ],
                    'connections' => null,
                    'ismobile' => null,
                ],
                'browser' => [
                    'name' => $row['name'] ?? null,
                    'modus' => null,
                    'version' => $row['version'] ?? null,
                    'manufacturer' => null,
                    'bits' => null,
                    'type' => null,
                    'isbot' => null,
                ],
                'platform' => [
                    'name' => $row['os'] ?? null,
                    'marketingName' => null,
                    'version' => $row['os_version'] ?? null,
                    'manufacturer' => null,
                    'bits' => null,
                ],
                'engine' => [
                    'name' => null,
                    'version' => null,
                    'manufacturer' => null,
                ],
            ];
        }
    }

    /**
     * @throws \LogicException
     * @throws \RuntimeException
     *
     * @return iterable|string[]
     */
    private function loadFromPath(): iterable
    {
        $path = 'vendor/woothee/woothee-testset/testsets';

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
        $finder->name('*.yaml');
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

            $data = Yaml::parse($file->getContents());

            if (!is_array($data)) {
                continue;
            }

            foreach ($data as $row) {
                if (!array_key_exists('target', $row) || empty($row['target'])) {
                    continue;
                }

                $agent = trim($row['target']);

                if (empty($agent)) {
                    continue;
                }

                yield $agent;
            }
        }

        $this->output->writeln('', OutputInterface::VERBOSITY_VERBOSE);
        $this->output->writeln("\r" . '<info>' . str_pad('done', $messageLength, ' ', STR_PAD_RIGHT) . '</info>', OutputInterface::VERBOSITY_VERBOSE);
    }
}
