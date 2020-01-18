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
use BrowserDetector\Loader\NotFoundException;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

final class BrowscapSource implements SourceInterface
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
        return 'browscap/browscap';
    }

    /**
     * @throws \LogicException
     *
     * @return array[]|iterable
     */
    public function getHeaders(): iterable
    {
        foreach ($this->loadFromPath() as $row) {
            $ua    = UserAgent::fromUseragent(trim($row['ua']));
            $agent = (string) $ua;

            if (empty($agent)) {
                continue;
            }

            yield $ua->getHeaders();
        }
    }

    /**
     * @throws \LogicException
     *
     * @return array[]|iterable
     */
    public function getProperties(): iterable
    {
        foreach ($this->loadFromPath() as $row) {
            $ua    = UserAgent::fromUseragent(trim($row['ua']));
            $agent = (string) $ua;

            if (empty($agent)) {
                continue;
            }

            $deviceTypeLoader = new \UaDeviceType\TypeLoader();

            try {
                $deviceType = $deviceTypeLoader->load($row['properties']['Device_Type'] ?? '');

                $isMobile = $deviceType->isMobile();
                $type1    = $deviceType->getType();
            } catch (NotFoundException $e) {
                $this->output->writeln('', OutputInterface::VERBOSITY_VERBOSE);
                $this->output->writeln(
                    '<info>' . $e . '</info>'
                );

                $isMobile = null;
                $type1    = null;

                try {
                    $deviceReflection = new \ReflectionClass($deviceTypeLoader);

                    foreach ($deviceReflection->getConstant('OPTIONS') as $type => $className) {
                        /** @var \UaDeviceType\TypeInterface $class */
                        $class = new $className();

                        if ($row['properties']['Device_Type'] ?? '' !== $class->getType()) {
                            continue;
                        }

                        $isMobile = $class->isMobile();
                        $type1    = $class->getType();
                    }
                } catch (\ReflectionException $e) {
                    $this->output->writeln('', OutputInterface::VERBOSITY_VERBOSE);
                    $this->output->writeln(
                        '<error>' . $e . '</error>'
                    );
                }
            }

            $browserTypeLoader = new \UaBrowserType\TypeLoader();

            try {
                $browserType = $browserTypeLoader->load($row['properties']['Browser_Type'] ?? '');

                $isBot = $browserType->isBot();
                $type2 = $browserType->getType();
            } catch (NotFoundException $e) {
                $this->output->writeln('', OutputInterface::VERBOSITY_VERBOSE);
                $this->output->writeln(
                    '<info>' . $e . '</info>'
                );

                $isBot = null;
                $type2 = null;

                try {
                    $browserReflection = new \ReflectionClass($browserTypeLoader);

                    foreach ($browserReflection->getConstant('OPTIONS') as $type => $className) {
                        /** @var \UaBrowserType\TypeInterface $class */
                        $class = new $className();

                        if ($row['properties']['Browser_Type'] ?? '' !== $class->getType()) {
                            continue;
                        }

                        $isBot = $class->isBot();
                        $type2 = $class->getType();
                    }
                } catch (\ReflectionException $e) {
                    $this->output->writeln('', OutputInterface::VERBOSITY_VERBOSE);
                    $this->output->writeln(
                        '<error>' . $e . '</error>'
                    );
                }
            }

            $pointingMethod = $row['properties']['Device_Pointing_Method'] ?? null;

            yield $agent => [
                'device' => [
                    'deviceName' => $row['properties']['Device_Code_Name'] ?? null,
                    'marketingName' => $row['properties']['Device_Name'] ?? null,
                    'manufacturer' => $row['properties']['Device_Maker'] ?? null,
                    'brand' => $row['properties']['Device_Brand_Name'] ?? null,
                    'display' => [
                        'width' => null,
                        'height' => null,
                        'touch' => ('touchscreen' === $pointingMethod),
                        'type' => null,
                        'size' => null,
                    ],
                    'dualOrientation' => null,
                    'type' => $type1,
                    'simCount' => null,
                    'market' => [
                        'regions' => null,
                        'countries' => null,
                        'vendors' => null,
                    ],
                    'connections' => null,
                    'ismobile' => $isMobile,
                ],
                'browser' => [
                    'name' => $row['properties']['Browser'] ?? null,
                    'modus' => $row['properties']['Browser_Modus'] ?? null,
                    'version' => $row['properties']['Version'] ?? null,
                    'manufacturer' => $row['properties']['Browser_Maker'] ?? null,
                    'bits' => $row['properties']['Browser_Bits'] ?? null,
                    'type' => $type2,
                    'isbot' => $isBot,
                ],
                'platform' => [
                    'name' => $row['properties']['Platform'] ?? null,
                    'marketingName' => null,
                    'version' => $row['properties']['Platform_Version'] ?? null,
                    'manufacturer' => $row['properties']['Platform_Maker'] ?? null,
                    'bits' => $row['properties']['Platform_Bits'] ?? null,
                ],
                'engine' => [
                    'name' => $row['properties']['RenderingEngine_Name'] ?? null,
                    'version' => $row['properties']['RenderingEngine_Version'] ?? null,
                    'manufacturer' => $row['properties']['RenderingEngine_Maker'] ?? null,
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
        $path = 'vendor/browscap/browscap/tests/issues';

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
        $finder->name('*.php');
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

            $data = include $filepath;

            if (!is_array($data)) {
                continue;
            }

            foreach ($data as $row) {
                if (!array_key_exists('ua', $row)) {
                    continue;
                }

                yield $row;
            }
        }

        $this->output->writeln('', OutputInterface::VERBOSITY_VERBOSE);
        $this->output->writeln("\r" . '<info>' . str_pad('done', $messageLength, ' ', STR_PAD_RIGHT) . '</info>', OutputInterface::VERBOSITY_VERBOSE);
    }
}
