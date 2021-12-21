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
use RuntimeException;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

use function array_map;
use function explode;
use function file_exists;
use function implode;
use function is_array;
use function mb_strlen;
use function sprintf;
use function str_pad;
use function trim;

use const STR_PAD_RIGHT;

final class PiwikSource implements OutputAwareInterface, SourceInterface
{
    use GetNameTrait;
    use OutputAwareTrait;

    private const NAME = 'matomo/device-detector';
    private const PATH = 'vendor/matomo/device-detector/regexes';

    public function isReady(string $parentMessage): bool
    {
        if (file_exists(self::PATH)) {
            return true;
        }

        $this->writeln("\r" . '<error>' . $parentMessage . sprintf('- path %s not found</error>', self::PATH), OutputInterface::VERBOSITY_NORMAL);

        return false;
    }

    /**
     * @return array<array<string, string>>|iterable
     *
     * @throws LogicException
     * @throws RuntimeException
     */
    public function getHeaders(string $message, int &$messageLength = 0): iterable
    {
        foreach ($this->loadFromPath($message, $messageLength) as $row) {
            $ua    = explode("\n", $row['user_agent']);
            $ua    = array_map('trim', $ua);
            $agent = trim(implode(' ', $ua));

            $ua    = UserAgent::fromUseragent($agent);
            $agent = (string) $ua;

            if (empty($agent)) {
                continue;
            }

            yield $ua->getHeaders();
        }
    }

    /**
     * @return array<string, string>|iterable
     *
     * @throws LogicException
     * @throws RuntimeException
     */
    private function loadFromPath(string $parentMessage, int &$messageLength = 0): iterable
    {
        $message = $parentMessage . sprintf('- reading path %s', self::PATH);

        if (mb_strlen($message) > $messageLength) {
            $messageLength = mb_strlen($message);
        }

        $this->write("\r" . '<info>' . str_pad($message, $messageLength, ' ', STR_PAD_RIGHT) . '</info>', false, OutputInterface::VERBOSITY_VERBOSE);

        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(self::PATH));
        $files = new \RegexIterator($iterator, '/^.+\.yml$/i', \RegexIterator::GET_MATCH);

        foreach ($files as $file) {
            assert(is_array($file));

            $filepath = $file[0];
            assert(is_string($filepath));

            $message = $parentMessage . sprintf('- reading file %s', $filepath);

            if (mb_strlen($message) > $messageLength) {
                $messageLength = mb_strlen($message);
            }

            $this->write("\r" . '<info>' . str_pad($message, $messageLength, ' ', STR_PAD_RIGHT) . '</info>', false, OutputInterface::VERBOSITY_VERY_VERBOSE);

            $data = Yaml::parseFile($filepath);

            if (!is_array($data)) {
                continue;
            }

            foreach ($data as $row) {
                if (empty($row['user_agent'])) {
                    continue;
                }

                yield $row;
            }
        }
    }
}
