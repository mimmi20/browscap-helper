<?php
/**
 * This file is part of the browscap-helper package.
 *
 * Copyright (c) 2015-2024, Thomas Mueller <mimmi20@live.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);

namespace BrowscapHelper\Normalizer;

use Ergebnis\Json\Exception\NotJson;
use Ergebnis\Json\Json;
use Ergebnis\Json\Normalizer\Format\Format;
use Ergebnis\Json\Normalizer\Format\Indent;
use Ergebnis\Json\Normalizer\Format\NewLine;
use Ergebnis\Json\Normalizer\Normalizer;
use JsonException;
use UnexpectedValueException;

use function array_key_exists;
use function assert;
use function explode;
use function implode;
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
    public function __construct(private readonly Format $format)
    {
        $this->checkPrettyPrint();
    }

    /** @throws NotJson */
    public function normalize(Json $json): Json
    {
        try {
            $encodedWithJsonEncodeOptions = json_encode(
                $json->decoded(),
                $this->format->jsonEncodeOptions()->toInt() | JSON_THROW_ON_ERROR,
            );
        } catch (JsonException $e) {
            throw new NotJson($e->getMessage(), 0, $e);
        }

        $json       = Json::fromString($encodedWithJsonEncodeOptions);
        $oldNewline = NewLine::fromJson($json)->toString();

        assert($oldNewline !== '');

        $lines = explode(
            $oldNewline,
            rtrim($json->encoded()),
        );

        $newNewline = $this->format->newLine()->toString();
        $oldIndent  = Indent::fromJson($json)->toString();
        $newIndent  = $this->format->indent()->toString();

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
            assert(mb_strpos($tempLine, $oldIndent) === false);
            assert(mb_strpos($tempLine, self::PLACE_HOLDER) !== false);

            $tempLine = str_replace(self::PLACE_HOLDER, $newIndent, $tempLine);

            assert(mb_strpos($tempLine, self::PLACE_HOLDER) === false);

            $formattedLines[] = $tempLine . $matches[2];
        }

        $content = implode($newNewline, $formattedLines);

        if ($this->format->hasFinalNewLine()) {
            $content .= $newNewline;
        }

        return Json::fromString($content);
    }

    /** @throws UnexpectedValueException */
    private function checkPrettyPrint(): void
    {
        $jsonOptions = $this->format->jsonEncodeOptions()->toInt();
        $prettyPrint = (bool) ($jsonOptions & JSON_PRETTY_PRINT);

        if (!$prettyPrint) {
            throw new UnexpectedValueException(
                'This Normalizer requires the JSON_PRETTY_PRINT option to be set.',
            );
        }
    }
}
