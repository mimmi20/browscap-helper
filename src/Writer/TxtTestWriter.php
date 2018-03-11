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
namespace BrowscapHelper\Writer;

class TxtTestWriter
{
    /**
     * @var string[]
     */
    private $outputTxt = [];

    /**
     * @param string $useragent
     * @param string $dir
     * @param int    $number
     *
     * @return void
     */
    public function write(string $useragent, string $dir, int $number): void
    {
        $this->outputTxt[] = $useragent;

        file_put_contents(
            $dir . sprintf('%1$07d', $number) . '.txt',
            implode(PHP_EOL, $this->outputTxt) . PHP_EOL
        );
    }
}
