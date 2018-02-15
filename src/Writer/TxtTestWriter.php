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

use Psr\Log\LoggerInterface;

class TxtTestWriter
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    private $outputTxt = [];

    private $chunkCounter = 0;

    /**
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param string $useragent
     * @param string $dir
     * @param int    $number
     * @param int    $totalCounter
     *
     * @return bool
     */
    public function write(string $useragent, string $dir, int $number, int &$totalCounter): bool
    {
        $this->outputTxt[] = $useragent;

        file_put_contents(
            $dir . sprintf('%1$07d', $number) . '.txt',
            implode(PHP_EOL, $this->outputTxt) . PHP_EOL
        );

        ++$this->chunkCounter;
        ++$totalCounter;

        if (1000 <= $this->chunkCounter) {
            $this->chunkCounter = 0;
            $this->outputTxt    = [];

            return true;
        }

        return false;
    }
}
