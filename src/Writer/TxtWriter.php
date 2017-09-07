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

class TxtWriter
{
    /**
     * @var string
     */
    private $file;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @param \Psr\Log\LoggerInterface $logger
     * @param string                   $file
     */
    public function __construct(LoggerInterface $logger, string $file)
    {
        $this->logger = $logger;
        $this->file   = $file;
    }

    /**
     * @param string $useragent
     *
     * @return bool
     */
    public function write(string $useragent): bool
    {
        file_put_contents($this->file, trim($useragent) . "\n", FILE_APPEND | LOCK_EX);

        return false;
    }
}
