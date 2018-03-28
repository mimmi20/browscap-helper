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
namespace BrowscapHelper\Command\Helper;

use BrowscapHelper\Source\SourceInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Helper\Helper;

class Useragent extends Helper
{
    /**
     * an logger instance
     *
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var int[]
     */
    private $allAgents = [];

    /**
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function getName()
    {
        return 'useragent';
    }

    /**
     * @param SourceInterface $source
     * @param bool            $skipDuplicates
     *
     * @return \Generator
     */
    public function getUserAgents(SourceInterface $source, bool $skipDuplicates = true): \Generator
    {
        foreach ($source->getUserAgents() as $useragent) {
            $useragent = trim(str_replace(["\r\n", "\r", "\n"], '\n', $useragent));

            if ($skipDuplicates && array_key_exists($useragent, $this->allAgents)) {
                $this->logger->debug('    UA "' . $useragent . '" added more than once --> skipped');
                continue;
            }

            $this->allAgents[$useragent] = 1;

            yield $useragent;
        }
    }
}
