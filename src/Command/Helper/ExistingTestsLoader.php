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

use BrowscapHelper\Source\SourceInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Helper\Helper;

class ExistingTestsLoader extends Helper
{
    public function getName()
    {
        return 'existing-tests-reader';
    }

    /**
     * @param \Psr\Log\LoggerInterface $logger
     * @param SourceInterface[]        $sources
     *
     * @return iterable|string[]
     */
    public function getHeaders(LoggerInterface $logger, array $sources): iterable
    {
        foreach ($sources as $source) {
            $logger->info(sprintf('    reading from source %s ...', $source->getName()));

            yield from $source->getHeaders();
        }
    }
}
