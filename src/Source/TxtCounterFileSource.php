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

namespace BrowscapHelper\Source;

use BrowscapHelper\Source\Ua\UserAgent;
use LogicException;
use Symfony\Component\Console\Output\OutputInterface;

use function explode;
use function fclose;
use function feof;
use function fgets;
use function file_exists;
use function fopen;
use function mb_strlen;
use function sprintf;
use function str_pad;
use function trim;

use const STR_PAD_RIGHT;

final class TxtCounterFileSource implements OutputAwareInterface, SourceInterface
{
    use GetNameTrait;
    use OutputAwareTrait;

    private const NAME = 'ctxt-files';

    private string $dir;

    public function __construct(string $dir)
    {
        $this->dir = $dir;
    }

    public function isReady(string $parentMessage): bool
    {
        if (file_exists($this->dir)) {
            return true;
        }

        $this->writeln("\r" . '<error>' . $parentMessage . sprintf('- path %s not found</error>', $this->dir), OutputInterface::VERBOSITY_NORMAL);

        return false;
    }

    /**
     * @return array<array<string, string>>|iterable
     *
     * @throws LogicException
     */
    public function getHeaders(string $message, int &$messageLength = 0): iterable
    {
        foreach ($this->loadFromPath($message, $messageLength) as $parts) {
            $ua    = UserAgent::fromUseragent($parts[1]);
            $agent = (string) $ua;

            if (empty($agent)) {
                continue;
            }

            yield $ua->getHeaders();
        }
    }

    /**
     * @return array<array<string>>|iterable
     *
     * @throws LogicException
     */
    private function loadFromPath(string $parentMessage, int &$messageLength = 0): iterable
    {
        $message = $parentMessage . sprintf('- reading path %s', $this->dir);

        if (mb_strlen($message) > $messageLength) {
            $messageLength = mb_strlen($message);
        }

        $this->write("\r" . '<info>' . str_pad($message, $messageLength, ' ', STR_PAD_RIGHT) . '</info>', false, OutputInterface::VERBOSITY_VERBOSE);

        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($this->dir));
        $files = new \RegexIterator($iterator, '/^.+\.ctxt$/i', \RegexIterator::GET_MATCH);

        foreach ($files as $file) {
            assert(is_array($file));

            $filepath = $file[0];
            assert(is_string($filepath));

            $message = $parentMessage . sprintf('- reading file %s', $filepath);

            if (mb_strlen($message) > $messageLength) {
                $messageLength = mb_strlen($message);
            }

            $this->write("\r" . '<info>' . str_pad($message, $messageLength, ' ', STR_PAD_RIGHT) . '</info>', false, OutputInterface::VERBOSITY_VERY_VERBOSE);

            $handle = @fopen($filepath, 'r');

            if (false === $handle) {
                $this->writeln('', OutputInterface::VERBOSITY_VERBOSE);
                $this->writeln('<error>reading file ' . $filepath . ' caused an error</error>', OutputInterface::VERBOSITY_NORMAL);
                continue;
            }

            $i = 1;

            while (!feof($handle)) {
                $line = fgets($handle, 65535);

                if (false === $line) {
                    continue;
                }

                ++$i;

                $line = trim($line);

                if (empty($line)) {
                    continue;
                }

                $parts = explode(' ', $line, 2);

                if (empty($parts[1])) {
                    continue;
                }

                yield $parts;
            }

            fclose($handle);
        }
    }
}
