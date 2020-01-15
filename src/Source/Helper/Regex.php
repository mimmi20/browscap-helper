<?php
/**
 * This file is part of the browscap-helper-source package.
 *
 * Copyright (c) 2016-2019, Thomas Mueller <mimmi20@live.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);
namespace BrowscapHelper\Source\Helper;

final class Regex
{
    /**
     * @return string
     */
    public function getRegex(): string
    {
        return '/^'
            . '(?P<remotehost>\S+)'              // remote host (IP)
            . '\s+'
            . '(?P<logname>\S+)'                 // remote logname
            . '\s+'
            . '(?P<user>\S+)'                    // remote user
            . '[^\[]+'
            . '\[(?P<time>[^\]]+)\]'             // date/time
            . '[^"]+'
            . '\"(?P<http>.*)\"'                 // Verb(GET|POST|HEAD) Path HTTP Version
            . '\s+'
            . '(?P<status>\d+)'                  // Status
            . '\D+'
            . '(?P<length>\d+)'                  // Length (include Header)
            . '[^\d"]+'
            . '\"(?P<referrer>.*)\"'             // Referrer
            . '[^"]+'
            . '\"(?P<userAgentString>[^"]*)\".*' // User Agent
            . '$/x';
    }
}
