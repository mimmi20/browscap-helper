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
use Ergebnis\Json\Printer\Printer;
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
     */
    public function init(OutputInterface $output, string $schemaUri): void
    {
        $messageLength = 0;
        $message = 'prepare JsonNormalizer';
        $message2 = $message . ' - init ...';

        if (strlen($message2) > $messageLength) {
            $messageLength = strlen($message2);
        }

        $output->write("\r" . str_pad($message2, $messageLength, ' ', STR_PAD_RIGHT));

        $schemaStorage       = new SchemaStorage();
        $jsonSchemavalidator = new Validator(
            new Factory(
                $schemaStorage,
                $schemaStorage->getUriRetriever()
            )
        );

        $message2 = $message . ' - define normalizers ...';

        if (strlen($message2) > $messageLength) {
            $messageLength = strlen($message2);
        }

        $output->write("\r" . str_pad($message2, $messageLength, ' ', STR_PAD_RIGHT));

        $this->normalizers = [
            'schema-normalizer' => new Normalizer\SchemaNormalizer(
                $schemaUri,
                $schemaStorage,
                new Normalizer\Validator\SchemaValidator($jsonSchemavalidator)
            ),
            'encoding-normalizer' => new Normalizer\JsonEncodeNormalizer(Normalizer\Format\JsonEncodeOptions::fromInt(JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)),
            'indent-normalizer' => new class implements Normalizer\NormalizerInterface {
                /**
                 * @inheritDoc
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
                    $formattedLines = [];

                    foreach ($lines as $line) {
                        if (!preg_match('/^(\s*)(\S.*)/', $line, $matches)) {
                            $formattedLines[] = $line;
                            continue;
                        }

                        $x = str_replace([$oldIndent, '$ni$'], ['$ni$', $newIndent], $matches[1]);

                        $formattedLines[] = $x . $matches[2];
                    }

                    return Normalizer\Json::fromEncoded(implode("\n", $formattedLines) . "\n");
                }
            }
        ];
    }

    /**
     * @param OutputInterface $output
     * @param array           $headers
     * @param string $message
     * @param int $messageLength
     *
     * @return string|null
     *@throws \Ergebnis\Json\Normalizer\Exception\InvalidNewLineStringException
     * @throws \Ergebnis\Json\Normalizer\Exception\InvalidIndentStyleException
     * @throws \Ergebnis\Json\Normalizer\Exception\InvalidIndentSizeException
     * @throws \Ergebnis\Json\Normalizer\Exception\InvalidJsonEncodeOptionsException
     */
    public function normalize(OutputInterface $output, array $headers, string $message, int &$messageLength = 0): ?string
    {
        $message2 = $message . ' - encode data ...';

        if (strlen($message2) > $messageLength) {
            $messageLength = strlen($message2);
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

        if (strlen($message2) > $messageLength) {
            $messageLength = strlen($message2);
        }

        $output->write("\r" . '<info>' . str_pad($message2, $messageLength, ' ', STR_PAD_RIGHT) . '</info>', false, OutputInterface::VERBOSITY_VERY_VERBOSE);

        foreach ($this->normalizers as $name => $normalizer) {
            $message2 = $message . sprintf(' - normalize with %s ...', $name);

            if (strlen($message2) > $messageLength) {
                $messageLength = strlen($message2);
            }

            $output->write("\r" . '<info>' . str_pad($message2, $messageLength, ' ', STR_PAD_RIGHT) . '</info>', false, OutputInterface::VERBOSITY_VERY_VERBOSE);

            /** @var \Ergebnis\Json\Normalizer\NormalizerInterface $normalizer */
            $json = $normalizer->normalize($json);
        }

        $message2 = $message . ' - normalizing done';

        if (strlen($message2) > $messageLength) {
            $messageLength = strlen($message2);
        }

        $output->write("\r" . '<info>' . str_pad($message2, $messageLength, ' ', STR_PAD_RIGHT) . '</info>', false, OutputInterface::VERBOSITY_VERY_VERBOSE);

        //$output->writeln('', OutputInterface::VERBOSITY_VERBOSE);
        //$output->writeln('<error>' . (new \Exception(sprintf('content error'), 0, $e)) . '</error>', OutputInterface::VERBOSITY_NORMAL);

        return $json->encoded();
    }
}
