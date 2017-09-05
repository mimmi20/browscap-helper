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
namespace BrowscapHelper\Source\Helper;

/**
 * Class DiffCommand
 *
 * @category   Browscap
 *
 * @author     James Titcumb <james@asgrim.com>
 */
class Regex
{
    /**
     * @return string
     */
    public function getRegex(): string
    {
        return '/^'
            . '(?P<remotehost>\S+)'             // remote host (IP)
            . '\s+'
            . '(?P<logname>\S+)'                // remote logname
            . '\s+'
            . '(?P<user>\S+)'                   // remote user
            . '.*'
            . '\[(?P<time>[^\]]+)\]'             // date/time
            . '[^"]+'
            . '\"(?P<http>.*)\"'                // Verb(GET|POST|HEAD) Path HTTP Version
            . '\s+'
            . '(?P<status>.*)'                  // Status
            . '\s+'
            . '(?P<length>.*)'                  // Length (include Header)
            . '[^"]+'
            . '\"(?P<referrer>.*)\"'            // Referrer
            . '[^"]+'
            . '\"(?P<userAgentString>.*)\".*'   // User Agent
            . '$/x';
    }
}
