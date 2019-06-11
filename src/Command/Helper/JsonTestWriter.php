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

use JsonClass\Json;
use Localheinz\Json\Normalizer;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Helper\Helper;

final class JsonTestWriter extends Helper
{
    public function getName()
    {
        return 'json-test-writer';
    }

    /**
     * @param \Psr\Log\LoggerInterface $logger
     * @param array                    $headers
     * @param string                   $dir
     * @param int                      $number
     *
     * @return void
     */
    public function write(LoggerInterface $logger, array $headers, string $dir, int $number): void
    {
        $fileName = $dir . '/' . sprintf('%1$07d', $number) . '.json';
        $schema   = 'file://' . realpath(__DIR__ . '/../../../schema/tests.json');

        $normalizer = new Normalizer\SchemaNormalizer($schema);
        $format     = new Normalizer\Format\Format(
            Normalizer\Format\JsonEncodeOptions::fromInt(
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_PRESERVE_ZERO_FRACTION
            ),
            Normalizer\Format\Indent::fromSizeAndStyle(2, 'space'),
            Normalizer\Format\NewLine::fromString("\n"),
            true
        );

        try {
            $content = (new Json())->encode(
                $headers,
                JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_PRESERVE_ZERO_FRACTION
            );
        } catch (\ExceptionalJSON\EncodeErrorException $e) {
            $logger->critical(new \Exception(sprintf('could not encode content for file %s', $fileName), 0, $e));

            return;
        }

        try {
            $normalized = (new Normalizer\FixedFormatNormalizer($normalizer, $format))->normalize(Normalizer\Json::fromEncoded($content));
        } catch (\Throwable $e) {
            $logger->critical(new \Exception(sprintf('content error'), 0, $e));

            return;
        }

        file_put_contents($fileName, $normalized);
    }
}
