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
namespace BrowscapHelper\Writer;

use Psr\Log\LoggerInterface;
use UaResult\Result\Result;
use UaResult\Result\ResultInterface;

class DetectorTestWriter
{
    /**
     * @var string
     */
    private $dir;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    private $outputDetector = [];
    private $counter        = 0;
    private $chunkCounter   = 0;
    private $fileCounter    = 0;

    /**
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param \UaResult\Result\ResultInterface $result
     * @param string                           $dir
     * @param int                              $number
     * @param string                           $useragent
     * @param int                              $totalCounter
     *
     * @return bool
     */
    public function write(ResultInterface $result, string $dir, int $number, string $useragent, int &$totalCounter): bool
    {
        $formatedIssue   = sprintf('%1$07d', $number);
        $formatedCounter = sprintf('%1$05d', $this->counter);

        $this->outputDetector['test-' . $formatedIssue . '-' . $formatedCounter] = [
            'ua'     => $useragent,
            'result' => $result->toArray(),
        ];

        $content = json_encode(
            $this->outputDetector,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_FORCE_OBJECT
        );

        if (false === $content) {
            $this->logger->critical('could not encode content');

            return false;
        }

        file_put_contents(
            $dir . 'test-' . sprintf('%1$07d', $number) . '-' . sprintf('%1$03d', $this->fileCounter) . '.json',
            $content . PHP_EOL
        );
        ++$this->counter;
        ++$this->chunkCounter;
        ++$totalCounter;

        if (100 <= $this->chunkCounter) {
            $this->chunkCounter   = 0;
            $this->outputDetector = [];
            ++$this->fileCounter;
        }

        if (10 <= $this->fileCounter) {
            $this->chunkCounter    = 0;
            $this->outputDetector  = [];
            $this->fileCounter     = 0;
            $this->counter         = 0;

            return true;
        }

        return false;
    }
}
