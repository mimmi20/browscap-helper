<?php

/**
 * This file is part of the browscap-helper package.
 *
 * Copyright (c) 2015-2026, Thomas Mueller <mimmi20@live.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);

namespace BrowscapHelper\Helper;

use BrowscapHelper\Source\Ua\UserAgent;
use DateTimeImmutable;
use Ergebnis\Json\Normalizer\Exception\InvalidIndentSize;
use Ergebnis\Json\Normalizer\Exception\InvalidIndentStyle;
use Ergebnis\Json\Normalizer\Exception\InvalidJsonEncodeOptions;
use Ergebnis\Json\Normalizer\Exception\InvalidNewLineString;
use RuntimeException;
use Symfony\Component\Console\Output\OutputInterface;
use UnexpectedValueException;

use function array_chunk;
use function file_put_contents;
use function mb_str_pad;
use function mb_strlen;
use function sprintf;

final readonly class RewriteTests
{
    /** @throws void */
    public function __construct(private JsonNormalizer $jsonNormalizer)
    {
        // nothing to do
    }

    /**
     * @param array<string, array{headers: array<string, string>, date-first: DateTimeImmutable|false, date-last: DateTimeImmutable|false}> $txtChecks
     *
     * @throws InvalidJsonEncodeOptions
     * @throws InvalidNewLineString
     * @throws InvalidIndentStyle
     * @throws InvalidIndentSize
     * @throws UnexpectedValueException
     */
    public function rewrite(OutputInterface $output, array $txtChecks, string $testSource): void
    {
        $folderChunks = array_chunk($txtChecks, 1000, true);
        $this->jsonNormalizer->init($output);

        $baseMessage   = 'rewriting files';
        $message       = $baseMessage . ' ...';
        $messageLength = mb_strlen($message);

        $output->writeln(
            mb_str_pad(string: $message, length: $messageLength),
            OutputInterface::VERBOSITY_NORMAL,
        );

        foreach ($folderChunks as $folderId => $folderChunk) {
            $testCases = [];

            $fileName = $testSource . '/' . sprintf('%1$07d', $folderId) . '.json';

            $message  = $baseMessage . sprintf(' %s', $fileName);
            $message2 = $message . ' - pre-check';

            if (mb_strlen($message2) > $messageLength) {
                $messageLength = mb_strlen($message2);
            }

            $output->write(
                "\r" . '<info>' . mb_str_pad(string: $message2, length: $messageLength) . '</info>',
                false,
                OutputInterface::VERBOSITY_VERY_VERBOSE,
            );

            foreach ($folderChunk as $headerString => $test) {
                $headerArray = UserAgent::fromString($headerString)->getHeaders();

                if ($headerArray === []) {
                    continue;
                }

                if (!$test['date-first'] instanceof DateTimeImmutable) {
                    continue;
                }

                if (!$test['date-last'] instanceof DateTimeImmutable) {
                    continue;
                }

                $testCases[] = [
                    'headers' => $test['headers'],
                    'date-first' => $test['date-first']->format('Y-m-d'),
                    'date-last' => $test['date-last']->format('Y-m-d'),
                ];
            }

            $message2 = $message . ' - normalizing';

            if (mb_strlen($message2) > $messageLength) {
                $messageLength = mb_strlen($message2);
            }

            $output->write(
                "\r" . '<info>' . mb_str_pad(string: $message2, length: $messageLength) . '</info>',
                false,
                OutputInterface::VERBOSITY_VERY_VERBOSE,
            );

            try {
                $normalized = $this->jsonNormalizer->normalize(
                    $output,
                    $testCases,
                    $message,
                    $messageLength,
                );
            } catch (RuntimeException $e) {
                $output->writeln('', OutputInterface::VERBOSITY_VERBOSE);
                $output->writeln('<error>' . $e . '</error>', OutputInterface::VERBOSITY_NORMAL);

                continue;
            }

            if ($normalized === null) {
                $output->writeln('', OutputInterface::VERBOSITY_VERBOSE);
                $output->writeln(
                    '<error>' . sprintf('normalisation failed for file %s', $fileName) . '</error>',
                    OutputInterface::VERBOSITY_NORMAL,
                );

                continue;
            }

            $message2 = $message . ' - writing';

            if (mb_strlen($message2) > $messageLength) {
                $messageLength = mb_strlen($message2);
            }

            $output->write(
                "\r" . '<info>' . mb_str_pad(string: $message2, length: $messageLength) . '</info>',
                false,
                OutputInterface::VERBOSITY_VERY_VERBOSE,
            );

            file_put_contents($fileName, $normalized);
        }

        $message = $baseMessage . ' - done';

        if (mb_strlen($message) > $messageLength) {
            $messageLength = mb_strlen($message);
        }

        $output->writeln(
            "\r" . '<info>' . mb_str_pad(string: $message, length: $messageLength) . '</info>',
            OutputInterface::VERBOSITY_VERBOSE,
        );
    }
}
