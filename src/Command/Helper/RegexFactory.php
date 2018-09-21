<?php
/**
 * This file is part of the browscap-helper package.
 *
 * Copyright (c) 2015-2018, Thomas Mueller <mimmi20@live.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);
namespace BrowscapHelper\Command\Helper;

use BrowscapHelper\Factory\Regex\GeneralBlackberryException;
use BrowscapHelper\Factory\Regex\GeneralDeviceException;
use BrowscapHelper\Factory\Regex\GeneralPhilipsTvException;
use BrowscapHelper\Factory\Regex\GeneralPhoneException;
use BrowscapHelper\Factory\Regex\GeneralTabletException;
use BrowscapHelper\Factory\Regex\NoMatchException;
use BrowserDetector\Factory;
use BrowserDetector\Loader\DeviceLoaderFactory;
use BrowserDetector\Loader\NotFoundException;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\InvalidArgumentException;
use Seld\JsonLint\ParsingException;
use Symfony\Component\Console\Helper\Helper;

/**
 * detection class using regexes
 *
 * @category  BrowserDetector
 */
class RegexFactory extends Helper
{
    /**
     * @var \Symfony\Component\Console\Helper\HelperSet
     */
    protected $helperSet;

    /**
     * @var array|null
     */
    private $match;

    /**
     * @var string|null
     */
    private $useragent;

    /**
     * an logger instance
     *
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var bool
     */
    private $runDetection = false;

    /**
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function getName()
    {
        return 'regex-factory';
    }

    /**
     * Gets the information about the rendering engine by User Agent
     *
     * @param string $useragent
     *
     * @throws \BrowserDetector\Loader\NotFoundException
     * @throws \InvalidArgumentException
     * @throws \BrowscapHelper\Factory\Regex\NoMatchException
     *
     * @return void
     */
    public function detect($useragent): void
    {
        $this->match     = null;
        $this->useragent = $useragent;
        /** @var \BrowscapHelper\Command\Helper\RegexLoader $regexLoader */
        $regexLoader = $this->helperSet->get('regex-loader');

        foreach ($regexLoader->getRegexes() as $regex) {
            $matches = [];

            if (preg_match($regex, $useragent, $matches)) {
                $this->match = $matches;

                $this->runDetection = true;

                return;
            }
        }

        $this->runDetection = true;

        throw new NoMatchException('no regex did match');
    }

