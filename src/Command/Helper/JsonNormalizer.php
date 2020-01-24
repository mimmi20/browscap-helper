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

use Ergebnis\Json\Normalizer;
use ExceptionalJSON\EncodeErrorException;
use JsonClass\Json;
use JsonSchema\Constraints\Factory;
use JsonSchema\SchemaStorage;
use JsonSchema\Validator;
use Symfony\Component\Console\Helper\Helper;
use Symfony\Component\Console\Output\OutputInterface;

final class JsonNormalizer extends Helper
{
    /**
     * @var \Ergebnis\Json\Normalizer\NormalizerInterface[]
     */
    private $normalizers;

    /**
     * @var Normalizer\Format\Format
     */
    private $format;

    /**
     * @var Normalizer\Format\Formatter
     */
    private $formatter;

    public function getName()
    {
        return 'json-normalizer';
    }

    /**
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param string                                            $schemaUri
     *
     * @throws \Ergebnis\Json\Normalizer\Exception\InvalidJsonEncodeOptionsException
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
            'encoding-normalizer' => new Normalizer\JsonEncodeNormalizer(Normalizer\Format\JsonEncodeOptions::fromInt(JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)),
            'indent-normalizer' => new class() implements Normalizer\NormalizerInterface {
                /**
                 * @param \Ergebnis\Json\Normalizer\Json $json
                 *
                 * @throws \Ergebnis\Json\Normalizer\Exception\InvalidJsonEncodedException
                 * @throws \Ergebnis\Json\Normalizer\Exception\InvalidIndentSizeException
                 * @throws \Ergebnis\Json\Normalizer\Exception\InvalidIndentStyleException
                 *
                 * @return \Ergebnis\Json\Normalizer\Json
                 */
                public function normalize(Normalizer\Json $json): Normalizer\Json
                {
                    $oldIndent = Normalizer\Format\Indent::fromJson($json);
                    $newIndent = Normalizer\Format\Indent::fromSizeAndStyle(2, 'space');

                    if ((string) $oldIndent === (string) $newIndent) {
                        return clone $json;
                    }

                    $newline = (string) Normalizer\Format\NewLine::fromJson($json);
                    $lines = explode($newline, $json->encoded());

                    if (false === $lines) {
                        return clone $json;
                    }

                    $formattedLines = [];

                    foreach ($lines as $line) {
                        if (1 > preg_match('/^(\s*)(\S.*)/', $line, $matches)) {
                            $formattedLines[] = $line;
                            continue;
                        }

                        $x = str_replace([$oldIndent, '$ni$'], ['$ni$', $newIndent], $matches[1]);

                        $formattedLines[] = $x . $matches[2];
                    }

                    return Normalizer\Json::fromEncoded(implode("\n", $formattedLines) . "\n");
                }
            },
        ];

        $message2 = $message . ' - done';

        if (mb_strlen($message2) > $messageLength) {
            $messageLength = mb_strlen($message2);
        }

        $output->writeln("\r" . str_pad($message2, $messageLength, ' ', STR_PAD_RIGHT), OutputInterface::VERBOSITY_VERBOSE);
    }

    /**
     * @param OutputInterface $output
     * @param array           $headers
     * @param string          $message
     * @param int             $messageLength
     *
     * @throws \Ergebnis\Json\Normalizer\Exception\SchemaUriReferencesInvalidJsonDocumentException
     * @throws \Ergebnis\Json\Normalizer\Exception\SchemaUriReferencesDocumentWithInvalidMediaTypeException
     * @throws \Ergebnis\Json\Normalizer\Exception\SchemaUriCouldNotBeResolvedException
     * @throws \Ergebnis\Json\Normalizer\Exception\SchemaUriCouldNotBeReadException
     * @throws \Ergebnis\Json\Normalizer\Exception\NormalizedInvalidAccordingToSchemaException
     * @throws \Ergebnis\Json\Normalizer\Exception\OriginalInvalidAccordingToSchemaException
     * @throws \Ergebnis\Json\Normalizer\Exception\InvalidJsonEncodedException
     *
     * @return string|null
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
            $output->writeln('<error>' . (new \Exception('could not encode content', 0, $e)) . '</error>', OutputInterface::VERBOSITY_NORMAL);

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

            /** @var \Ergebnis\Json\Normalizer\NormalizerInterface $normalizer */
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
