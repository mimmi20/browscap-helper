<?php
/**
 * This file is part of the browscap-helper package.
 *
 * Copyright (c) 2015-2019, Thomas Mueller <mimmi20@live.de>
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
use BrowscapHelper\Factory\Regex\GeneralTvException;
use BrowscapHelper\Factory\Regex\NoMatchException;
use BrowserDetector\Loader\CompanyLoaderFactory;
use BrowserDetector\Loader\DeviceLoaderFactory;
use BrowserDetector\Loader\Helper\Filter;
use BrowserDetector\Loader\NotFoundException;
use BrowserDetector\Parser;
use BrowserDetector\Parser\PlatformParserFactory;
use JsonClass\Json;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Helper\Helper;

final class RegexFactory extends Helper
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
     * @var bool
     */
    private $runDetection = false;

    public function getName()
    {
        return 'regex-factory';
    }

    /**
     * Gets the information about the rendering engine by User Agent
     *
     * @param string $useragent
     *
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
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

            if ((bool) preg_match($regex, $useragent, $matches)) {
                $this->match        = $matches;
                $this->runDetection = true;

                return;
            }
        }

        $this->runDetection = true;

        throw new NoMatchException('no regex did match');
    }

    /**
     * @param \Psr\Log\LoggerInterface $logger
     *
     * @throws \BrowscapHelper\Factory\Regex\GeneralDeviceException
     * @throws \BrowscapHelper\Factory\Regex\GeneralTabletException
     * @throws \BrowscapHelper\Factory\Regex\GeneralPhilipsTvException
     * @throws \BrowscapHelper\Factory\Regex\GeneralTvException
     * @throws \BrowscapHelper\Factory\Regex\GeneralPhoneException
     * @throws \BrowscapHelper\Factory\Regex\GeneralBlackberryException
     * @throws \BrowscapHelper\Factory\Regex\NoMatchException
     * @throws \BrowserDetector\Loader\NotFoundException
     * @throws \InvalidArgumentException
     *
     * @return array
     */
    public function getDevice(LoggerInterface $logger): array
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

        $deviceCode = mb_strtolower($this->match['devicecode']);

        $jsonParser           = new Json();
        $companyLoaderFactory = new CompanyLoaderFactory($jsonParser, new Filter());

        /** @var \BrowserDetector\Loader\CompanyLoader $companyLoader */
        $companyLoader = $companyLoaderFactory();

        $platformParserFactory = new PlatformParserFactory($logger, $jsonParser, $companyLoader);
        $platformParser        = $platformParserFactory();

        $deviceLoaderFactory = new DeviceLoaderFactory($logger, $jsonParser, $companyLoader, $platformParser, new Filter());

        if (!array_key_exists('osname', $this->match) || '' === $this->match['osname']) {
            $platformCode = null;
        } else {
            $platformCode = mb_strtolower($this->match['osname']);
        }

        if ('windows' === $deviceCode) {
            $deviceLoader = $deviceLoaderFactory('unknown');

            return $deviceLoader->load('windows desktop', $this->useragent);
        }

        if ('macintosh' === $deviceCode) {
            $deviceLoader = $deviceLoaderFactory('apple');

            return $deviceLoader->load('macintosh', $this->useragent);
        }

        $loaderFactory = new DeviceLoaderFactory($logger, $jsonParser, $companyLoader, $platformParser, new Filter());
        $fileParser    = new Parser\Helper\RulefileParser($jsonParser, $logger);

        if ('cfnetwork' === $deviceCode) {
            $darwinParser = new Parser\Device\DarwinParser($fileParser, $loaderFactory);

            return $darwinParser->parse($this->useragent);
        }

        if (in_array($deviceCode, ['dalvik', 'android', 'opera/9.80', 'opera/9.50', 'generic'], true)
            && array_key_exists('osname', $this->match)
            && 'blackberry' === mb_strtolower($this->match['osname'])
        ) {
            throw new GeneralBlackberryException('use general mobile device');
        }

        if (in_array($deviceCode, ['dalvik', 'android'], true)) {
            if (array_key_exists('devicetype', $this->match)) {
                $deviceType = mb_strtolower($this->match['devicetype']);

                if ('tablet' === $deviceType) {
                    throw new GeneralTabletException('use general tablet');
                }

                if ('mobile' === $deviceType) {
                    throw new GeneralPhoneException('use general mobile phone');
                }

                if ('tv' === $deviceType) {
                    throw new GeneralTvException('general tv device');
                }
            }

            throw new GeneralDeviceException('use general mobile device');
        }

        if (in_array($deviceCode, ['opera/9.80', 'opera/9.50', 'series 60', 'generic', ''], true)) {
            throw new GeneralDeviceException('use general mobile device');
        }

        if (in_array($deviceCode, ['at', 'ap', 'ip', 'it'], true) && 'linux' === $platformCode) {
            throw new GeneralDeviceException('use general mobile device');
        }

        if ('philipstv' === $deviceCode) {
            throw new GeneralPhilipsTvException('use general philips tv device');
        }

        if (in_array($deviceCode, ['4g lte', '3g', '709v82_jbla118', 'linux arm'], true)) {
            throw new GeneralDeviceException('use general mobile device');
        }

        if ('linux' === $deviceCode || 'cros' === $deviceCode) {
            $deviceLoader = $deviceLoaderFactory('unknown');

            return $deviceLoader->load('linux desktop', $this->useragent);
        }

        if ('touch' === $deviceCode
            && array_key_exists('osname', $this->match)
            && 'bb10' === mb_strtolower($this->match['osname'])
        ) {
            $deviceLoader = $deviceLoaderFactory('rim');

            return $deviceLoader->load('z10', $this->useragent);
        }

        if (array_key_exists('manufacturercode', $this->match)) {
            $manufacturercode = mb_strtolower($this->match['manufacturercode']);
            $manufacturercode = str_replace('-', '', $manufacturercode);

            if ('sonyericsson' === $manufacturercode) {
                $manufacturercode = 'sony';
            }

            if ('blackberry' === $manufacturercode) {
                $manufacturercode = 'rim';
            }

            if ('imobile' === $manufacturercode) {
                $manufacturercode = 'i-mobile';
            }

            if ('ktouch' === $manufacturercode) {
                $manufacturercode = 'k-touch';
            }

            if ((bool) $manufacturercode) {
                $deviceLoader = $deviceLoaderFactory($manufacturercode);

                try {
                    /** @var \UaResult\Device\DeviceInterface $device */
                    [$device, $platform] = $deviceLoader->load(
                        $manufacturercode . ' ' . $deviceCode,
                        $this->useragent
                    );

                    if (!in_array($device->getDeviceName(), ['unknown', null], true)) {
                        $logger->debug('device detected via manufacturercode and devicecode');

                        return [$device, $platform];
                    }
                } catch (\Throwable $e) {
                    $logger->info(new \Exception(sprintf('an error occured while'), 0, $e));
                }
            }
        }

        if (array_key_exists('devicetype', $this->match)) {
            if ('wpdesktop' === mb_strtolower($this->match['devicetype']) || 'xblwp7' === mb_strtolower($this->match['devicetype'])) {
                $mobileParser = new Parser\Device\MobileParser($fileParser, $loaderFactory);

                try {
                    return $mobileParser->parse($this->useragent);
                } catch (\Throwable $e) {
                    throw new GeneralDeviceException('use general mobile device', 0, $e);
                }
            }

            $deviceType = mb_strtolower($this->match['devicetype']);

            if (in_array($deviceType, ['mobile', 'tablet'], true) && isset($this->match['browsername']) && 'firefox' === mb_strtolower($this->match['browsername'])) {
                if ('tablet' === $deviceType) {
                    throw new GeneralTabletException('use general tablet');
                }

                throw new GeneralDeviceException('use general mobile device');
            }

            $className = '\\BrowserDetector\\Parser\\Device\\' . ucfirst($deviceType) . 'Parser';

            if (class_exists($className)) {
                $logger->debug('device detected via device type (mobile or tv)');
                /** @var \BrowserDetector\Parser\DeviceParserInterface $parser */
                $parser = new $className($fileParser, $loaderFactory);

                return $parser->parse($this->useragent);
            }

            $logger->info('device type class was not found');
        }

        throw new NotFoundException('device not found via regexes');
    }
}
