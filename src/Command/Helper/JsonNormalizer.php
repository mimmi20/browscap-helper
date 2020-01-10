<?php
/**
 * This file is part of the browscap-helper package.
 *
 * Copyright (c) 2015-2019, Thomas Mueller <mimmi20@live.de>
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
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Helper\Helper;

final class JsonNormalizer extends Helper
{
    public function getName()
    {
        return 'json-normalizer';
    }

    /**
     * @param \Psr\Log\LoggerInterface $logger
     * @param array                    $headers
     * @param string                   $schema
     *
     * @throws \Ergebnis\Json\Normalizer\Exception\InvalidJsonEncodeOptionsException
     * @throws \Ergebnis\Json\Normalizer\Exception\InvalidNewLineStringException
     * @throws \Ergebnis\Json\Normalizer\Exception\InvalidIndentStyleException
     * @throws \Ergebnis\Json\Normalizer\Exception\InvalidIndentSizeException
     *
     * @return string|null
     */
    public function normalize(LoggerInterface $logger, array $headers, string $schema): ?string
    {
        $normalizer = new Normalizer\SchemaNormalizer(
            $schema,
            new SchemaStorage(),
            new Normalizer\Validator\SchemaValidator(new Validator())
        );
        $format = new Normalizer\Format\Format(
            Normalizer\Format\JsonEncodeOptions::fromInt(
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_PRESERVE_ZERO_FRACTION
            ),
            Normalizer\Format\Indent::fromSizeAndStyle(2, 'space'),
            Normalizer\Format\NewLine::fromString("\n"),
            true
        );
        $printer   = new Printer();
        $formatter = new Normalizer\Format\Formatter($printer);

        try {
            $content = (new Json())->encode($headers);
        } catch (EncodeErrorException $e) {
            $logger->critical(new \Exception('could not encode content', 0, $e));

            return null;
        }

        try {
            $normalized = (new Normalizer\FixedFormatNormalizer($normalizer, $format, $formatter))->normalize(Normalizer\Json::fromEncoded($content));
        } catch (\Throwable $e) {
            $logger->critical(new \Exception(sprintf('content error'), 0, $e));

            return null;
        }

        return $normalized->encoded();
    }
}
