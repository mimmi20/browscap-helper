<?php
/**
 * This file is part of the browscap-helper package.
 *
 * Copyright (c) 2015-2022, Thomas Mueller <mimmi20@live.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);

namespace BrowscapHelper\Normalizer;

use Ergebnis\Json\Normalizer\Exception\InvalidJsonEncoded;
use Ergebnis\Json\Normalizer\Format\Format;
use Ergebnis\Json\Normalizer\Format\Indent;
use Ergebnis\Json\Normalizer\Format\NewLine;
use Ergebnis\Json\Normalizer\Json;
use Ergebnis\Json\Normalizer\Normalizer;
use JsonException;
use UnexpectedValueException;

use function array_key_exists;
use function assert;
use function explode;
use function implode;
use function is_array;
use function is_bool;
use function is_string;
use function json_encode;
use function mb_strpos;
use function preg_match;
use function rtrim;
use function str_replace;

use const JSON_PRETTY_PRINT;
use const JSON_THROW_ON_ERROR;

/** @SuppressWarnings(PHPMD.CouplingBetweenObjects) */
final class FormatNormalizer implements Normalizer
{
    private const PLACE_HOLDER = '$ni$';

    /** @throws UnexpectedValueException */
    public function __construct(private Format $format)
    {
        $this->checkPrettyPrint();
    }

    /**
     * @throws JsonException When the encode operation fails
     * @throws InvalidJsonEncoded
     */
    public function normalize(Json $json): Json
    {
        $encodedWithJsonEncodeOptions = json_encode(
            $json->decoded(),
            $this->format->jsonEncodeOptions()->toInt() | JSON_THROW_ON_ERROR,
        );

        $json       = Json::fromEncoded($encodedWithJsonEncodeOptions);
        $oldNewline = NewLine::fromJson($json)->toString();

        assert(is_string($oldNewline));
        assert('' !== $oldNewline);

        $lines = explode(
            $oldNewline,
            rtrim($json->encoded()),
        );

        assert(is_array($lines));

        $newNewline = $this->format->newLine()->toString();
        assert(is_string($newNewline));

        $oldIndent = Indent::fromJson($json)->toString();
        assert(is_string($oldIndent));

        $newIndent = $this->format->indent()->toString();
        assert(is_string($newIndent));

        $formattedLines = [];
        $matches        = [];

        foreach ($lines as $line) {
            if (!preg_match('/^(?P<ident>\s+)(\S.*)/', $line, $matches)) {
                $formattedLines[] = $line;

                continue;
            }

            assert(array_key_exists('ident', $matches));
            assert(is_string($matches['ident']));

            $tempLine = str_replace($oldIndent, self::PLACE_HOLDER, $matches['ident']);

            assert(is_string($tempLine));
            assert(false === mb_strpos($tempLine, $oldIndent));
            assert(false !== mb_strpos($tempLine, self::PLACE_HOLDER));

            $tempLine = str_replace(self::PLACE_HOLDER, $newIndent, $tempLine);

            assert(false === mb_strpos($tempLine, self::PLACE_HOLDER));

            $formattedLines[] = $tempLine . $matches[2];
        }

        $content = implode($newNewline, $formattedLines);

        if ($this->format->hasFinalNewLine()) {
            $content .= $newNewline;
        }

        return Json::fromEncoded($content);
    }

    /** @throws UnexpectedValueException */
    private function checkPrettyPrint(): void
    {
        $jsonOptions = $this->format->jsonEncodeOptions()->toInt();
        $prettyPrint = (bool) ($jsonOptions & JSON_PRETTY_PRINT);
        assert(is_bool($prettyPrint));

        if (!$prettyPrint) {
            throw new UnexpectedValueException('This Normalizer requires the JSON_PRETTY_PRINT option to be set.');
        }
    }
}
