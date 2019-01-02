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

use Symfony\Component\Console\Helper\Helper;

class TxtTestWriter extends Helper
{
    public function getName()
    {
        return 'txt-test-writer';
    }

    /**
     * @param array  $useragents
     * @param string $dir
     * @param int    $number
     *
     * @return void
     */
    public function write(array $useragents, string $dir, int $number): void
    {
        file_put_contents(
            $dir . '/' . sprintf('%1$07d', $number) . '.txt',
            implode(PHP_EOL, $useragents) . PHP_EOL
        );
    }
}
