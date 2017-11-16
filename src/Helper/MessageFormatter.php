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
namespace BrowscapHelper\Helper;

use BrowserDetector\Version\Version;
use BrowserDetector\Version\VersionInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;
use UaResult\Result\Result;
use UaResult\Result\ResultFactory;

/**
 * BrowserDetectorModule.ini parsing class with caching and update capabilities
 *
 * @category  BrowserDetectorModule
 *
 * @author    Thomas Mueller <mimmi20@live.de>
 * @copyright 2012-2014 Thomas Mueller
 * @license   http://www.opensource.org/licenses/MIT MIT License
 */
class MessageFormatter
{
    /**
     * @var \UaResult\Result\Result[]
     */
    private $collection;

    /**
     * @var int
     */
    private $columnsLength = 0;

    /**
     * @var \UaResult\Result\ResultFactory
     */
    private $resultFactory;

    public function __construct()
    {
        $this->resultFactory = new ResultFactory();
    }

    /**
     * @param \UaResult\Result\Result[] $collection
     */
    public function setCollection(array $collection): void
    {
        $this->collection = $collection;
    }

    /**
     * @param int $columnsLength
     */
    public function setColumnsLength(int $columnsLength): void
    {
        $this->columnsLength = $columnsLength;
    }

    /**
     * @param string                            $propertyName
     * @param \Psr\Cache\CacheItemPoolInterface $cache
     * @param \Psr\Log\LoggerInterface          $logger
     *
     * @return string[]
     */
    public function formatMessage(string $propertyName, CacheItemPoolInterface $cache, LoggerInterface $logger): array
    {
        $modules = array_keys($this->collection);
        /** @var \UaResult\Result\Result|null $firstElement */
        $firstElement = $this->collection[$modules[0]]['result'];

        if (null === $firstElement) {
            $strReality = '(NULL)';
        } else {
            $strReality = $this->getValue($this->resultFactory->fromArray($cache, $logger, (array) $firstElement), $propertyName);
        }

        $detectionResults = [];

        foreach ($modules as $module => $name) {
            /** @var \UaResult\Result\Result|null $element */
            $element = $this->collection[$name]['result'];
            if (null === $element) {
                $strTarget = '(NULL)';
            } else {
                $strTarget = $this->getValue($this->resultFactory->fromArray($cache, $logger, (array) $element), $propertyName);
            }

            if (mb_strtolower($strTarget) === mb_strtolower($strReality)) {
                $r1 = ' ';
            } elseif (in_array($strReality, ['(NULL)', '', '(empty)']) || in_array($strTarget, ['(NULL)', '', '(empty)'])) {
                $r1 = ' ';
            } else {
                if ((mb_strlen($strTarget) > mb_strlen($strReality))
                    && (0 < mb_strlen($strReality))
                    && (0 === mb_strpos($strTarget, $strReality))
                ) {
                    $r1 = '-';
                } elseif ((mb_strlen($strTarget) < mb_strlen($strReality))
                    && (0 < mb_strlen($strTarget))
                    && (0 === mb_strpos($strReality, $strTarget))
                ) {
                    $r1 = ' ';
                } else {
                    $r1 = '-';
                }
            }

            $result = $r1 . $strTarget;
            if (mb_strlen($result) > $this->columnsLength) {
                $result = mb_substr($result, 0, $this->columnsLength - 3) . '...';
            }

            $detectionResults[$module] = str_pad($result, $this->columnsLength, ' ');
        }

        return $detectionResults;
    }

    /**
     * @param \UaResult\Result\Result $element
     * @param string                  $propertyName
     *
     * @return string
     */
    private function getValue(Result $element, string $propertyName): string
    {
        switch ($propertyName) {
            case 'mobile_browser':
                $value = $element->getBrowser()->getName();

                break;
            case 'mobile_browser_version':
                $value = $element->getBrowser()->getVersion();

                if ($value instanceof Version) {
                    $value = $value->getVersion(VersionInterface::IGNORE_MICRO_IF_EMPTY | VersionInterface::IGNORE_MINOR_IF_EMPTY | VersionInterface::IGNORE_MACRO_IF_EMPTY);
                }

                if ('' === $value) {
                    $value = null;
                }

                break;
            case 'mobile_browser_modus':
                $value = $element->getBrowser()->getModus();

                break;
            case 'mobile_browser_bits':
                $value = $element->getBrowser()->getBits();

                break;
            case 'browser_type':
                $value = $element->getBrowser()->getType()->getName();

                break;
            case 'mobile_browser_manufacturer':
                $value = $element->getBrowser()->getManufacturer()->getName();

                break;
            case 'renderingengine_name':
                $value = $element->getEngine()->getName();

                break;
            case 'renderingengine_version':
                $value = $element->getEngine()->getVersion();

                if ($value instanceof Version) {
                    $value = $value->getVersion(VersionInterface::IGNORE_MICRO_IF_EMPTY | VersionInterface::IGNORE_MINOR_IF_EMPTY | VersionInterface::IGNORE_MACRO_IF_EMPTY);
                }

                if ('' === $value) {
                    $value = null;
                }

                break;
            case 'renderingengine_manufacturer':
                $value = $element->getEngine()->getManufacturer()->getName();

                break;
            case 'device_os':
                $value = $element->getOs()->getName();

                break;
            case 'device_os_version':
                $value = $element->getOs()->getVersion();

                if ($value instanceof Version) {
                    $value = $value->getVersion(VersionInterface::IGNORE_MICRO_IF_EMPTY | VersionInterface::IGNORE_MINOR_IF_EMPTY | VersionInterface::IGNORE_MACRO_IF_EMPTY);
                }

                if ('' === $value) {
                    $value = null;
                }

                break;
            case 'device_os_bits':
                $value = $element->getOs()->getBits();

                break;
            case 'device_os_manufacturer':
                $value = $element->getOs()->getManufacturer()->getName();

                break;
            case 'brand_name':
                $value = $element->getDevice()->getBrand()->getBrandName();

                break;
            case 'marketing_name':
                $value = $element->getDevice()->getMarketingName();

                break;
            case 'model_name':
                $value = $element->getDevice()->getDeviceName();

                break;
            case 'manufacturer_name':
                $value = $element->getDevice()->getManufacturer()->getName();

                break;
            case 'device_type':
                $value = $element->getDevice()->getType()->getName();

                break;
            case 'pointing_method':
                $value = $element->getDevice()->getPointingMethod();

                break;
            case 'resolution_width':
                $value = $element->getDevice()->getResolutionWidth();

                break;
            case 'resolution_height':
                $value = $element->getDevice()->getResolutionHeight();

                break;
            case 'dual_orientation':
                $value = $element->getDevice()->getDualOrientation();

                break;
            default:
                $value = '(n/a)';

                break;
        }

        if (null === $value || 'null' === $value) {
            $output = '(NULL)';
        } elseif ('' === $value) {
            $output = '(empty)';
        } elseif (false === $value || 'false' === $value) {
            $output = '(false)';
        } elseif (true === $value || 'true' === $value) {
            $output = '(true)';
        } else {
            $output = (string) $value;
        }

        return $output;
    }
}
