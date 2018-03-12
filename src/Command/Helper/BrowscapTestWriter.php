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

use BrowserDetector\Version\VersionInterface;
use Psr\Log\LoggerInterface;
use UaResult\Result\ResultInterface;
use Symfony\Component\Console\Helper\Helper;

class BrowscapTestWriter extends Helper
{
    /**
     * @var string
     */
    private $dir;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    private $outputBrowscap = '';

    private $counter = 0;

    private $number = 0;

    /**
     * @param \Psr\Log\LoggerInterface $logger
     * @param string                   $dir
     */
    public function __construct(LoggerInterface $logger, string $dir)
    {
        $this->logger = $logger;
        $this->dir    = $dir;
    }

    public function getName()
    {
        return 'browscap-test-writer';
    }

    /**
     * @param \UaResult\Result\ResultInterface $result
     * @param int                              $number
     * @param string                           $useragent
     * @param int                              $totalCounter
     *
     * @return void
     */
    public function write(ResultInterface $result, int $number, string $useragent, int &$totalCounter): void
    {
        $platform = clone $result->getOs();
        $device   = clone $result->getDevice();
        $engine   = clone $result->getEngine();
        $browser  = clone $result->getBrowser();

        if ($this->number !== $number) {
            $this->number  = $number;
            $this->counter = 0;
        }

        $formatedIssue   = sprintf('%1$05d', $number);
        $formatedCounter = sprintf('%1$05d', $this->counter);

        $this->outputBrowscap .= "    'issue-" . $formatedIssue . '-' . $formatedCounter . "' => [
        'ua' => '" . str_replace(['\\', "'"], ['\\\\', "\\'"], $useragent) . "',
        'properties' => [
            'Comment' => 'Default Browser',
            'Browser' => '" . str_replace(['\\', "'"], ['\\\\', "\\'"], $browser->getName()) . "',
            'Browser_Type' => '" . $browser->getType()->getName() . "',
            'Browser_Bits' => '" . $browser->getBits() . "',
            'Browser_Maker' => '" . $browser->getManufacturer()->getName() . "',
            'Browser_Modus' => '" . $browser->getModus() . "',
            'Version' => '" . $browser->getVersion()->getVersion() . "',
            'Platform' => '" . $platform->getName() . "',
            'Platform_Version' => '" . $platform->getVersion()->getVersion(VersionInterface::IGNORE_MICRO) . "',
            'Platform_Description' => '',
            'Platform_Bits' => '" . $platform->getBits() . "',
            'Platform_Maker' => '" . $platform->getManufacturer()->getName() . "',
            'Alpha' => false,
            'Beta' => false,
            'isSyndicationReader' => false,
            'isFake' => false,
            'isAnonymized' => false,
            'isModified' => false,
            'Device_Name' => '" . $device->getMarketingName() . "',
            'Device_Maker' => '" . $device->getManufacturer()->getName() . "',
            'Device_Type' => '" . $device->getType()->getName() . "',
            'Device_Pointing_Method' => '" . $device->getPointingMethod() . "',
            'Device_Code_Name' => '" . $device->getDeviceName() . "',
            'Device_Brand_Name' => '" . $device->getBrand()->getBrandName() . "',
            'RenderingEngine_Name' => '" . $engine->getName() . "',
            'RenderingEngine_Version' => 'unknown',
            'RenderingEngine_Maker' => '" . $engine->getManufacturer()->getName() . "',
        ],
        'lite' => true,
        'standard' => true,
        'full' => true,
    ],\n";

        file_put_contents($this->dir . '/issue-' . sprintf('%1$05d', $number) . '.php', "<?php\n\nreturn [\n" . $this->outputBrowscap . "];\n");

        ++$this->counter;
        ++$totalCounter;
    }
}
