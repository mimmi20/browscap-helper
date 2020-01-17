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
use http\Header;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Yaml;

final class WhichBrowserSource implements SourceInterface
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
        return 'whichbrowser/parser';
    }

    /**
     * @throws \LogicException
     * @throws \RuntimeException
     *
     * @return array[]|iterable
     */
    public function getHeaders(): iterable
    {
        foreach ($this->loadFromPath() as $row) {
            $lowerHeaders = [];

            foreach ($this->getHeadersFromRow($row) as $header => $value) {
                $lowerHeaders[mb_strtolower((string) $header)] = $value;
            }

            $ua    = UserAgent::fromHeaderArray($lowerHeaders);
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
        foreach ($this->loadFromPath() as $row) {
            $lowerHeaders = [];

            foreach ($this->getHeadersFromRow($row) as $header => $value) {
                $lowerHeaders[mb_strtolower((string) $header)] = $value;
            }

            $ua    = UserAgent::fromHeaderArray($lowerHeaders);
            $agent = (string) $ua;

            if (empty($agent)) {
                continue;
            }

            yield $agent => [
                'device' => [
                    'deviceName' => $row['device']['model'] ?? null,
                    'marketingName' => null,
                    'manufacturer' => null,
                    'brand' => $row['device']['manufacturer'] ?? null,
                    'display' => [
                        'width' => null,
                        'height' => null,
                        'touch' => null,
                        'type' => null,
                        'size' => null,
                    ],
                    'dualOrientation' => null,
                    'type' => $row['device']['type'] ?? null,
                    'simCount' => null,
                    'market' => [
                        'regions' => null,
                        'countries' => null,
                        'vendors' => null,
                    ],
                    'connections' => null,
                    'ismobile' => $this->isMobile($row) ? true : false,
                ],
                'browser' => [
                    'name' => $row['browser']['name'] ?? null,
                    'modus' => null,
                    'version' => (!empty($row['browser']['version']) ? is_array($row['browser']['version']) ? $row['browser']['version']['value'] : $row['browser']['version'] : null),
                    'manufacturer' => null,
                    'bits' => null,
                    'type' => null,
                    'isbot' => null,
                ],
                'platform' => [
                    'name' => $row['os']['name'] ?? null,
                    'marketingName' => null,
                    'version' => (!empty($row['os']['version']) ? is_array($row['os']['version']) ? $row['os']['version']['value'] : $row['os']['version'] : null),
                    'manufacturer' => null,
                    'bits' => null,
                ],
                'engine' => [
                    'name' => $row['engine']['name'] ?? null,
                    'version' => $row['engine']['version'] ?? null,
                    'manufacturer' => null,
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
        $path = 'vendor/whichbrowser/parser/tests/data';

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
                yield $row;
            }
        }

        $this->output->writeln('', OutputInterface::VERBOSITY_VERBOSE);
        $this->output->writeln("\r" . '<info>' . str_pad('done', $messageLength, ' ', STR_PAD_RIGHT) . '</info>', OutputInterface::VERBOSITY_VERBOSE);
    }

    /**
     * @param array $row
     *
     * @return array
     */
    private function getHeadersFromRow(array $row): array
    {
        $headers = [];

        if (array_key_exists('headers', $row)) {
            if (is_array($row['headers'])) {
                return $row['headers'];
            }

            if (class_exists(Header::class)) {
                // pecl_http versions 2.x/3.x
                $headers = Header::parse($row['headers']);
            } elseif (function_exists('\http_parse_headers')) {
                // pecl_http version 1.x
                $headers = \http_parse_headers($row['headers']);
            } elseif (0 === mb_strpos($row['headers'], 'User-Agent: ')) {
                $headers = ['user-agent' => str_replace('User-Agent: ', '', $row['headers'])];
            } else {
                return [];
            }
        }

        if (is_array($headers)) {
            return $headers;
        }

        return [];
    }

    /**
     * @param array $data
     *
     * @return bool
     */
    private function isMobile(array $data): bool
    {
        if (!isset($data['device']['type'])) {
            return false;
        }

        $mobileTypes = ['mobile', 'tablet', 'ereader', 'media', 'watch', 'camera'];

        if (in_array($data['device']['type'], $mobileTypes, true)) {
            return true;
        }

        if ('gaming' === $data['device']['type']) {
            if (isset($data['device']['subtype']) && 'portable' === $data['device']['subtype']) {
                return true;
            }
        }

        return false;
    }
}
