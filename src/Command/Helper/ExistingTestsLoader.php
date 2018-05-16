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
use Symfony\Component\Console\Output\OutputInterface;

/**
 * reading existing tests
 *
 * @category  BrowserDetector
 */
class ExistingTestsLoader extends Helper
{
    /**
     * an logger instance
     *
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function getName()
    {
        return 'existing-tests-reader';
    }

    /**
     * @param OutputInterface $output
     * @param SourceInterface $source
     *
     * @return iterable|string[]
     */
    public function getHeaders(OutputInterface $output, SourceInterface $source): iterable
    {
        $output->writeln('reading already existing tests ...');

        /** @var Useragent $useragentLoader */
        $useragentLoader = $this->getHelperSet()->get('useragent');

        yield from $useragentLoader->getHeaders($source, false);
    }
}
