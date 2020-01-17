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
use JsonSchema\SchemaStorage;
use JsonSchema\Validator;
use Symfony\Component\Console\Helper\Helper;
use Symfony\Component\Console\Output\OutputInterface;

final class JsonNormalizer extends Helper
{
    /**
     * @var Normalizer\SchemaNormalizer
     */
    private $normalizer;

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

    public function init(string $schema): void
    {
        $this->normalizer = new Normalizer\SchemaNormalizer(
            $schema,
            new SchemaStorage(),
            new Normalizer\Validator\SchemaValidator(new Validator())
        );

        $this->format = new Normalizer\Format\Format(
            Normalizer\Format\JsonEncodeOptions::fromInt(
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT
            ),
            Normalizer\Format\Indent::fromSizeAndStyle(2, 'space'),
            Normalizer\Format\NewLine::fromString("\n"),
            true
        );

        $this->formatter = new Normalizer\Format\Formatter(new Printer());
    }

    /**
     * @param OutputInterface $output
     * @param array           $headers
     *
     * @throws \Ergebnis\Json\Normalizer\Exception\InvalidJsonEncodeOptionsException
     * @throws \Ergebnis\Json\Normalizer\Exception\InvalidNewLineStringException
     * @throws \Ergebnis\Json\Normalizer\Exception\InvalidIndentStyleException
     * @throws \Ergebnis\Json\Normalizer\Exception\InvalidIndentSizeException
     *
     * @return string|null
     */
    public function normalize(OutputInterface $output, array $headers): ?string
    {
        try {
            $content = (new Json())->encode($headers);
        } catch (EncodeErrorException $e) {
            $output->writeln('', OutputInterface::VERBOSITY_VERBOSE);
            $output->writeln('<error>' . (new \Exception('could not encode content', 0, $e)) . '</error>', OutputInterface::VERBOSITY_NORMAL);

            return null;
        }

        try {
            $normalized = (new Normalizer\FixedFormatNormalizer($this->normalizer, $this->format, $this->formatter))->normalize(Normalizer\Json::fromEncoded($content));
        } catch (\Throwable $e) {
            $output->writeln('', OutputInterface::VERBOSITY_VERBOSE);
            $output->writeln('<error>' . (new \Exception(sprintf('content error'), 0, $e)) . '</error>', OutputInterface::VERBOSITY_NORMAL);

            return null;
        }

        return $normalized->encoded();
    }
}
