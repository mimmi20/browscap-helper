<?php
/**
 * This file is part of the browscap-helper package.
 *
 * Copyright (c) 2015-2023, Thomas Mueller <mimmi20@live.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);

namespace BrowscapHelper\Helper;

use BrowscapHelper\Normalizer\FormatNormalizer;
use Ergebnis\Json\Exception\NotJson;
use Ergebnis\Json\Json;
use Ergebnis\Json\Normalizer;
use Ergebnis\Json\Normalizer\Exception\InvalidIndentSize;
use Ergebnis\Json\Normalizer\Exception\InvalidIndentStyle;
use Ergebnis\Json\Normalizer\Exception\InvalidJsonEncodeOptions;
use Ergebnis\Json\Normalizer\Exception\InvalidNewLineString;
use Ergebnis\Json\Normalizer\Exception\NormalizedInvalidAccordingToSchema;
use Ergebnis\Json\Normalizer\Exception\OriginalInvalidAccordingToSchema;
use Ergebnis\Json\Normalizer\Exception\SchemaUriCouldNotBeRead;
use Ergebnis\Json\Normalizer\Exception\SchemaUriCouldNotBeResolved;
use Ergebnis\Json\Normalizer\Exception\SchemaUriReferencesDocumentWithInvalidMediaType;
use Ergebnis\Json\Normalizer\Exception\SchemaUriReferencesInvalidJsonDocument;
use Ergebnis\Json\Normalizer\Normalizer as NormalizerInterface;
use Exception;
use JsonException;
use Symfony\Component\Console\Output\OutputInterface;
use UnexpectedValueException;

use function assert;
use function json_encode;
use function mb_strlen;
use function sprintf;
use function str_pad;

use const JSON_PRETTY_PRINT;
use const JSON_THROW_ON_ERROR;
use const JSON_UNESCAPED_SLASHES;
use const JSON_UNESCAPED_UNICODE;

final class JsonNormalizer
{
    /** @var array<NormalizerInterface> */
    private array $normalizers = [];

    /**
     * @throws InvalidJsonEncodeOptions
     * @throws InvalidNewLineString
     * @throws InvalidIndentStyle
     * @throws InvalidIndentSize
     * @throws UnexpectedValueException
     */
    public function init(OutputInterface $output): void
    {
        $message       = 'prepare JsonNormalizer';
        $message2      = $message . ' - init ...';
        $messageLength = mb_strlen($message2);

        $output->write(
            "\r" . str_pad(string: $message2, length: $messageLength),
            false,
            OutputInterface::VERBOSITY_VERBOSE,
        );

        $message2      = $message . ' - define normalizers ...';
        $messageLength = mb_strlen($message2);

        $output->write(
            "\r" . str_pad(string: $message2, length: $messageLength),
            false,
            OutputInterface::VERBOSITY_VERBOSE,
        );

        $this->normalizers = [
            'format-normalizer' => new FormatNormalizer(
                Normalizer\Format\Format::create(
                    Normalizer\Format\JsonEncodeOptions::fromInt(
                        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR,
                    ),
                    Normalizer\Format\Indent::fromSizeAndStyle(2, 'space'),
                    Normalizer\Format\NewLine::fromString("\n"),
                    true,
                ),
            ),
        ];

        $message2 = $message . ' - done';

        $output->writeln(
            "\r" . str_pad(string: $message2, length: $messageLength),
            OutputInterface::VERBOSITY_VERBOSE,
        );
    }

    /**
     * @param array<int, array<string, string>> $headers
     *
     * @throws SchemaUriReferencesInvalidJsonDocument
     * @throws SchemaUriReferencesDocumentWithInvalidMediaType
     * @throws SchemaUriCouldNotBeResolved
     * @throws SchemaUriCouldNotBeRead
     * @throws NormalizedInvalidAccordingToSchema
     * @throws OriginalInvalidAccordingToSchema
     */
    public function normalize(
        OutputInterface $output,
        array $headers,
        string $message,
        int &$messageLength = 0,
    ): string | null {
        $message2 = $message . ' - encode data ...';

        if (mb_strlen($message2) > $messageLength) {
            $messageLength = mb_strlen($message2);
        }

        $output->write(
            "\r" . '<info>' . str_pad(string: $message2, length: $messageLength) . '</info>',
            false,
            OutputInterface::VERBOSITY_VERY_VERBOSE,
        );

        try {
            $content = json_encode($headers, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            $output->writeln('', OutputInterface::VERBOSITY_VERBOSE);
            $output->writeln(
                '<error>' . (new Exception('could not encode content', 0, $e)) . '</error>',
                OutputInterface::VERBOSITY_NORMAL,
            );

            return null;
        }

        try {
            $json = Json::fromString($content);
        } catch (NotJson $e) {
            $output->writeln('', OutputInterface::VERBOSITY_VERBOSE);
            $output->writeln(
                '<error>' . (new Exception('could not encode content', 0, $e)) . '</error>',
                OutputInterface::VERBOSITY_NORMAL,
            );

            return null;
        }

        $message2 = $message . ' - normalize ...';

        if (mb_strlen($message2) > $messageLength) {
            $messageLength = mb_strlen($message2);
        }

        $output->write(
            "\r" . '<info>' . str_pad(string: $message2, length: $messageLength) . '</info>',
            false,
            OutputInterface::VERBOSITY_VERY_VERBOSE,
        );

        foreach ($this->normalizers as $name => $normalizer) {
            $message2 = $message . sprintf(' - normalize with %s ...', $name);

            if (mb_strlen($message2) > $messageLength) {
                $messageLength = mb_strlen($message2);
            }

            $output->write(
                "\r" . '<info>' . str_pad(string: $message2, length: $messageLength) . '</info>',
                false,
                OutputInterface::VERBOSITY_VERY_VERBOSE,
            );

            assert($normalizer instanceof NormalizerInterface);
            $json = $normalizer->normalize($json);
        }

        $message2 = $message . ' - normalizing done';

        if (mb_strlen($message2) > $messageLength) {
            $messageLength = mb_strlen($message2);
        }

        $output->write(
            "\r" . '<info>' . str_pad(string: $message2, length: $messageLength) . '</info>',
            false,
            OutputInterface::VERBOSITY_VERY_VERBOSE,
        );

        return $json->encoded();
    }
}
