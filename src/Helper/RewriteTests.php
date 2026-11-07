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

use BrowscapHelper\Entity\TestData;
use Ergebnis\Json\Normalizer\Exception\InvalidIndentSize;
use Ergebnis\Json\Normalizer\Exception\InvalidIndentStyle;
use Ergebnis\Json\Normalizer\Exception\InvalidJsonEncodeOptions;
use Ergebnis\Json\Normalizer\Exception\InvalidNewLineString;
use RuntimeException;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;
use UnexpectedValueException;

use function array_chunk;
use function array_keys;
use function array_map;
use function array_values;
use function file_put_contents;
use function mb_str_pad;
use function mb_strlen;
use function sprintf;
use function var_export;

final readonly class RewriteTests
{
    /** @throws void */
    public function __construct(private JsonNormalizer $jsonNormalizer)
    {
        // nothing to do
    }

    /**
     * @param non-empty-array<string, TestData> $txtChecks
     *
     * @throws InvalidJsonEncodeOptions
     * @throws InvalidNewLineString
     * @throws InvalidIndentStyle
     * @throws InvalidIndentSize
     * @throws UnexpectedValueException
     *
     * @phpcs:disable SlevomatCodingStandard.Functions.FunctionLength.FunctionLength
     */
    public function rewrite(OutputInterface $output, array $txtChecks, string $testSource): void
    {
        /** @var non-empty-array<int, non-empty-array<string, TestData>> $fileChunks */
        $fileChunks = array_chunk($txtChecks, 1000, preserve_keys: true);
        $this->jsonNormalizer->init($output);

        $baseMessage   = 'rewriting files';
        $message       = $baseMessage . ' ...';
        $messageLength = mb_strlen($message);

        $output->writeln(
            mb_str_pad(string: $message, length: $messageLength),
            OutputInterface::VERBOSITY_NORMAL,
        );

        foreach ($fileChunks as $fileId => $fileChunk) {
            $fileNameJson = $testSource . '/' . sprintf('%1$07d', $fileId) . '.json';

            $message  = $baseMessage . sprintf(' %s', $fileNameJson);
            $message2 = $message . ' - pre-check';

            if (mb_strlen($message2) > $messageLength) {
                $messageLength = mb_strlen($message2);
            }

            $output->write(
                "\r" . '<info>' . mb_str_pad(string: $message2, length: $messageLength) . '</info>',
                newline: false,
                options: OutputInterface::VERBOSITY_VERY_VERBOSE,
            );

            /** @var non-empty-list<array{headers: non-empty-array<non-empty-string, non-empty-string>, date-first: non-falsy-string, date-last: non-falsy-string}|null> $testCases */
            $testCases = array_map(
                static function (string $headerString, TestData $test) use ($output, $messageLength): array | null {
                    try {
                        $headerArray = Yaml::parse($headerString);
                    } catch (ParseException $e) {
                        $output->writeln(
                            '',
                            options: OutputInterface::VERBOSITY_VERY_VERBOSE,
                        );
                        $output->writeln(
                            "\r" . '<error>' . $e . '</error>',
                            options: OutputInterface::VERBOSITY_NORMAL,
                        );

                        return null;
                    }

                    if ($headerArray === []) {
                        $output->writeln(
                            '',
                            options: OutputInterface::VERBOSITY_VERY_VERBOSE,
                        );
                        $output->writeln(
                            "\r" . '<info>' . mb_str_pad(
                                string: 'not array: ' . $headerString . ' [' . var_export(
                                    $headerString,
                                    return: true,
                                ) . ']',
                                length: $messageLength,
                            ) . '</info>',
                            options: OutputInterface::VERBOSITY_NORMAL,
                        );

                        return null;
                    }

                    if ($test->getHeaders() === []) {
                        $output->writeln(
                            '',
                            options: OutputInterface::VERBOSITY_VERY_VERBOSE,
                        );
                        $output->writeln(
                            "\r" . '<info>' . mb_str_pad(
                                string: 'no headers: ' . $headerString,
                                length: $messageLength,
                            ) . '</info>',
                            options: OutputInterface::VERBOSITY_NORMAL,
                        );

                        return null;
                    }

                    return [
                        'headers' => $test->getHeaders(),
                        'date-first' => $test->getDateFirst()->format('Y-m-d'),
                        'date-last' => $test->getDateLast()->format('Y-m-d'),
                    ];
                },
                array_keys($fileChunk),
                array_values($fileChunk),
            );

            $message2 = $message . ' - writing Json';

            if (mb_strlen($message2) > $messageLength) {
                $messageLength = mb_strlen($message2);
            }

            $output->write(
                "\r" . '<info>' . mb_str_pad(string: $message2, length: $messageLength) . '</info>',
                newline: false,
                options: OutputInterface::VERBOSITY_VERY_VERBOSE,
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
                    '<error>' . sprintf('normalisation failed for file %s', $fileNameJson) . '</error>',
                    OutputInterface::VERBOSITY_NORMAL,
                );

                continue;
            }

            file_put_contents($fileNameJson, $normalized);
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
