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

namespace BrowscapHelper\Source\Reader;

use BrowscapHelper\Source\Helper\Regex;
use BrowscapHelper\Source\OutputAwareInterface;
use BrowscapHelper\Source\OutputAwareTrait;
use Symfony\Component\Console\Output\OutputInterface;

use function array_key_exists;
use function array_pop;
use function explode;
use function fclose;
use function feof;
use function fgets;
use function fopen;
use function mb_strlen;
use function preg_match;
use function sprintf;
use function str_pad;
use function trim;

use const STR_PAD_RIGHT;

final class LogFileReader implements OutputAwareInterface, ReaderInterface
{
    use OutputAwareTrait;

    /** @var array<string> */
    private array $files = [];

    /**
     * @throws void
     */
    public function addLocalFile(string $file): void
    {
        $this->files[] = $file;
    }

    /**
     * @return array<string>|iterable
     *
     * @throws void
     */
    public function getAgents(string $parentMessage = '', int &$messageLength = 0): iterable
    {
        $regex = (new Regex())->getRegex();

        foreach ($this->files as $file) {
            $message = $parentMessage . sprintf('- reading file %s', $file);

            if (mb_strlen($message) > $messageLength) {
                $messageLength = mb_strlen($message);
            }

            $this->write("\r" . '<info>' . str_pad($message, $messageLength, ' ', STR_PAD_RIGHT) . '</info>', false, OutputInterface::VERBOSITY_VERBOSE);

            $handle = @fopen($file, 'r');

            if (false === $handle) {
                $this->writeln("\r" . '<error>' . $parentMessage . sprintf('- reading file %s caused an error</error>', $file), OutputInterface::VERBOSITY_NORMAL);
                continue;
            }

            $i = 1;

            while (!feof($handle)) {
                $line = fgets($handle, 65535);

                if (false === $line) {
                    continue;
                }

                ++$i;

                if (empty($line)) {
                    continue;
                }

                $lineMatches = [];

                if (!(bool) preg_match($regex, $line, $lineMatches)) {
                    $this->writeln("\r" . '<error>' . $parentMessage . sprintf('- no useragent found in line "%s" used regex: "%s"</error>', $line, $regex), OutputInterface::VERBOSITY_NORMAL);

                    continue;
                }

                if (array_key_exists('userAgentString', $lineMatches)) {
                    $agentOfLine = trim($lineMatches['userAgentString']);
                } else {
                    $agentOfLine = trim($this->extractAgent($line));
                }

                if (empty($agentOfLine)) {
                    continue;
                }

                yield $agentOfLine;
            }

            fclose($handle);
        }
    }

    /**
     * @throws void
     */
    private function extractAgent(string $text): string
    {
        $parts = explode('"', $text);
        array_pop($parts);

        return (string) array_pop($parts);
    }
}