    /**
     * @throws InvalidArgumentException
     *
     * @return array
     */
    public function getDevice(): array
    {
        if (null === $this->useragent) {
            throw new \InvalidArgumentException('no useragent was set');
        }

        if (!is_array($this->match) && $this->runDetection) {
            throw new NoMatchException('device not found via regexes');
        }

        if (!is_array($this->match)) {
            throw new \InvalidArgumentException('please call the detect function before trying to get the result');
        }

        if (!array_key_exists('devicecode', $this->match) || '' === $this->match['devicecode']) {
            throw new NoMatchException('device not detected via regexes');
        }

        $deviceCode          = mb_strtolower($this->match['devicecode']);
        $deviceLoaderFactory = new DeviceLoaderFactory($this->logger);

        if (!array_key_exists('osname', $this->match) || '' === $this->match['osname']) {
            $platformCode = null;
        } else {
            $platformCode = mb_strtolower($this->match['osname']);
        }

        if ('windows' === $deviceCode) {
            $deviceLoader = $deviceLoaderFactory('unknown', 'desktop');
            $deviceLoader->init();

            return $deviceLoader->load('windows desktop', $this->useragent);
        }

        if ('macintosh' === $deviceCode) {
            $deviceLoader = $deviceLoaderFactory('apple', 'desktop');
            $deviceLoader->init();

            return $deviceLoader->load('macintosh', $this->useragent);
        }

        if ('cfnetwork' === $deviceCode) {
            try {
                $factory = new Factory\Device\DarwinFactory($this->logger);

                return $factory($this->useragent);
            } catch (InvalidArgumentException | ParsingException $e) {
                throw new NotFoundException('not found', 0, $e);
            }
        }

        if (in_array($deviceCode, ['dalvik', 'android', 'opera/9.80', 'opera/9.50', 'generic'])
            && array_key_exists('osname', $this->match)
            && 'blackberry' === mb_strtolower($this->match['osname'])
        ) {
            throw new GeneralBlackberryException('use general mobile device');
        }

        if (in_array($deviceCode, ['dalvik', 'android'])) {
            if (array_key_exists('devicetype', $this->match)) {
                $deviceType = mb_strtolower($this->match['devicetype']);

                if ('tablet' === $deviceType) {
                    throw new GeneralTabletException('use general tablet');
                }
            }

            throw new GeneralPhoneException('use general mobile phone');
        }

        if (in_array($deviceCode, ['opera/9.80', 'opera/9.50', 'generic', ''])) {
            throw new GeneralDeviceException('use general mobile device');
        }

        if (in_array($deviceCode, ['at', 'ap', 'ip', 'it']) && 'linux' === $platformCode) {
            throw new GeneralDeviceException('use general mobile device');
        }

        if ('philipstv' === $deviceCode) {
            throw new GeneralPhilipsTvException('use general philips tv device');
        }

        if (in_array($deviceCode, ['4g lte', '3g', '709v82_jbla118', 'linux arm'])) {
            throw new GeneralDeviceException('use general mobile device');
        }

        if ('linux' === $deviceCode || 'cros' === $deviceCode) {
            $deviceLoader = $deviceLoaderFactory('unknown', 'desktop');
            $deviceLoader->init();

            return $deviceLoader->load('linux desktop', $this->useragent);
        }

        if ('touch' === $deviceCode
            && array_key_exists('osname', $this->match)
            && 'bb10' === mb_strtolower($this->match['osname'])
        ) {
            $deviceLoader = $deviceLoaderFactory('rim', 'mobile');
            $deviceLoader->init();

            return $deviceLoader->load('z10', $this->useragent);
        }

        if (array_key_exists('manufacturercode', $this->match)) {
            $manufacturercode = mb_strtolower($this->match['manufacturercode']);
            $manufacturercode = str_replace('-', '', $manufacturercode);

            if ('sonyericsson' === $manufacturercode) {
                $manufacturercode = 'sony';
            }

            if ($manufacturercode) {
                try {
                    $deviceLoader = $deviceLoaderFactory($manufacturercode, 'mobile');
                    $deviceLoader->init();
                } catch (\Throwable $e) {
                    $this->logger->info(
                        new \Exception(
                            sprintf(
                                'an error occured while initializing the device loader for manufacturer "%s"',
                                $manufacturercode
                            ),
                            0,
                            $e
                        )
                    );
                    $deviceLoader = null;
                }

                if (null !== $deviceLoader) {
                    try {
                        /** @var \UaResult\Device\DeviceInterface $device */
                        [$device, $platform] = $deviceLoader->load(
                            $manufacturercode . ' ' . $deviceCode,
                            $this->useragent
                        );

                        if (!in_array($device->getDeviceName(), ['unknown', null])) {
                            $this->logger->debug('device detected via manufacturercode and devicecode');

                            return [$device, $platform];
                        }
                    } catch (\Throwable $e) {
                        $this->logger->info(new \Exception(sprintf('an error occured while'), 0, $e));
                    }
                }
            }
        }

        if (array_key_exists('devicetype', $this->match)) {
            if ('wpdesktop' === mb_strtolower($this->match['devicetype']) || 'xblwp7' === mb_strtolower($this->match['devicetype'])) {
                $factory = new Factory\Device\MobileFactory($this->logger);

                try {
                    return $factory($this->useragent);
                } catch (InvalidArgumentException | ParsingException $e) {
                    throw new GeneralDeviceException('use general mobile device', 0, $e);
                }
            }

            if (!empty($this->match['devicetype'])) {
                $deviceType = mb_strtolower($this->match['devicetype']);

                if (in_array($deviceType, ['mobile', 'tablet']) && isset($this->match['browsername']) && 'firefox' === mb_strtolower($this->match['browsername'])) {
                    if ('tablet' === $deviceType) {
                        throw new GeneralTabletException('use general tablet');
                    }

                    throw new GeneralDeviceException('use general mobile device');
                }

                $className = '\\BrowserDetector\\Factory\\Device\\' . ucfirst($deviceType) . 'Factory';

                if (class_exists($className)) {
                    $this->logger->debug('device detected via device type (mobile or tv)');
                    /** @var \BrowserDetector\Factory\DeviceFactoryInterface $factory */
                    $factory = new $className($this->logger);

                    try {
                        return $factory($this->useragent);
                    } catch (NotFoundException $e) {
                        $this->logger->warning($e);

                        throw $e;
                    }
                } else {
                    $this->logger->error('factory "' . $className . '" not found');
                }

                $this->logger->info('device type class was not found');
            }
        }

        throw new NotFoundException('device not found via regexes');
    }
}
