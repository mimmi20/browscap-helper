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

use BrowserDetector\Version\VersionInterface;
use Symfony\Component\Console\Helper\Helper;
use UaResult\Result\ResultInterface;

final class BrowscapTestWriter extends Helper
{
    /**
     * @var string
     */
    private $dir;

    /**
     * @param string $dir
     */
    public function __construct(string $dir)
    {
        $this->dir = $dir;
    }

    public function getName()
    {
        return 'browscap-test-writer';
    }

    /**
     * @param array $tests
     * @param int   $folderId
     * @param int   $totalCounter
     *
     * @return void
     */
    public function write(array $tests, int $folderId, int &$totalCounter): void
    {
        $outputBrowscap = '';

        foreach ($tests as $counter => $result) {
            /** @var ResultInterface $result */
            $platform  = clone $result->getOs();
            $device    = clone $result->getDevice();
            $engine    = clone $result->getEngine();
            $browser   = clone $result->getBrowser();
            $useragent = $result->getHeaders()['user-agent'];

            $formatedIssue   = sprintf('%1$05d', $folderId);
            $formatedCounter = $this->formatTestNumber($counter);

            $outputBrowscap .= "    'issue-" . $formatedIssue . '-' . $formatedCounter . "' => [
        'ua' => '" . str_replace(['\\', "'"], ['\\\\', "\\'"], $useragent) . "',
        'properties' => [
            'Comment' => 'Default Browser',
            'Browser' => '" . str_replace(['\\', "'"], ['\\\\', "\\'"], (string) $browser->getName()) . "',
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
            'Device_Pointing_Method' => 'unknown',
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

            ++$totalCounter;
        }

        file_put_contents($this->dir . '/issue-' . sprintf('%1$05d', $folderId) . '.php', "<?php\n\nreturn [\n" . $outputBrowscap . "];\n");
    }

    /**
     * @param int $counter
     *
     * @return string
     */
    private function formatTestNumber(int $counter): string
    {
        $folderId = $counter;
        $chars    = [];

        do {
            $chars[]  = chr(($folderId % 26) + 65);
            $folderId = (int) ($folderId / 26);
        } while (1 <= $folderId);

        return implode('', array_reverse($chars));
    }
}
