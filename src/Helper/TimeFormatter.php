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

/**
 * BrowserDetectorModule.ini parsing class with caching and update capabilities
 *
 * @category  BrowserDetectorModule
 *
 * @author    Thomas Mueller <mimmi20@live.de>
 * @copyright 2012-2014 Thomas Mueller
 * @license   http://www.opensource.org/licenses/MIT MIT License
 */
class TimeFormatter
{
    /**
     * @param string $time
     *
     * @return string
     */
    public static function formatTime(string $time): string
    {
        $wochen      = (int) ((int) $time / 604800);
        $restwoche   = (int) ((int) $time % 604800);
        $tage        = (int) ($restwoche / 86400);
        $resttage    = (int) ($restwoche % 86400);
        $stunden     = (int) ($resttage / 3600);
        $reststunden = (int) ($resttage % 3600);
        $minuten     = (int) ($reststunden / 60);
        $sekunden    = (int) ($reststunden % 60);

        return mb_substr('00' . $wochen, -2) . ' Wochen '
            . mb_substr('00' . $tage, -2) . ' Tage '
            . mb_substr('00' . $stunden, -2) . ' Stunden '
            . mb_substr('00' . $minuten, -2) . ' Minuten '
            . mb_substr('00' . $sekunden, -2) . ' Sekunden';
    }
}
