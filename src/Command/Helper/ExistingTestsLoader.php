<?php
/**
 * This file is part of the browscap-helper package.
 *
 * Copyright (c) 2015-2021, Thomas Mueller <mimmi20@live.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);
namespace BrowscapHelper\Command\Helper;

use BrowscapHelper\Source\OutputAwareInterface;
use BrowscapHelper\Source\SourceInterface;
use Symfony\Component\Console\Helper\Helper;
use Symfony\Component\Console\Output\OutputInterface;

final class ExistingTestsLoader extends Helper
{
    /**
     * @return string
     */
    public function getName(): string
    {
        return 'existing-tests-loader';
    }

    /**
     * @param OutputInterface   $output
     * @param SourceInterface[] $sources
     *
     * @throws \LogicException
     * @throws \RuntimeException
     *
     * @return array[]|iterable
     */
    public function getHeaders(OutputInterface $output, array $sources): iterable
    {
        $baseMessage   = 'reading sources';
        $messageLength = 0;

        $message = $baseMessage . ' ...';

        if (mb_strlen($message) > $messageLength) {
            $messageLength = mb_strlen($message);
        }

        $output->writeln(str_pad($message, $messageLength, ' ', STR_PAD_RIGHT), OutputInterface::VERBOSITY_NORMAL);

        foreach ($sources as $source) {
            if ($source instanceof OutputAwareInterface) {
                $source->setOutput($output);
            }

            $baseMessage = sprintf('reading from source %s ', $source->getName());

            if (!$source->isReady($baseMessage)) {
                continue;
            }

            $message = $baseMessage . '...';

            if (mb_strlen($message) > $messageLength) {
                $messageLength = mb_strlen($message);
            }

            $output->write("\r" . '<info>' . str_pad($message, $messageLength, ' ', STR_PAD_RIGHT) . '</info>', false, OutputInterface::VERBOSITY_VERBOSE);

            yield from $source->getHeaders($baseMessage, $messageLength);

            $message = $baseMessage . '- done';

            if (mb_strlen($message) > $messageLength) {
                $messageLength = mb_strlen($message);
            }

            $output->writeln("\r" . '<info>' . str_pad($message, $messageLength, ' ', STR_PAD_RIGHT) . '</info>', OutputInterface::VERBOSITY_VERBOSE);
        }
    }
}
