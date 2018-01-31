<?php
/**
 * This file is part of the browscap-helper package.
 *
 * Copyright (c) 2015-2017, Thomas Mueller <mimmi20@live.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);
namespace BrowscapHelper\Factory;

use BrowscapHelper\Factory\Regex\GeneralDeviceException;
use BrowscapHelper\Loader\RegexLoader;
use BrowserDetector\Cache\Cache;
use BrowserDetector\Factory;
use BrowserDetector\Loader\BrowserLoader;
use BrowserDetector\Loader\DeviceLoader;
use BrowserDetector\Loader\EngineLoader;
use BrowserDetector\Loader\NotFoundException;
use BrowserDetector\Loader\PlatformLoader;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface as PsrCacheInterface;
use Stringy\Stringy;
use UaResult\Engine\EngineInterface;
use UaResult\Os\OsInterface;

/**
 * detection class using regexes
 *
 * @category  BrowserDetector
 */
class RegexFactory
{
    /**
     * @var \BrowserDetector\Cache\Cache
     */
    private $cache;

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
     * @param \Psr\SimpleCache\CacheInterface $cache
     * @param \Psr\Log\LoggerInterface        $logger
     */
    public function __construct(PsrCacheInterface $cache, LoggerInterface $logger)
    {
        $this->cache  = new Cache($cache);
        $this->logger = $logger;
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

        foreach (RegexLoader::getInstance($this->logger)->getRegexes() as $regex) {
            $matches = [];

            if (preg_match($regex, $useragent, $matches)) {
                $this->match = $matches;

                $this->runDetection = true;

                return;
            }
        }

        $this->runDetection = true;

        throw new Regex\NoMatchException('no regex did match');
    }

