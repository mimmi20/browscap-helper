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

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

use function array_chunk;
use function array_keys;
use function array_map;
use function array_values;
use function file_put_contents;
use function is_string;
use function mb_str_pad;
use function mb_strlen;
use function sprintf;
use function var_export;

final readonly class RewriteTests
{
    /** @throws void */
    public function __construct()
    {
        // nothing to do
    }

    /**
     * @param array<string, array{headers: array<string, string>, date-first: string, date-last: string}> $txtChecks
     *
     * @throws void
     *
     * @phpcs:disable SlevomatCodingStandard.Functions.FunctionLength.FunctionLength
     */
    public function rewrite(OutputInterface $output, array $txtChecks, string $testSource): void
    {
        $fileChunks = array_chunk($txtChecks, 1000, preserve_keys: true);

        $baseMessage   = 'rewriting files';
        $message       = $baseMessage . ' ...';
        $messageLength = mb_strlen($message);

        $output->writeln(
            mb_str_pad(string: $message, length: $messageLength),
            OutputInterface::VERBOSITY_NORMAL,
        );

        foreach ($fileChunks as $fileId => $fileChunk) {
            $fileNameYaml = $testSource . '/' . sprintf('%1$07d', $fileId) . '.yaml';

            $message  = $baseMessage . sprintf(' %s', $fileNameYaml);
            $message2 = $message . ' - pre-check';

            if (mb_strlen($message2) > $messageLength) {
                $messageLength = mb_strlen($message2);
            }

            $output->write(
                "\r" . '<info>' . mb_str_pad(string: $message2, length: $messageLength) . '</info>',
                newline: false,
                options: OutputInterface::VERBOSITY_VERY_VERBOSE,
            );

            $testCases = array_map(
                static function (string $headerString, array $test) use ($output, $messageLength): array | null {
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

                    if (!is_string($test['date-first'])) {
                        $output->writeln(
                            '',
                            options: OutputInterface::VERBOSITY_VERY_VERBOSE,
                        );
                        $output->writeln(
                            "\r" . '<info>' . mb_str_pad(
                                string: 'no date-first: ' . $headerString,
                                length: $messageLength,
                            ) . '</info>',
                            options: OutputInterface::VERBOSITY_NORMAL,
                        );

                        return null;
                    }

                    if (!is_string($test['date-last'])) {
                        $output->writeln(
                            '',
                            options: OutputInterface::VERBOSITY_VERY_VERBOSE,
                        );
                        $output->writeln(
                            "\r" . '<info>' . mb_str_pad(
                                string: 'no date-last: ' . $headerString,
                                length: $messageLength,
                            ) . '</info>',
                            options: OutputInterface::VERBOSITY_NORMAL,
                        );

                        return null;
                    }

                    return [
                        'headers' => $test['headers'],
                        'date-first' => $test['date-first'],
                        'date-last' => $test['date-last'],
                    ];
                },
                array_keys($fileChunk),
                array_values($fileChunk),
            );

            $message2 = $message . ' - writing Yaml';

            if (mb_strlen($message2) > $messageLength) {
                $messageLength = mb_strlen($message2);
            }

            $output->write(
                "\r" . '<info>' . mb_str_pad(string: $message2, length: $messageLength) . '</info>',
                newline: false,
                options: OutputInterface::VERBOSITY_VERY_VERBOSE,
            );

            file_put_contents($fileNameYaml, Yaml::dump($testCases, 12, 2));
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
