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
use Symfony\Component\Console\Helper\Helper;
use Symfony\Component\Console\Output\OutputInterface;
use UnexpectedValueException;

use function assert;
use function json_encode;
use function mb_str_pad;
use function mb_strlen;
use function sprintf;

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
            "\r" . mb_str_pad(string: $message2, length: $messageLength),
            false,
            OutputInterface::VERBOSITY_VERBOSE,
        );

        $message2      = $message . ' - define normalizers ...';
        $messageLength = mb_strlen($message2);

        $output->write(
            "\r" . mb_str_pad(string: $message2, length: $messageLength),
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
            "\r" . mb_str_pad(string: $message2, length: $messageLength),
            OutputInterface::VERBOSITY_VERBOSE,
        );
    }

    /**
     * @param array<int|string, array<string, string>|string> $headers
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
        $message2 = '<info>' . $message . '</info> - encode data ...';

        $diff = $this->messageLength($output, $message2, $messageLength);

        $output->write(
            "\r" . mb_str_pad(string: $message2, length: $messageLength + $diff),
            false,
            OutputInterface::VERBOSITY_VERY_VERBOSE,
        );
        $output->writeln(sprintf(' <bg=red>%d</>', $messageLength), OutputInterface::VERBOSITY_DEBUG);

        try {
            $content = json_encode($headers, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            $output->writeln('', OutputInterface::VERBOSITY_VERBOSE);
            $output->writeln(
                '<error>' . (new Exception('could not encode content', 0, $e)) . '</error>',
                OutputInterface::VERBOSITY_NORMAL,
            );

            $messageLength = 0;

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

            $messageLength = 0;

            return null;
        }

        $message2 = '<info>' . $message . '</info> - normalize ...';

        $diff = $this->messageLength($output, $message2, $messageLength);

        $output->write(
            "\r" . mb_str_pad(string: $message2, length: $messageLength + $diff),
            false,
            OutputInterface::VERBOSITY_VERY_VERBOSE,
        );
        $output->writeln(sprintf(' <bg=red>%d</>', $messageLength), OutputInterface::VERBOSITY_DEBUG);

        foreach ($this->normalizers as $name => $normalizer) {
            $message2 = '<info>' . $message . '</info>' . sprintf(' - normalize with %s ...', $name);

            $diff = $this->messageLength($output, $message2, $messageLength);

            $output->write(
                "\r" . mb_str_pad(string: $message2, length: $messageLength + $diff),
                false,
                OutputInterface::VERBOSITY_VERY_VERBOSE,
            );
            $output->writeln(sprintf(' <bg=red>%d</>', $messageLength), OutputInterface::VERBOSITY_DEBUG);

            assert($normalizer instanceof NormalizerInterface);
            $json = $normalizer->normalize($json);
        }

        $message2 = '<info>' . $message . '</info> - normalizing done';

        $diff = $this->messageLength($output, $message2, $messageLength);

        $output->write(
            "\r" . mb_str_pad(string: $message2, length: $messageLength + $diff),
            false,
            OutputInterface::VERBOSITY_VERY_VERBOSE,
        );
        $output->writeln(sprintf(' <bg=red>%d</>', $messageLength), OutputInterface::VERBOSITY_DEBUG);

        return $json->encoded();
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