    /**
     * @return array
     */
    public function getDevice(): array
    {
        if (null === $this->useragent) {
            throw new \InvalidArgumentException('no useragent was set');
        }

        if (!is_array($this->match) && $this->runDetection) {
            throw new \InvalidArgumentException('device not found via regexes');
        }

        if (!is_array($this->match)) {
            throw new \InvalidArgumentException('please call the detect function before trying to get the result');
        }

        if (!array_key_exists('devicecode', $this->match) || '' === $this->match['devicecode']) {
            throw new Regex\NoMatchException('device not detected via regexes');
        }

        $deviceCode   = mb_strtolower($this->match['devicecode']);
        $deviceLoader = DeviceLoader::getInstance($this->cache, $this->logger);

        if (!array_key_exists('osname', $this->match) || '' === $this->match['osname']) {
            $platformCode = null;
        } else {
            $platformCode = mb_strtolower($this->match['osname']);
        }

        $s = new Stringy($this->useragent);

        if ('windows' === $deviceCode) {
            return $deviceLoader->load('windows desktop', $this->useragent);
        }
        if ('macintosh' === $deviceCode) {
            return $deviceLoader->load('macintosh', $this->useragent);
        }
        if ('cfnetwork' === $deviceCode) {
            try {
                return (new Factory\Device\DarwinFactory($deviceLoader))->detect($this->useragent, $s);
            } catch (NotFoundException $e) {
                throw $e;
            }
        } elseif (in_array($deviceCode, ['dalvik', 'android', 'opera/9.80', 'generic'])) {
            throw new GeneralDeviceException('use general mobile device');
        } elseif (in_array($deviceCode, ['at', 'ap', 'ip', 'it']) && 'linux' === $platformCode) {
            throw new GeneralDeviceException('use general mobile device');
        } elseif ('philipstv' === $deviceCode) {
            return $deviceLoader->load('general philips tv', $this->useragent);
        } elseif (in_array($deviceCode, ['4g lte', '3g', '709v82_jbla118', 'linux arm'])) {
            throw new GeneralDeviceException('use general mobile device');
        } elseif ('linux' === $deviceCode || 'cros' === $deviceCode) {
            return $deviceLoader->load('linux desktop', $this->useragent);
        } elseif ('touch' === $deviceCode
            && array_key_exists('osname', $this->match)
            && 'bb10' === mb_strtolower($this->match['osname'])
        ) {
            return $deviceLoader->load('z10', $this->useragent);
        }

        if (array_key_exists('manufacturercode', $this->match)) {
            $manufacturercode = mb_strtolower($this->match['manufacturercode']);
        } else {
            $manufacturercode = '';
        }

        if ($deviceLoader->has($manufacturercode . ' ' . $deviceCode)) {
            /** @var \UaResult\Device\DeviceInterface $device */
            [$device, $platform] = $deviceLoader->load($manufacturercode . ' ' . $deviceCode, $this->useragent);

            if (!in_array($device->getDeviceName(), ['unknown', null])) {
                $this->logger->debug('device detected via manufacturercode and devicecode');

                return [$device, $platform];
            }
        }

        if ($deviceLoader->has($deviceCode)) {
            /** @var \UaResult\Device\DeviceInterface $device */
            [$device, $platform] = $deviceLoader->load($deviceCode, $this->useragent);

            if (!in_array($device->getDeviceName(), ['unknown', null])) {
                $this->logger->debug('device detected via devicecode');

                return [$device, $platform];
            }
        }

        if ($manufacturercode) {
            if ('sonyericsson' === mb_strtolower($manufacturercode)) {
                $manufacturercode = 'Sony';
            }

            $className = '\\BrowserDetector\\Factory\\Device\\Mobile\\' . ucfirst($manufacturercode) . 'Factory';

            if (class_exists($className)) {
                $this->logger->debug('device detected via manufacturer');
                /** @var \BrowserDetector\Factory\FactoryInterface $factory */
                $factory = new $className($deviceLoader);

                try {
                    return $factory->detect($this->useragent, $s);
                } catch (NotFoundException $e) {
                    $this->logger->warning($e);

                    throw $e;
                }
            } else {
                $this->logger->error('factory "' . $className . '" not found');
            }

            $this->logger->info('device manufacturer class was not found');
        }

        if (array_key_exists('devicetype', $this->match)) {
            if ('wpdesktop' === mb_strtolower($this->match['devicetype']) || 'xblwp7' === mb_strtolower($this->match['devicetype'])) {
                $factory = new Factory\Device\MobileFactory($deviceLoader);

                try {
                    return $factory->detect($this->useragent, $s);
                } catch (NotFoundException $e) {
                    throw new GeneralDeviceException('use general mobile device', 0, $e);
                }
            } elseif (!empty($this->match['devicetype'])) {
                $className = '\\BrowserDetector\\Factory\\Device\\' . ucfirst(mb_strtolower($this->match['devicetype'])) . 'Factory';

                if (class_exists($className)) {
                    $this->logger->debug('device detected via device type (mobile or tv)');
                    /** @var \BrowserDetector\Factory\FactoryInterface $factory */
                    $factory = new $className($deviceLoader);

                    try {
                        return $factory->detect($this->useragent, $s);
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

    /**
     * @return \UaResult\Os\OsInterface
     */
    public function getPlatform(): OsInterface
    {
        if (null === $this->useragent) {
            throw new \InvalidArgumentException('no useragent was set');
        }

        if (!is_array($this->match) && $this->runDetection) {
            throw new NotFoundException('platform not found via regexes');
        }

        if (!is_array($this->match)) {
            throw new \InvalidArgumentException('please call the detect function before trying to get the result');
        }

        $platformLoader = PlatformLoader::getInstance($this->cache, $this->logger);

        if (!array_key_exists('osname', $this->match)
            && array_key_exists('manufacturercode', $this->match)
            && 'blackberry' === mb_strtolower($this->match['manufacturercode'])
        ) {
            $this->logger->debug('platform forced to rim os');

            return $platformLoader->load('rim os', $this->useragent);
        }

        if (!array_key_exists('osname', $this->match) || '' === $this->match['osname']) {
            throw new Regex\NoMatchException('platform not detected via regexes');
        }

        $platformCode = mb_strtolower($this->match['osname']);

        $s = new Stringy($this->useragent);

        if ('darwin' === $platformCode) {
            $darwinFactory = new Factory\Platform\DarwinFactory($platformLoader);

            return $darwinFactory->detect($this->useragent, $s);
        }

        if ('linux' === $platformCode && array_key_exists('devicecode', $this->match)) {
            // Android Desktop Mode
            $platformCode = 'android';
        } elseif ('adr' === $platformCode) {
            // Android Desktop Mode with UCBrowser
            $platformCode = 'android';
        } elseif ('linux' === $platformCode && $s->containsAll(['opera mini', 'ucbrowser'], false)) {
            // Android Desktop Mode with UCBrowser
            $platformCode = 'android';
        } elseif ('linux' === $platformCode) {
            $linuxFactory = new Factory\Platform\LinuxFactory($platformLoader);

            return $linuxFactory->detect($this->useragent, $s);
        } elseif ('bb10' === $platformCode || 'blackberry' === $platformCode) {
            // Rim OS
            $platformCode = 'rim os';
        } elseif ('cros' === $platformCode) {
            $platformCode = 'chromeos';
        } elseif (in_array($platformCode, ['j2me/midp', 'java'])) {
            $platformCode = 'java';
        } elseif (in_array($platformCode, ['maui runtime', 'spreadtrum', 'vre'])) {
            $platformCode = 'android';
        } elseif ('series 60' === $platformCode) {
            $platformCode = 'symbian';
        } elseif ('windows mobile' === $platformCode) {
            $platformCode = 'windows mobile os';
        } elseif ('windows phone' === $platformCode) {
            $platformCode = 'windows phone';
        }

        if (false !== mb_strpos($platformCode, 'windows nt') && array_key_exists('devicetype', $this->match)) {
            // Windows Phone Desktop Mode
            $platformCode = 'windows phone';
        }

        if ($platformLoader->has($platformCode)) {
            $platform = $platformLoader->load($platformCode, $this->useragent);

            if (!in_array($platform->getName(), ['unknown', null])) {
                return $platform;
            }

            $this->logger->info('platform with code "' . $platformCode . '" not found via regexes');
        }

        throw new NotFoundException('platform not found via regexes');
    }

    /**
     * @return array
     */
    public function getBrowser(): array
    {
        if (null === $this->useragent) {
            throw new \InvalidArgumentException('no useragent was set');
        }

        if (!is_array($this->match) && $this->runDetection) {
            throw new NotFoundException('browser not found via regexes');
        }

        if (!is_array($this->match)) {
            throw new \InvalidArgumentException('please call the detect function before trying to get the result');
        }

        if (!array_key_exists('browsername', $this->match) || '' === $this->match['browsername']) {
            throw new Regex\NoMatchException('browser not detected via regexes');
        }

        $browserCode   = mb_strtolower($this->match['browsername']);
        $browserLoader = BrowserLoader::getInstance($this->cache, $this->logger);

        switch ($browserCode) {
            case 'opr':
                $browserCode = 'opera';

                break;
            case 'msie':
                $browserCode = 'internet explorer';

                break;
            case 'ucweb':
            case 'ubrowser':
                $browserCode = 'ucbrowser';

                break;
            case 'crmo':
                $browserCode = 'chrome';

                break;
            case 'granparadiso':
                $browserCode = 'firefox';

                break;
            default:
                // do nothing here
        }

        if ('safari' === $browserCode) {
            if (array_key_exists('osname', $this->match)) {
                $osname = mb_strtolower($this->match['osname']);

                if ('android' === $osname || 'linux' === $osname) {
                    return $browserLoader->load('android webkit', $this->useragent);
                }

                if ('tizen' === $osname) {
                    return $browserLoader->load('samsungbrowser', $this->useragent);
                }

                if ('blackberry' === $osname) {
                    return $browserLoader->load('blackberry', $this->useragent);
                }

                if ('symbian' === $osname || 'symbianos' === $osname) {
                    return $browserLoader->load('android webkit', $this->useragent);
                }
            }

            if (array_key_exists('manufacturercode', $this->match)) {
                $devicemaker = mb_strtolower($this->match['manufacturercode']);

                if ('nokia' === $devicemaker) {
                    return $browserLoader->load('nokiabrowser', $this->useragent);
                }
            }
        }

        if ($browserLoader->has($browserCode)) {
            /** @var \UaResult\Browser\BrowserInterface $browser */
            [$browser] = $browserLoader->load($browserCode, $this->useragent);

            if (!in_array($browser->getName(), ['unknown', null])) {
                return [$browser];
            }

            $this->logger->info('browser with code "' . $browserCode . '" not found via regexes');
        }

        throw new NotFoundException('browser not found via regexes');
    }

    /**
     * @return \UaResult\Engine\EngineInterface
     */
    public function getEngine(): EngineInterface
    {
        if (null === $this->useragent) {
            throw new \InvalidArgumentException('no useragent was set');
        }

        if (!is_array($this->match) && $this->runDetection) {
            throw new NotFoundException('engine not found via regexes');
        }

        if (!is_array($this->match)) {
            throw new \InvalidArgumentException('please call the detect function before trying to get the result');
        }

        if (!array_key_exists('enginename', $this->match) || '' === $this->match['enginename']) {
            throw new Regex\NoMatchException('engine not detected via regexes');
        }

        $engineCode   = mb_strtolower($this->match['enginename']);
        $engineLoader = EngineLoader::getInstance($this->cache, $this->logger);

        if ('cfnetwork' === $engineCode) {
            return $engineLoader->load('webkit', $this->useragent);
        }

        if (in_array($engineCode, ['applewebkit', 'webkit'])) {
            if (array_key_exists('chromeversion', $this->match)) {
                $chromeversion = (int) $this->match['chromeversion'];
            } else {
                $chromeversion = 0;
            }

            if (28 <= $chromeversion) {
                $engineCode = 'blink';
            } else {
                $engineCode = 'webkit';
            }
        }

        if ($engineLoader->has($engineCode)) {
            $engine = $engineLoader->load($engineCode, $this->useragent);

            if (!in_array($engine->getName(), ['unknown', null])) {
                return $engine;
            }

            $this->logger->info('engine with code "' . $engineCode . '" not found via regexes');
        }

        throw new NotFoundException('engine not found via regexes');
    }
}
