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
use JsonClass\DecodeErrorException;
use JsonClass\Json;
use LogicException;
use RuntimeException;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

use function file_exists;
use function is_array;
use function mb_strlen;
use function sprintf;
use function str_pad;
use function unlink;

use const PHP_EOL;
use const STR_PAD_RIGHT;

final class DetectorSource implements OutputAwareInterface, SourceInterface
{
    use GetNameTrait;
    use OutputAwareTrait;

    private const NAME = 'mimmi20/browser-detector';
    private const PATH = 'vendor/mimmi20/browser-detector/tests/data';

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
        foreach ($this->loadFromPath($message, $messageLength) as $test) {
            $ua    = UserAgent::fromHeaderArray($test['headers']);
            $agent = (string) $ua;

            if (empty($agent)) {
                continue;
            }

            yield $ua->getHeaders();
        }
    }

    /**
     * @return array<array<string, string>>|iterable
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

        $finder = new Finder();
        $finder->files();
        $finder->name('*.json');
        $finder->ignoreDotFiles(true);
        $finder->ignoreVCS(true);
        $finder->sortByName();
        $finder->ignoreUnreadableDirs();
        $finder->in(self::PATH);

        foreach ($finder as $file) {
            /** @var SplFileInfo $file */
            $filepath = $file->getPathname();

            $message = $parentMessage . sprintf('- reading file %s', $filepath);

            if (mb_strlen($message) > $messageLength) {
                $messageLength = mb_strlen($message);
            }

            $this->write("\r" . '<info>' . str_pad($message, $messageLength, ' ', STR_PAD_RIGHT) . '</info>', false, OutputInterface::VERBOSITY_VERY_VERBOSE);

            $content = $file->getContents();

            if ('' === $content || PHP_EOL === $content) {
                unlink($filepath);

                continue;
            }

            try {
                $data = (new Json())->decode(
                    $content,
                    true
                );
            } catch (DecodeErrorException $e) {
                $this->writeln('', OutputInterface::VERBOSITY_VERBOSE);
                $this->writeln('    <error>parsing file content [' . $filepath . '] failed</error>', OutputInterface::VERBOSITY_NORMAL);

                continue;
            }

            if (!is_array($data)) {
                continue;
            }

            foreach ($data as $test) {
                yield $test;
            }
        }
    }
}
