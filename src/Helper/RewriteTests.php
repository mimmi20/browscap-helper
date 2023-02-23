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

namespace BrowscapHelper\Helper;

use BrowscapHelper\Source\Ua\UserAgent;
use Ergebnis\Json\Normalizer\Exception\InvalidIndentSize;
use Ergebnis\Json\Normalizer\Exception\InvalidIndentStyle;
use Ergebnis\Json\Normalizer\Exception\InvalidJsonEncodeOptions;
use Ergebnis\Json\Normalizer\Exception\InvalidNewLineString;
use InvalidArgumentException;
use RuntimeException;
use Symfony\Component\Console\Output\OutputInterface;
use UnexpectedValueException;

use function array_chunk;
use function array_keys;
use function file_put_contents;
use function mb_strlen;
use function sprintf;
use function str_pad;

use const STR_PAD_RIGHT;

final class RewriteTests
{
    /** @throws void */
    public function __construct(
        private readonly JsonNormalizer $jsonNormalizer,
    ) {
        // nothing to do
    }

    /**
     * @param array<string, array<string, array<string, array<string, bool|float|int|string|null>|bool|int|string|null>>|int> $txtChecks
     *
     * @throws InvalidJsonEncodeOptions
     * @throws InvalidNewLineString
     * @throws InvalidIndentStyle
     * @throws InvalidIndentSize
     * @throws UnexpectedValueException
     */
    public function rewrite(
        OutputInterface $output,
        array $txtChecks,
        string $testSource,
    ): void {
        $folderChunks = array_chunk($txtChecks, 1000, true);
        $this->jsonNormalizer->init($output);

        $baseMessage   = 'rewriting files';
        $message       = $baseMessage . ' ...';
        $messageLength = mb_strlen($message);

        $output->writeln(str_pad($message, $messageLength, ' ', STR_PAD_RIGHT), OutputInterface::VERBOSITY_NORMAL);

        foreach ($folderChunks as $folderId => $folderChunk) {
            $headers = [];

            $fileName = $testSource . '/' . sprintf('%1$07d', $folderId) . '.json';

            $message  = $baseMessage . sprintf(' %s', $fileName);
            $message2 = $message . ' - pre-check';

            if (mb_strlen($message2) > $messageLength) {
                $messageLength = mb_strlen($message2);
            }

            $output->write("\r" . '<info>' . str_pad($message2, $messageLength, ' ', STR_PAD_RIGHT) . '</info>', false, OutputInterface::VERBOSITY_VERY_VERBOSE);

            foreach (array_keys($folderChunk) as $headerString) {
                $headerArray = UserAgent::fromString($headerString)->getHeaders();

                if ([] === $headerArray) {
                    continue;
                }

                $headers[] = $headerArray;
            }

            $message2 = $message . ' - normalizing';

            if (mb_strlen($message2) > $messageLength) {
                $messageLength = mb_strlen($message2);
            }

            $output->write("\r" . '<info>' . str_pad($message2, $messageLength, ' ', STR_PAD_RIGHT) . '</info>', false, OutputInterface::VERBOSITY_VERY_VERBOSE);

            try {
                $normalized = $this->jsonNormalizer->normalize($output, $headers, $message, $messageLength);
            } catch (InvalidArgumentException | RuntimeException $e) {
                $output->writeln('', OutputInterface::VERBOSITY_VERBOSE);
                $output->writeln('<error>' . $e . '</error>', OutputInterface::VERBOSITY_NORMAL);

                continue;
            }

            if (null === $normalized) {
                $output->writeln('', OutputInterface::VERBOSITY_VERBOSE);
                $output->writeln('<error>' . sprintf('normalisation failed for file %s', $fileName) . '</error>', OutputInterface::VERBOSITY_NORMAL);

                continue;
            }

            $message2 = $message . ' - writing';

            if (mb_strlen($message2) > $messageLength) {
                $messageLength = mb_strlen($message2);
            }

            $output->write("\r" . '<info>' . str_pad($message2, $messageLength, ' ', STR_PAD_RIGHT) . '</info>', false, OutputInterface::VERBOSITY_VERY_VERBOSE);

            file_put_contents($fileName, $normalized);
        }

        $message = $baseMessage . ' - done';

        if (mb_strlen($message) > $messageLength) {
            $messageLength = mb_strlen($message);
        }

        $output->writeln("\r" . '<info>' . str_pad($message, $messageLength, ' ', STR_PAD_RIGHT) . '</info>', OutputInterface::VERBOSITY_VERBOSE);
    }
}
