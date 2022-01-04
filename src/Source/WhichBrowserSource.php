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
use FilterIterator;
use Iterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;
use SplFileInfo;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

use function array_key_exists;
use function assert;
use function file_exists;
use function is_array;
use function is_string;
use function mb_strlen;
use function mb_strpos;
use function mb_strtolower;
use function sprintf;
use function str_pad;
use function str_replace;

use const STR_PAD_RIGHT;

final class WhichBrowserSource implements OutputAwareInterface, SourceInterface
{
    use GetNameTrait;
    use OutputAwareTrait;

    private const NAME = 'whichbrowser/parser';
    private const PATH = 'vendor/whichbrowser/parser/tests/data';

    /**
     * @throws void
     */
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
     * @throws RuntimeException
     */
    public function getHeaders(string $message, int &$messageLength = 0): iterable
    {
        foreach ($this->loadFromPath($message, $messageLength) as $row) {
            $lowerHeaders = [];

            foreach ($this->getHeadersFromRow($row) as $header => $value) {
                $lowerHeaders[mb_strtolower((string) $header)] = $value;
            }

            $ua    = UserAgent::fromHeaderArray($lowerHeaders);
            $agent = (string) $ua;

            if (empty($agent)) {
                continue;
            }

            yield $ua->getHeaders();
        }
    }

    /**
     * @return array<string, array<string, string>|string>|iterable
     *
     * @throws RuntimeException
     */
    private function loadFromPath(string $parentMessage, int &$messageLength = 0): iterable
    {
        $message = $parentMessage . sprintf('- reading path %s', self::PATH);

        if (mb_strlen($message) > $messageLength) {
            $messageLength = mb_strlen($message);
        }

        $this->write("\r" . '<info>' . str_pad($message, $messageLength, ' ', STR_PAD_RIGHT) . '</info>', false, OutputInterface::VERBOSITY_VERBOSE);

        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(self::PATH));
        $files    = new class ($iterator, 'yaml') extends FilterIterator {
            private string $extension;

            /**
             * @param Iterator<SplFileInfo> $iterator
             */
            public function __construct(Iterator $iterator, string $extension)
            {
                parent::__construct($iterator);
                $this->extension = $extension;
            }

            public function accept(): bool
            {
                $file = $this->getInnerIterator()->current();

                assert($file instanceof SplFileInfo);

                return $file->isFile() && $file->getExtension() === $this->extension;
            }
        };

        foreach ($files as $file) {
            /** @var SplFileInfo $file */
            $pathName = $file->getPathname();
            $filepath = str_replace('\\', '/', $pathName);
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
                yield $row;
            }
        }
    }

    /**
     * @param array<string, array<string, string>|string> $row
     *
     * @return array<string, string>
     *
     * @throws void
     */
    private function getHeadersFromRow(array $row): array
    {
        if (array_key_exists('headers', $row)) {
            if (is_array($row['headers'])) {
                return $row['headers'];
            }

            if (is_string($row['headers']) && 0 === mb_strpos($row['headers'], 'User-Agent: ')) {
                return ['user-agent' => str_replace('User-Agent: ', '', $row['headers'])];
            }

            return [];
        }

        return [];
    }
}
