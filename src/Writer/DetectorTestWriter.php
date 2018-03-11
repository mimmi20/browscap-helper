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
use UaResult\Result\ResultInterface;

class DetectorTestWriter
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    private $outputDetector = [];

    private $counter = 0;

    private $fileCounter = 0;

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
     *
     * @return void
     */
    public function write(ResultInterface $result, string $dir, int $number): void
    {
        $formatedIssue   = sprintf('%1$07d', $number);
        $formatedCounter = sprintf('%1$05d', $this->counter);

        $this->outputDetector['test-' . $formatedIssue . '-' . $formatedCounter] = $result->toArray();

        $content = json_encode(
            $this->outputDetector,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_FORCE_OBJECT
        );

        if (false === $content) {
            $this->logger->critical('could not encode content');

            return;
        }

        file_put_contents(
            $dir . 'test-' . sprintf('%1$07d', $number) . '-' . sprintf('%1$03d', $this->fileCounter) . '.json',
            $content . PHP_EOL
        );
    }
}
