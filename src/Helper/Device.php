<?php
/**
 * Copyright (c) 1998-2014 Browser Capabilities Project
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * Refer to the LICENSE file distributed with this package.
 *
 * @category   Browscap
 * @copyright  1998-2014 Browser Capabilities Project
 * @license    MIT
 */

namespace BrowscapHelper\Helper;

/**
 * Class DiffCommand
 *
 * @category   Browscap
 * @author     James Titcumb <james@asgrim.com>
 */
class Device
{
    /**
     * @param string $ua
     *
     * @return array
     */
    public function detect($ua)
    {
        $mobileDevice = false;

        $devices = [
            '' => [
                'Device_Name'            => 'unknown',
                'Device_Maker'           => 'unknown',
                'Device_Type'            => 'unknown',
                'Device_Pointing_Method' => 'unknown',
                'Device_Code_Name'       => 'unknown',
                'Device_Brand_Name'      => 'unknown',
            ],
            'Windows Desktop' => [
                'Device_Name'            => 'Windows Desktop',
                'Device_Maker'           => 'Various',
                'Device_Type'            => 'Desktop',
                'Device_Pointing_Method' => 'mouse',
                'Device_Code_Name'       => 'Windows Desktop',
                'Device_Brand_Name'      => 'unknown',
            ],
            'Linux Desktop' => [
                'Device_Name'            => 'Linux Desktop',
                'Device_Maker'           => 'Various',
                'Device_Type'            => 'Desktop',
                'Device_Pointing_Method' => 'mouse',
                'Device_Code_Name'       => 'Linux Desktop',
                'Device_Brand_Name'      => 'unknown',
            ],
            'Macintosh' => [
                'Device_Name'            => 'Macintosh',
                'Device_Maker'           => 'Apple Inc',
                'Device_Type'            => 'Desktop',
                'Device_Pointing_Method' => 'mouse',
                'Device_Code_Name'       => 'Macintosh',
                'Device_Brand_Name'      => 'Apple',
            ],
            'iPhone' => [
                'Device_Name'            => 'iPhone',
                'Device_Maker'           => 'Apple Inc',
                'Device_Type'            => 'Mobile Phone',
                'Device_Pointing_Method' => 'touchscreen',
                'Device_Code_Name'       => 'iPhone',
                'Device_Brand_Name'      => 'Apple',
            ],
            'iPad' => [
                'Device_Name'            => 'iPad',
                'Device_Maker'           => 'Apple Inc',
                'Device_Type'            => 'Tablet',
                'Device_Pointing_Method' => 'touchscreen',
                'Device_Code_Name'       => 'iPad',
                'Device_Brand_Name'      => 'Apple',
            ],
            'iPod' => [
                'Device_Name'            => 'iPod',
                'Device_Maker'           => 'Apple Inc',
                'Device_Type'            => 'Mobile Device',
                'Device_Pointing_Method' => 'touchscreen',
                'Device_Code_Name'       => 'iPod',
                'Device_Brand_Name'      => 'Apple',
            ],
            'AT10-A' => [
                'Device_Name'            => 'eXcite Pure',
                'Device_Maker'           => 'Toshiba',
                'Device_Type'            => 'Tablet',
                'Device_Pointing_Method' => 'touchscreen',
                'Device_Code_Name'       => 'AT10-A',
                'Device_Brand_Name'      => 'Toshiba',
            ],
            'SM-T235' => [
                'Device_Name'            => 'Galaxy Tab 4 7.0 WiFi + LTE',
                'Device_Maker'           => 'Samsung',
                'Device_Type'            => 'Tablet',
                'Device_Pointing_Method' => 'touchscreen',
                'Device_Code_Name'       => 'SM-T235',
                'Device_Brand_Name'      => 'Samsung',
            ],
            'SM-T705' => [
                'Device_Name'            => 'Galaxy Tab S 8.4 LTE',
                'Device_Maker'           => 'Samsung',
                'Device_Type'            => 'Tablet',
                'Device_Pointing_Method' => 'touchscreen',
                'Device_Code_Name'       => 'SM-T705',
                'Device_Brand_Name'      => 'Samsung',
            ],
            'SM-T2105' => [
                'Device_Name'            => 'Galaxy Tab 3 Kids',
                'Device_Maker'           => 'Samsung',
                'Device_Type'            => 'Tablet',
                'Device_Pointing_Method' => 'touchscreen',
                'Device_Code_Name'       => 'SM-T2105',
                'Device_Brand_Name'      => 'Samsung',
            ],
            'SM-N900A' => [
                'Device_Name'            => 'Galaxy Note 3 LTE (AT&T)',
                'Device_Maker'           => 'Samsung',
                'Device_Type'            => 'Mobile Phone',
                'Device_Pointing_Method' => 'touchscreen',
                'Device_Code_Name'       => 'SM-N900A',
                'Device_Brand_Name'      => 'Samsung',
            ],
            'S5000-F' => [
                'Device_Name'            => 'IdeaTab S5000-F',
                'Device_Maker'           => 'Lenovo',
                'Device_Type'            => 'Tablet',
                'Device_Pointing_Method' => 'touchscreen',
                'Device_Code_Name'       => 'S5000-F',
                'Device_Brand_Name'      => 'Lenovo',
            ],
            'S5000-H' => [
                'Device_Name'            => 'IdeaTab S5000-H',
                'Device_Maker'           => 'Lenovo',
                'Device_Type'            => 'Tablet',
                'Device_Pointing_Method' => 'touchscreen',
                'Device_Code_Name'       => 'S5000-H',
                'Device_Brand_Name'      => 'Lenovo',
            ],
            'A7600-H' => [
                'Device_Name'            => 'A10-70 A7600 Wi-Fi + 3G',
                'Device_Maker'           => 'Lenovo',
                'Device_Type'            => 'Tablet',
                'Device_Pointing_Method' => 'touchscreen',
                'Device_Code_Name'       => 'A7600-H',
                'Device_Brand_Name'      => 'Lenovo',
            ],
            'LG-L160L' => [
                'Device_Name'            => 'Optimus LTE2',
                'Device_Maker'           => 'LG',
                'Device_Type'            => 'Mobile Phone',
                'Device_Pointing_Method' => 'touchscreen',
                'Device_Code_Name'       => 'L160L',
                'Device_Brand_Name'      => 'LG',
            ],
            'GT-S5830' => [
                'Device_Name'            => 'Galaxy Ace',
                'Device_Maker'           => 'Samsung',
                'Device_Type'            => 'Mobile Phone',
                'Device_Pointing_Method' => 'touchscreen',
                'Device_Code_Name'       => 'GT-S5830',
                'Device_Brand_Name'      => 'Samsung',
            ],
            'GT-S5830i' => [
                'Device_Name'            => 'Galaxy Ace',
                'Device_Maker'           => 'Samsung',
                'Device_Type'            => 'Mobile Phone',
                'Device_Pointing_Method' => 'touchscreen',
                'Device_Code_Name'       => 'GT-S5830i',
                'Device_Brand_Name'      => 'Samsung',
            ],
            'GT-S5830c' => [
                'Device_Name'            => 'Galaxy Ace',
                'Device_Maker'           => 'Samsung',
                'Device_Type'            => 'Mobile Phone',
                'Device_Pointing_Method' => 'touchscreen',
                'Device_Code_Name'       => 'GT-S5830C',
                'Device_Brand_Name'      => 'Samsung',
            ],
            'MI 4W' => [
                'Device_Name'            => 'MI 4W',
                'Device_Maker'           => 'Xiaomi Tech',
                'Device_Type'            => 'Mobile Phone',
                'Device_Pointing_Method' => 'touchscreen',
                'Device_Code_Name'       => 'MI 4W',
                'Device_Brand_Name'      => 'Xiaomi',
            ],
            'MI 4LTE' => [
                'Device_Name'            => 'MI 4 LTE',
                'Device_Maker'           => 'Xiaomi Tech',
                'Device_Type'            => 'Mobile Phone',
                'Device_Pointing_Method' => 'touchscreen',
                'Device_Code_Name'       => 'MI 4 LTE',
                'Device_Brand_Name'      => 'Xiaomi',
            ],
            'Mi Pad' => [
                'Device_Name'            => 'Mi Pad',
                'Device_Maker'           => 'Xiaomi Tech',
                'Device_Type'            => 'Tablet',
                'Device_Pointing_Method' => 'touchscreen',
                'Device_Code_Name'       => 'Mi Pad',
                'Device_Brand_Name'      => 'Xiaomi',
            ],
        ];

        $device   = '';

        if (false !== strpos($ua, 'Windows Phone')) {
            $mobileDevice                   = true;
        } elseif (false !== strpos($ua, 'wds')) {
            $mobileDevice                   = true;
        } elseif (false !== stripos($ua, 'wpdesktop')) {
            $mobileDevice                   = true;
        } elseif (false !== strpos($ua, 'Tizen')) {
            $mobileDevice                   = true;
        } elseif (false !== strpos($ua, 'Windows CE')) {
            $mobileDevice                   = true;
        } elseif (false !== strpos($ua, 'Linux; Android')) {
            $mobileDevice                   = true;
        } elseif (false !== strpos($ua, 'Linux; U; Android')) {
            $mobileDevice                   = true;
        } elseif (false !== strpos($ua, 'U; Adr')) {
            $mobileDevice                   = true;
        } elseif (false !== strpos($ua, 'Android') || false !== strpos($ua, 'MTK')) {
            $mobileDevice                   = true;
        } elseif (false !== strpos($ua, 'Symbian') || false !== strpos($ua, 'Series 60')) {
            $mobileDevice                   = true;
        } elseif (false !== strpos($ua, 'MIDP')) {
            $mobileDevice                   = true;
        } elseif (false !== strpos($ua, 'Windows NT 10.0')) {
            $mobileDevice                   = false;
            $device = 'Windows Desktop';
        } elseif (false !== strpos($ua, 'Windows NT 6.4')) {
            $mobileDevice                   = false;
            $device = 'Windows Desktop';
        } elseif (false !== strpos($ua, 'Windows NT 6.3') && false !== strpos($ua, 'ARM')) {
            $mobileDevice                   = false;
            $device = 'Windows Desktop';
        } elseif (false !== strpos($ua, 'Windows NT 6.3')) {
            $mobileDevice                   = false;
            $device = 'Windows Desktop';
        } elseif (false !== strpos($ua, 'Windows NT 6.2') && false !== strpos($ua, 'ARM')) {
            $mobileDevice                   = false;
            $device = 'Windows Desktop';
        } elseif (false !== strpos($ua, 'Windows NT 6.2')) {
            $mobileDevice                   = false;
            $device = 'Windows Desktop';
        } elseif (false !== strpos($ua, 'Windows NT 6.1')) {
            $mobileDevice                   = false;
            $device = 'Windows Desktop';
        } elseif (false !== strpos($ua, 'Windows NT 6.0')) {
            $mobileDevice                   = false;
            $device = 'Windows Desktop';
        } elseif (false !== strpos($ua, 'Windows NT 5.3')) {
            $mobileDevice                   = false;
            $device = 'Windows Desktop';
        } elseif (false !== strpos($ua, 'Windows NT 5.2')) {
            $mobileDevice                   = false;
            $device = 'Windows Desktop';
        } elseif (false !== strpos($ua, 'Windows NT 5.1')) {
            $mobileDevice                   = false;
            $device = 'Windows Desktop';
        } elseif (false !== strpos($ua, 'Windows NT 5.01')) {
            $mobileDevice                   = false;
            $device   = 'Windows Desktop';
        } elseif (false !== strpos($ua, 'Windows NT 5.0')) {
            $mobileDevice                   = false;
            $device   = 'Windows Desktop';
        } elseif (false !== strpos($ua, 'Windows NT 4.1')) {
            $mobileDevice                   = false;
            $device   = 'Windows Desktop';
        } elseif (false !== strpos($ua, 'Windows NT 4.0')) {
            $mobileDevice                   = false;
            $device   = 'Windows Desktop';
        } elseif (false !== strpos($ua, 'Windows NT 3.5')) {
            $mobileDevice                   = false;
            $device   = 'Windows Desktop';
        } elseif (false !== strpos($ua, 'Windows NT 3.1')) {
            $mobileDevice                   = false;
            $device   = 'Windows Desktop';
        } elseif (false !== strpos($ua, 'Windows NT')) {
            $mobileDevice                   = false;
            $device   = 'Windows Desktop';
        } elseif (false !== stripos($ua, 'cygwin')) {
            $mobileDevice                   = false;
            $device   = 'Windows Desktop';
        } elseif (false !== strpos($ua, 'CPU OS')) {
            $mobileDevice                   = true;

            if (false !== strpos($ua, 'iPad')) {
                $device = 'iPad';
            } elseif (false !== strpos($ua, 'iPod')) {
                $device = 'iPod';
            } elseif (false !== strpos($ua, 'iPhone')) {
                $device = 'iPhone';
            }
        } elseif (false !== strpos($ua, 'CPU iPhone OS')) {
            $mobileDevice                   = true;

            if (false !== strpos($ua, 'iPad')) {
                $device = 'iPad';
            } elseif (false !== strpos($ua, 'iPod')) {
                $device = 'iPod';
            } elseif (false !== strpos($ua, 'iPhone')) {
                $device = 'iPhone';
            }
        } elseif (false !== strpos($ua, 'CPU like Mac OS X')) {
            $mobileDevice                   = true;

            if (false !== strpos($ua, 'iPad')) {
                $device = 'iPad';
            } elseif (false !== strpos($ua, 'iPod')) {
                $device = 'iPod';
            } elseif (false !== strpos($ua, 'iPhone')) {
                $device = 'iPhone';
            }
        } elseif (false !== strpos($ua, 'iOS')) {
            $mobileDevice                   = true;

            if (false !== strpos($ua, 'iPad')) {
                $device = 'iPad';
            } elseif (false !== strpos($ua, 'iPod')) {
                $device = 'iPod';
            } elseif (false !== strpos($ua, 'iPhone')) {
                $device = 'iPhone';
            }
        } elseif (false !== strpos($ua, 'Mac OS X')) {
            $device = 'Macintosh';
        } elseif (false !== stripos($ua, 'kubuntu')) {
            $mobileDevice                   = false;
            $device = 'Linux Desktop';
        } elseif (false !== stripos($ua, 'ubuntu')) {
            $mobileDevice                   = false;
            $device = 'Linux Desktop';
        } elseif (false !== stripos($ua, 'fedora')) {
            $mobileDevice                   = false;
            $device = 'Linux Desktop';
        } elseif (false !== stripos($ua, 'suse')) {
            $mobileDevice                   = false;
            $device = 'Linux Desktop';
        } elseif (false !== stripos($ua, 'mandriva')) {
            $mobileDevice                   = false;
            $device = 'Linux Desktop';
        } elseif (false !== stripos($ua, 'gentoo')) {
            $mobileDevice                   = false;
            $device = 'Linux Desktop';
        } elseif (false !== stripos($ua, 'slackware')) {
            $mobileDevice                   = false;
            $device = 'Linux Desktop';
        } elseif (false !== strpos($ua, 'CrOS')) {
            $mobileDevice                   = false;
            $device = 'Linux Desktop';
        } elseif (false !== strpos($ua, 'Linux')) {
            $mobileDevice                   = false;
            $device = 'Linux Desktop';
        } elseif (false !== strpos($ua, 'SymbOS')) {
            $mobileDevice                   = true;
        } elseif (false !== strpos($ua, 'hpwOS')) {
            $mobileDevice                   = true;
        }

        if (false !== strpos($ua, 'Silk') && false === strpos($ua, 'Android')) {
            $mobileDevice              = true;
        }

        if (false !== strpos($ua, 'AT10-A')) {
            $device = 'AT10-A';
        } elseif (false !== strpos($ua, 'SM-T235')) {
            $device = 'SM-T235';
        } elseif (false !== strpos($ua, 'SM-T705')) {
            $device = 'SM-T705';
        } elseif (false !== strpos($ua, 'SM-T2105')) {
            $device = 'SM-T2105';
        } elseif (false !== strpos($ua, 'SM-N900A')) {
            $device = 'SM-N900A';
        } elseif (false !== strpos($ua, 'S5000-F')) {
            $device = 'S5000-F';
        } elseif (false !== strpos($ua, 'S5000-H')) {
            $device = 'S5000-H';
        } elseif (false !== strpos($ua, 'A7600-H')) {
            $device = 'A7600-H';
        } elseif (false !== strpos($ua, 'LG-L160L')) {
            $device = 'LG-L160L';
        } elseif (false !== strpos($ua, 'GT-S5830i')) {
            $device = 'GT-S5830i';
        } elseif (false !== strpos($ua, 'GT-S5830C')) {
            $device = 'GT-S5830c';
        } elseif (false !== strpos($ua, 'GT-S5830')) {
            $device = 'GT-S5830';
        } elseif (false !== strpos($ua, 'MI PAD')) {
            $device = 'Mi Pad';
        } elseif (false !== strpos($ua, 'MI 4W')) {
            $device = 'MI 4W';
        } elseif (false !== strpos($ua, 'MI 4LTE')) {
            $device = 'MI 4LTE';
        }

        return [
            (isset($devices[$device]) ? $devices[$device]['Device_Name'] : 'unknown'),
            (isset($devices[$device]) ? $devices[$device]['Device_Maker'] : 'unknown'),
            (isset($devices[$device]) ? $devices[$device]['Device_Type'] : 'unknown'),
            (isset($devices[$device]) ? $devices[$device]['Device_Pointing_Method'] : 'unknown'),
            (isset($devices[$device]) ? $devices[$device]['Device_Code_Name'] : 'unknown'),
            (isset($devices[$device]) ? $devices[$device]['Device_Brand_Name'] : 'unknown'),
            $mobileDevice,
            (isset($devices[$device]['Device_Type']) && 'Tablet' === $devices[$device]['Device_Type'] ? true : false),
        ];

        /*
        $outputBrowscap .= "    'issue-$issue-$i' => [
        'ua' => '" . str_replace(['\\', "'"], ['\\\\', "\\'"], $ua) . "',
        'properties' => [
            'Comment' => 'Default Browser',
            'Browser' => '" . str_replace(['\\', "'"], ['\\\\', "\\'"], $browserNameBrowscap) . "',
            'Browser_Type' => '$browserType',
            'Browser_Bits' => '$browserBits',
            'Browser_Maker' => '$browserMaker',
            'Browser_Modus' => '$browserModus',
            'Version' => '$browserVersion',
            'MajorVer' => '$maxVersion',
            'MinorVer' => '$minVersion',
            'Platform' => '$platformNameBrowscap',
            'Platform_Version' => '$platformVersionBrowscap',
            'Platform_Description' => '$platformDescriptionBrowscap',
            'Platform_Bits' => '$platformBits',
            'Platform_Maker' => '$platformMakerBrowscap',
            'Alpha' => false,
            'Beta' => false,
            'Win16' => " . ($win16 ? 'true' : 'false') . ",
            'Win32' => " . ($win32 ? 'true' : 'false') . ",
            'Win64' => " . ($win64 ? 'true' : 'false') . ",
            'Frames' => true,
            'IFrames' => true,
            'Tables' => true,
            'Cookies' => true,
            'BackgroundSounds' => " . ($activex ? 'true' : 'false') . ",
            'JavaScript' => true,
            'VBScript' => " . ($activex ? 'true' : 'false') . ",
            'JavaApplets' => " . ($applets ? 'true' : 'false') . ",
            'ActiveXControls' => " . ($activex ? 'true' : 'false') . ",
            'isMobileDevice' => " . ($mobileDevice ? 'true' : 'false') . ",
            'isTablet' => " . (isset($devices[$device]['Device_Type']) && 'Tablet' === $devices[$device]['Device_Type'] ? 'true' : 'false') . ",
            'isSyndicationReader' => false,
            'Crawler' => " . ($crawler ? 'true' : 'false') . ",
            'isFake' => false,
            'isAnonymized' => false,
            'isModified' => false,
            'CssVersion' => '0',
            'AolVersion' => '0',
            'Device_Name' => '" . (isset($devices[$device]) ? $devices[$device]['Device_Name'] : 'unknown') . "',
            'Device_Maker' => '" . (isset($devices[$device]) ? $devices[$device]['Device_Maker'] : 'unknown') . "',
            'Device_Type' => '" . (isset($devices[$device]) ? $devices[$device]['Device_Type'] : 'unknown') . "',
            'Device_Pointing_Method' => '" . (isset($devices[$device]) ? $devices[$device]['Device_Pointing_Method']
                : 'unknown') . "',
            'Device_Code_Name' => '" . (isset($devices[$device]) ? $devices[$device]['Device_Code_Name'] : 'unknown') . "',
            'Device_Brand_Name' => '" . (isset($devices[$device]) ? $devices[$device]['Device_Brand_Name']
                : 'unknown') . "',
            'RenderingEngine_Name' => '$engineName',
            'RenderingEngine_Version' => '$engineVersion',
            'RenderingEngine_Maker' => '$engineMaker',
        ],
        'lite' => " . ($lite ? 'true' : 'false') . ",
        'standard' => " . ($standard ? 'true' : 'false') . ",
    ],\n";

        $formatedIssue   = sprintf('%1$05d', (int) $issue);
        $formatedCounter = sprintf('%1$05d', (int) $counter);

        $outputDetector['browscap-issue-' . $formatedIssue . '-' . $formatedCounter] = [
            'ua' => $ua,
            'properties' => [
                'Browser_Name'            => $browserNameDetector,
                'Browser_Type'            => $browserType,
                'Browser_Bits'            => $browserBits,
                'Browser_Maker'           => $browserMaker,
                'Browser_Modus'           => $browserModus,
                'Browser_Version'         => $browserVersion,
                'Platform_Codename'       => $platformCodenameDetector,
                'Platform_Marketingname'  => $platformMarketingnameDetector,
                'Platform_Version'        => $platformVersionDetector,
                'Platform_Bits'           => $platformBits,
                'Platform_Maker'          => $platformMakerNameDetector,
                'Platform_Brand_Name'     => $platformMakerBrandnameDetector,
                'Device_Name'             => (isset($devices[$device]) ? $devices[$device]['Device_Name'] : 'unknown'),
                'Device_Maker'            => (isset($devices[$device]) ? $devices[$device]['Device_Maker'] : 'unknown'),
                'Device_Type'             => (isset($devices[$device]) ? $devices[$device]['Device_Type'] : 'unknown'),
                'Device_Pointing_Method'  => (isset($devices[$device]) ? $devices[$device]['Device_Pointing_Method'] : 'unknown'),
                'Device_Dual_Orientation' => false,
                'Device_Code_Name'        => (isset($devices[$device]) ? $devices[$device]['Device_Code_Name'] : 'unknown'),
                'Device_Brand_Name'       => (isset($devices[$device]) ? $devices[$device]['Device_Brand_Name'] : 'unknown'),
                'RenderingEngine_Name'    => $engineName,
                'RenderingEngine_Version' => $engineVersion,
                'RenderingEngine_Maker'   => $engineMaker,
            ],
        ];

        ++$counter;

        $checks[$ua] = $i;

        return;
        /**/
    }
}
