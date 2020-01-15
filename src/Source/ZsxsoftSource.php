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
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

final class ZsxsoftSource implements SourceInterface
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
        return 'zsxsoft/php-useragent';
    }

    /**
     * @throws \LogicException
     *
     * @return array[]|iterable
     */
    public function getHeaders(): iterable
    {
        foreach ($this->loadFromPath() as $data) {
            $agent = trim($data[0][0]);

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
        $brands = $this->getBrands();

        foreach ($this->loadFromPath() as $data) {
            $agent = trim($data[0][0]);

            $ua    = UserAgent::fromUseragent($agent);
            $agent = (string) $ua;

            if (empty($agent)) {
                continue;
            }

            $model = '';

            foreach ($brands as $brand) {
                if (false !== mb_strpos($data[1][8], $brand)) {
                    $model = trim(str_replace($brand, '', $data[1][8]));

                    break;
                }

                $brand = '';
            }

            yield $agent => [
                'device' => [
                    'deviceName' => $model,
                    'marketingName' => null,
                    'manufacturer' => null,
                    'brand' => $brand ?? '',
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
                    'name' => $data[1][2],
                    'modus' => null,
                    'version' => $data[1][3],
                    'manufacturer' => null,
                    'bits' => null,
                    'type' => null,
                    'isbot' => null,
                ],
                'platform' => [
                    'name' => $data[1][5],
                    'marketingName' => null,
                    'version' => $data[1][6],
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
     *
     * @return array[]|iterable
     */
    private function loadFromPath(): iterable
    {
        $path = 'vendor/zsxsoft/php-useragent/tests';

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
        $finder->name('UserAgentList.php');
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

            $provider = require $filepath;

            foreach ($provider as $data) {
                yield $data;
            }
        }

        $this->output->writeln('', OutputInterface::VERBOSITY_VERBOSE);
        $this->output->writeln("\r" . '<info>' . str_pad('done', $messageLength, ' ', STR_PAD_RIGHT) . '</info>', OutputInterface::VERBOSITY_VERBOSE);
    }

    /**
     * @throws \LogicException
     * @throws \RuntimeException
     *
     * @return string[]
     */
    private function getBrands(): array
    {
        $brands = [];
        $file   = new \SplFileObject('vendor/zsxsoft/php-useragent/lib/useragent_detect_device.php');
        $file->setFlags(\SplFileObject::DROP_NEW_LINE);
        while (!$file->eof()) {
            $line = trim($file->fgets());
            preg_match('/^\$brand = (["\'])(.*)(["\']);$/', $line, $matches);

            if (0 < count($matches)) {
                $brand = $matches[2];
                if (!empty($brand)) {
                    $brands[] = $brand;
                }
            }
        }
        $brands = array_unique($brands);

        usort($brands, static function ($a, $b): int {
            return mb_strlen($b) - mb_strlen($a);
        });

        return $brands;
    }
}
