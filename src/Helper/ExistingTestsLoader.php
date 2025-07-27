<?php

/**
 * This file is part of the browscap-helper package.
 *
 * Copyright (c) 2015-2025, Thomas Mueller <mimmi20@live.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);

namespace BrowscapHelper\Helper;

use BrowscapHelper\Source\OutputAwareInterface;
use BrowscapHelper\Source\SourceInterface;
use RuntimeException;
use Symfony\Component\Console\Helper\Helper;
use Symfony\Component\Console\Output\OutputInterface;

use function mb_str_pad;
use function mb_strlen;
use function sprintf;

final class ExistingTestsLoader
{
    /**
     * @param array<SourceInterface> $sources
     *
     * @return iterable<array<string, string>>
     * @phpstan-return iterable<non-empty-string, array{headers: array<non-empty-string, non-empty-string>, device: array{deviceName: string|null, marketingName: string|null, manufacturer: string|null, brand: string|null, display: array{width: int|null, height: int|null, touch: bool|null, type: string|null, size: float|int|null}, type: string|null, ismobile: bool|null}, client: array{name: string|null, modus: string|null, version: string|null, manufacturer: string|null, bits: int|null, type: string|null, isbot: bool|null}, platform: array{name: string|null, marketingName: string|null, version: string|null, manufacturer: string|null, bits: int|null}, engine: array{name: string|null, version: string|null, manufacturer: string|null}}>
     *
     * @throws RuntimeException
     */
    public function getProperties(OutputInterface $output, array $sources, int &$messageLength = 0): iterable
    {
        $baseMessage   = 'reading sources';
        $message       = $baseMessage . ' ...';
        $diff = $this->messageLength($output, $message, $messageLength);

        $output->writeln(
            mb_str_pad(string: $message, length: $messageLength + $diff),
            OutputInterface::VERBOSITY_NORMAL,
        );

        foreach ($sources as $source) {
            if ($source instanceof OutputAwareInterface) {
                $source->setOutput($output);
            }

            $baseMessage = sprintf('reading from source %s ', $source->getName());

            if (!$source->isReady($baseMessage)) {
                continue;
            }

            $message = '<info>' . $baseMessage . '...</info>';

            $diff = $this->messageLength($output, $message, $messageLength);

            $output->write(
                "\r" . mb_str_pad(string: $message, length: $messageLength + $diff),
                false,
                OutputInterface::VERBOSITY_VERBOSE,
            );

            yield from $source->getProperties($baseMessage, $messageLength);

            $message = '<info>' . $baseMessage . '- done</info>';

            $diff = $this->messageLength($output, $message, $messageLength);

            $output->writeln(
                "\r" . mb_str_pad(string: $message, length: $messageLength + $diff),
                OutputInterface::VERBOSITY_VERBOSE,
            );
        }
    }

    /** @throws void */
    private function messageLength(OutputInterface $output, string $message, int &$messageLength): int
    {
        $messageLengthWithoutFormat = Helper::width(Helper::removeDecoration($output->getFormatter(), $message));
        $messageLengthWithFormat    = Helper::width($message);

        $messageLength = min(
            max(
                $messageLength,
                $messageLengthWithFormat,
            ),
            200,
        );

        return $messageLengthWithFormat - $messageLengthWithoutFormat;
    }
}
