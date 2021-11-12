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

use Ergebnis\Json\Normalizer;
use Ergebnis\Json\Normalizer\Exception\InvalidIndentSizeException;
use Ergebnis\Json\Normalizer\Exception\InvalidIndentStyleException;
use Ergebnis\Json\Normalizer\Exception\InvalidJsonEncodedException;
use Ergebnis\Json\Normalizer\Exception\InvalidJsonEncodeOptionsException;
use Ergebnis\Json\Normalizer\Exception\InvalidNewLineStringException;
use Ergebnis\Json\Normalizer\Exception\NormalizedInvalidAccordingToSchemaException;
use Ergebnis\Json\Normalizer\Exception\OriginalInvalidAccordingToSchemaException;
use Ergebnis\Json\Normalizer\Exception\SchemaUriCouldNotBeReadException;
use Ergebnis\Json\Normalizer\Exception\SchemaUriCouldNotBeResolvedException;
use Ergebnis\Json\Normalizer\Exception\SchemaUriReferencesDocumentWithInvalidMediaTypeException;
use Ergebnis\Json\Normalizer\Exception\SchemaUriReferencesInvalidJsonDocumentException;
use Ergebnis\Json\Normalizer\NormalizerInterface;
use Exception;
use Json\Normalizer\FormatNormalizer;
use JsonClass\EncodeErrorException;
use JsonClass\Json;
use JsonSchema\Constraints\Factory;
use JsonSchema\SchemaStorage;
use JsonSchema\Validator;
use Symfony\Component\Console\Helper\Helper;
use Symfony\Component\Console\Output\OutputInterface;
use UnexpectedValueException;

use function assert;
use function mb_strlen;
use function sprintf;
use function str_pad;

use const JSON_PRETTY_PRINT;
use const JSON_UNESCAPED_SLASHES;
use const JSON_UNESCAPED_UNICODE;
use const STR_PAD_RIGHT;

final class JsonNormalizer extends Helper
{
    /** @var NormalizerInterface[] */
    private array $normalizers;

    public function getName(): string
    {
        return 'json-normalizer';
    }

    /**
     * @throws InvalidJsonEncodeOptionsException
     * @throws InvalidNewLineStringException
     * @throws InvalidIndentStyleException
     * @throws InvalidIndentSizeException
     * @throws UnexpectedValueException
     */
    public function init(OutputInterface $output, string $schemaUri): void
    {
        $messageLength = 0;
        $message       = 'prepare JsonNormalizer';
        $message2      = $message . ' - init ...';

        if (mb_strlen($message2) > $messageLength) {
            $messageLength = mb_strlen($message2);
        }

        $output->write("\r" . str_pad($message2, $messageLength, ' ', STR_PAD_RIGHT), false, OutputInterface::VERBOSITY_VERBOSE);

        $schemaStorage       = new SchemaStorage();
        $jsonSchemavalidator = new Validator(
            new Factory(
                $schemaStorage,
                $schemaStorage->getUriRetriever()
            )
        );

        $message2 = $message . ' - define normalizers ...';

        if (mb_strlen($message2) > $messageLength) {
            $messageLength = mb_strlen($message2);
        }

        $output->write("\r" . str_pad($message2, $messageLength, ' ', STR_PAD_RIGHT), false, OutputInterface::VERBOSITY_VERBOSE);

        $this->normalizers = [
            'schema-normalizer' => new Normalizer\SchemaNormalizer(
                $schemaUri,
                $schemaStorage,
                new Normalizer\Validator\SchemaValidator($jsonSchemavalidator)
            ),
            'format-normalizer' => new FormatNormalizer(
                new Normalizer\Format\Format(
                    Normalizer\Format\JsonEncodeOptions::fromInt(JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT),
                    Normalizer\Format\Indent::fromSizeAndStyle(2, 'space'),
                    Normalizer\Format\NewLine::fromString("\n"),
                    true
                )
            ),
        ];

        $message2 = $message . ' - done';

        if (mb_strlen($message2) > $messageLength) {
            $messageLength = mb_strlen($message2);
        }

        $output->writeln("\r" . str_pad($message2, $messageLength, ' ', STR_PAD_RIGHT), OutputInterface::VERBOSITY_VERBOSE);
    }

    /**
     * @param array<int, array<string, string>> $headers
     *
     * @throws SchemaUriReferencesInvalidJsonDocumentException
     * @throws SchemaUriReferencesDocumentWithInvalidMediaTypeException
     * @throws SchemaUriCouldNotBeResolvedException
     * @throws SchemaUriCouldNotBeReadException
     * @throws NormalizedInvalidAccordingToSchemaException
     * @throws OriginalInvalidAccordingToSchemaException
     * @throws InvalidJsonEncodedException
     */
    public function normalize(OutputInterface $output, array $headers, string $message, int &$messageLength = 0): ?string
    {
        $message2 = $message . ' - encode data ...';

        if (mb_strlen($message2) > $messageLength) {
            $messageLength = mb_strlen($message2);
        }

        $output->write("\r" . '<info>' . str_pad($message2, $messageLength, ' ', STR_PAD_RIGHT) . '</info>', false, OutputInterface::VERBOSITY_VERY_VERBOSE);

        try {
            $content = (new Json())->encode($headers);
        } catch (EncodeErrorException $e) {
            $output->writeln('', OutputInterface::VERBOSITY_VERBOSE);
            $output->writeln('<error>' . (new Exception('could not encode content', 0, $e)) . '</error>', OutputInterface::VERBOSITY_NORMAL);

            return null;
        }

        $json = Normalizer\Json::fromEncoded($content);

        $message2 = $message . ' - normalize ...';

        if (mb_strlen($message2) > $messageLength) {
            $messageLength = mb_strlen($message2);
        }

        $output->write("\r" . '<info>' . str_pad($message2, $messageLength, ' ', STR_PAD_RIGHT) . '</info>', false, OutputInterface::VERBOSITY_VERY_VERBOSE);

        foreach ($this->normalizers as $name => $normalizer) {
            $message2 = $message . sprintf(' - normalize with %s ...', $name);

            if (mb_strlen($message2) > $messageLength) {
                $messageLength = mb_strlen($message2);
            }

            $output->write("\r" . '<info>' . str_pad($message2, $messageLength, ' ', STR_PAD_RIGHT) . '</info>', false, OutputInterface::VERBOSITY_VERY_VERBOSE);

            assert($normalizer instanceof NormalizerInterface);
            $json = $normalizer->normalize($json);
        }

        $message2 = $message . ' - normalizing done';

        if (mb_strlen($message2) > $messageLength) {
            $messageLength = mb_strlen($message2);
        }

        $output->write("\r" . '<info>' . str_pad($message2, $messageLength, ' ', STR_PAD_RIGHT) . '</info>', false, OutputInterface::VERBOSITY_VERY_VERBOSE);

        return $json->encoded();
    }
}
