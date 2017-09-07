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

use BrowserDetector\Helper\GenericRequestFactory;
use FileLoader\Loader;
use Psr\Log\LoggerInterface;
use Symfony\Component\Finder\Finder;
use UaResult\Browser\Browser;
use UaResult\Device\Device;
use UaResult\Engine\Engine;
use UaResult\Os\Os;
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
     * @param string $file
     */
    public function __construct(LoggerInterface $logger, string $dir)
    {
        $this->logger = $logger;
        $this->dir    = $dir;
    }

    /**
     * @param \UaResult\Result\ResultInterface        $result
     * @param int $number
     * @param int $totalCounter
     *
     * @return bool
     */
    public function write(ResultInterface $result, int $number, int &$totalCounter): bool
    {
        $formatedIssue   = sprintf('%1$07d', $number);
        $formatedCounter = sprintf('%1$05d', $this->counter);

        $this->outputDetector['test-' . $formatedIssue . '-' . $formatedCounter] = [
            'ua'     => $useragent,
            'result' => $result->toArray(),
        ];

        file_put_contents(
            $this->dir . 'test-' . sprintf('%1$07d', $number) . '-' . sprintf('%1$03d', $this->fileCounter) . '.json',
            json_encode(
                $this->outputDetector,
                JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_FORCE_OBJECT
            ) . PHP_EOL
        );
        ++$this->counter;
        ++$this->chunkCounter;
        ++$totalCounter;

        if ($this->chunkCounter >= 100) {
            $this->chunkCounter   = 0;
            $this->outputDetector = [];
            ++$this->fileCounter;
        }

        if ($this->fileCounter >= 10) {
            $this->chunkCounter    = 0;
            $this->outputDetector  = [];
            $this->fileCounter     = 0;
            $this->counter         = 0;

            return true;
        }

        return false;
    }
}
