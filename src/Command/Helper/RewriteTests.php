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

use BrowscapHelper\Source\Ua\UserAgent;
use Ergebnis\Json\Normalizer\Exception\InvalidIndentSizeException;
use Ergebnis\Json\Normalizer\Exception\InvalidIndentStyleException;
use Ergebnis\Json\Normalizer\Exception\InvalidJsonEncodeOptionsException;
use Ergebnis\Json\Normalizer\Exception\InvalidNewLineStringException;
use InvalidArgumentException;
use RuntimeException;
use Symfony\Component\Console\Helper\Helper;
use Symfony\Component\Console\Output\OutputInterface;
use UnexpectedValueException;

use function array_chunk;
use function array_keys;
use function array_unique;
use function assert;
use function file_put_contents;
use function mb_strlen;
use function realpath;
use function sprintf;
use function str_pad;

use const STR_PAD_RIGHT;

final class RewriteTests extends Helper
{
    public function getName(): string
    {
        return 'rewrite-tests';
    }

    /**
     * @param array<string, int> $txtChecks
     *
     * @throws InvalidJsonEncodeOptionsException
     * @throws InvalidNewLineStringException
     * @throws InvalidIndentStyleException
     * @throws InvalidIndentSizeException
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     * @throws UnexpectedValueException
     */
    public function rewrite(OutputInterface $output, array $txtChecks, string $testSource): void
    {
        $jsonNormalizer = $this->getHelperSet()->get('json-normalizer');
        assert($jsonNormalizer instanceof JsonNormalizer);
        $schema = 'file://' . realpath(__DIR__ . '/../../../schema/tests.json');

        $folderChunks = array_chunk(array_unique(array_keys($txtChecks)), 1000);
        $jsonNormalizer->init($output, $schema);

        $baseMessage   = 'rewriting files';
        $messageLength = 0;

        $message = $baseMessage . ' ...';

        if (mb_strlen($message) > $messageLength) {
            $messageLength = mb_strlen($message);
        }

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

            foreach ($folderChunk as $headerString) {
                $headers[] = UserAgent::fromString($headerString)->getHeaders();
            }

            $message2 = $message . ' - normalizing';

            if (mb_strlen($message2) > $messageLength) {
                $messageLength = mb_strlen($message2);
            }

            $output->write("\r" . '<info>' . str_pad($message2, $messageLength, ' ', STR_PAD_RIGHT) . '</info>', false, OutputInterface::VERBOSITY_VERY_VERBOSE);

            try {
                $normalized = $jsonNormalizer->normalize($output, $headers, $message, $messageLength);
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
