<?php
/**
 * This file is part of the browscap-helper package.
 *
 * Copyright (c) 2015-2020, Thomas Mueller <mimmi20@live.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);
namespace BrowscapHelper\Command\Helper;

use BrowscapHelper\Source\SourceInterface;
use Symfony\Component\Console\Helper\Helper;
use Symfony\Component\Console\Output\OutputInterface;

final class ExistingTestsLoader extends Helper
{
    public function getName()
    {
        return 'existing-tests-loader';
    }

    /**
     * @param OutputInterface   $output,
     * @param SourceInterface[] $sources
     *
     * @throws \LogicException
     * @throws \RuntimeException
     *
     * @return array[]|iterable
     */
    public function getHeaders(OutputInterface $output, array $sources): iterable
    {
        $output->writeln('reading sources ...', OutputInterface::VERBOSITY_NORMAL);

        $messageLength = 0;

        foreach ($sources as $source) {
            $message = sprintf('reading from source %s ...', $source->getName());

            if (mb_strlen($message) > $messageLength) {
                $messageLength = mb_strlen($message);
            }

            $output->write("\r" . '<info>' . str_pad($message, $messageLength, ' ', STR_PAD_RIGHT) . '</info>', false, OutputInterface::VERBOSITY_VERBOSE);

            yield from $source->getHeaders();
        }
    }
}
