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
use ExceptionalJSON\DecodeErrorException;
use JsonClass\Json;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

final class UaParserJsSource implements SourceInterface
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
        return 'ua-parser-js';
    }

    /**
     * @throws \LogicException
     * @throws \RuntimeException
     *
     * @return array[]|iterable
     */
    public function getHeaders(): iterable
    {
        foreach ($this->loadFromPath() as $providerName => $data) {
            $agent = trim($data['ua']);

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
        $agents = [];
        $base   = [
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
                'type' => null,
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
                'name' => null,
                'modus' => null,
                'version' => null,
                'manufacturer' => null,
                'bits' => null,
                'type' => null,
                'isbot' => null,
            ],
            'platform' => [
                'name' => null,
                'marketingName' => null,
                'version' => null,
                'manufacturer' => null,
                'bits' => null,
            ],
            'engine' => [
                'name' => null,
                'version' => null,
                'manufacturer' => null,
            ],
        ];

        foreach ($this->loadFromPath() as $providerName => $data) {
            $agent = trim($data['ua']);

            if (!isset($agents[$agent])) {
                $agents[$agent] = $base;
            }

            switch ($providerName) {
                case 'browser-test.json':
                    $agents[$agent]['browser']['name']    = 'undefined' === $data['expect']['name'] ? '' : $data['expect']['name'];
                    $agents[$agent]['browser']['version'] = 'undefined' === $data['expect']['version'] ? '' : $data['expect']['version'];

                    break;
                case 'device-test.json':
                    $agents[$agent]['device']['name']  = 'undefined' === $data['expect']['model'] ? '' : $data['expect']['model'];
                    $agents[$agent]['device']['brand'] = 'undefined' === $data['expect']['vendor'] ? '' : $data['expect']['vendor'];
                    $agents[$agent]['device']['type']  = 'undefined' === $data['expect']['type'] ? '' : $data['expect']['type'];

                    break;
                case 'os-test.json':
                    $agents[$agent]['platform']['name']    = 'undefined' === $data['expect']['name'] ? '' : $data['expect']['name'];
                    $agents[$agent]['platform']['version'] = 'undefined' === $data['expect']['version'] ? '' : $data['expect']['version'];

                    break;
                // Skipping cpu-test.json because we don't look at CPU data, which is all that file tests against
                // Skipping engine-test.json because we don't look at Engine data // @todo: fix
                // Skipping mediaplayer-test.json because it seems that this file isn't used in this project's actual tests (see test.js)
            }
        }

        foreach ($agents as $agent => $test) {
            $ua    = UserAgent::fromUseragent($agent);
            $agent = (string) $ua;

            if (empty($agent)) {
                continue;
            }

            yield $agent => $test;
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
        $path = 'node_modules/ua-parser-js/test';

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

            try {
                $provider = (new Json())->decode(
                    $file->getContents(),
                    true
                );
            } catch (DecodeErrorException $e) {
                $this->output->error(
                    new \Exception(sprintf('file %s contains invalid json.', $file->getPathname()), 0, $e)
                );
                continue;
            }

            if (!is_array($provider)) {
                continue;
            }

            $providerName = $file->getFilename();

            foreach ($provider as $data) {
                $agent = trim($data['ua']);

                if (empty($agent)) {
                    continue;
                }

                yield $providerName => $data;
            }
        }

        $this->output->writeln('', OutputInterface::VERBOSITY_VERBOSE);
        $this->output->writeln("\r" . '<info>' . str_pad('done', $messageLength, ' ', STR_PAD_RIGHT) . '</info>', OutputInterface::VERBOSITY_VERBOSE);
    }
}
