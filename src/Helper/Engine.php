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
class Engine
{
    /**
     * @param string $ua
     *
     * @return array
     */
    public function detect($ua)
    {
        $engineName  = 'unknown';
        $engineMaker = 'unknown';

        $applets      = false;
        $activex      = false;

        $chromeVersion = 0;

        if (false !== strpos($ua, 'Chrome')) {
            if (preg_match('/Chrome\/(\d+\.\d+)/', $ua, $matches)) {
                $chromeVersion = (float) $matches[1];
            }
        }

        if (false !== strpos($ua, ' U3/')) {
            $engineName  = 'U3';
            $engineMaker = 'UC Web';
        } elseif (false !== strpos($ua, ' U2/')) {
            $engineName  = 'U2';
            $engineMaker = 'UC Web';
        } elseif (false !== strpos($ua, ' T5/')) {
            $engineName  = 'T5';
            $engineMaker = 'Baidu';
        } elseif (false !== strpos($ua, 'AppleWebKit')) {
            if ($chromeVersion >= 28.0) {
                $engineName  = 'Blink';
                $engineMaker = 'Google Inc';
            } else {
                $engineName  = 'WebKit';
                $engineMaker = 'Apple Inc';
                $applets     = true;
            }
        } elseif (false !== strpos($ua, 'Presto')) {
            $engineName  = 'Presto';
            $engineMaker = 'Opera Software ASA';
        } elseif (false !== strpos($ua, 'Trident')) {
            $engineName  = 'Trident';
            $engineMaker = 'Microsoft Corporation';
            $applets     = true;
            $activex     = true;
        } elseif (false !== strpos($ua, 'Gecko')) {
            $engineName  = 'Gecko';
            $engineMaker = 'Mozilla Foundation';
            $applets     = true;
        }

        return [
            $engineName,
            $engineMaker,
            $applets,
            $activex,
        ];
    }
}
