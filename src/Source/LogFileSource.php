<?php
/**
 * This file is part of the browscap-helper package.
 *
 * Copyright (c) 2015-2020, Thomas Mueller <mimmi20@live.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);
namespace BrowscapHelper\Source;

use BrowscapHelper\Source\Helper\FilePath;
use BrowscapHelper\Source\Reader\LogFileReader;
use BrowscapHelper\Source\Ua\UserAgent;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

final class LogFileSource implements OutputAwareInterface, SourceInterface
{
    use GetNameTrait;
    use OutputAwareTrait;

    /** @var string */
    private $dir;

    private const NAME = 'log-files';

    /**
     * @param string $sourcesDirectory
     */
    public function __construct(string $sourcesDirectory)
    {
        $this->dir = $sourcesDirectory;
    }

    /**
     * @param string $parentMessage
     *
     * @return bool
     */
    public function isReady(string $parentMessage): bool
    {
        if (file_exists($this->dir)) {
            return true;
        }

        $this->writeln("\r" . '<error>' . $parentMessage . sprintf('- path %s not found</error>', $this->dir), OutputInterface::VERBOSITY_NORMAL);

        return false;
    }

    /**
     * @param string $message
     * @param int    $messageLength
     *
     * @throws \LogicException
     *
     * @return array[]|iterable
     */
    public function getHeaders(string $message, int &$messageLength = 0): iterable
    {
        foreach ($this->loadFromPath($message, $messageLength) as $agent) {
            $ua    = UserAgent::fromUseragent($agent);
            $agent = (string) $ua;

            if (empty($agent)) {
                continue;
            }

            yield $ua->getHeaders();
        }
    }

    /**
     * @param string $parentMessage
     * @param int    $messageLength
     *
     * @throws \LogicException
     *
     * @return iterable|string[]
     */
    private function loadFromPath(string $parentMessage, int &$messageLength = 0): iterable
    {
        $message = $parentMessage . sprintf('- reading path %s', $this->dir);

        if (mb_strlen($message) > $messageLength) {
            $messageLength = mb_strlen($message);
        }

        $this->write("\r" . '<info>' . str_pad($message, $messageLength, ' ', STR_PAD_RIGHT) . '</info>', false, OutputInterface::VERBOSITY_VERBOSE);

        $finder = new Finder();
        $finder->files();
        $finder->notName('*.filepart');
        $finder->notName('*.sql');
        $finder->notName('*.rename');
        $finder->notName('*.txt');
        $finder->notName('*.ctxt');
        $finder->notName('*.zip');
        $finder->notName('*.rar');
        $finder->notName('*.php');
        $finder->notName('*.gitkeep');
        $finder->ignoreDotFiles(true);
        $finder->ignoreVCS(true);
        $finder->sortByName();
        $finder->ignoreUnreadableDirs();
        $finder->in($this->dir);

        $filepathHelper = new FilePath();
        $reader         = new LogFileReader();

        if (null !== $this->output) {
            $reader->setOutput($this->output);
        }

        foreach ($finder as $file) {
            /** @var \Symfony\Component\Finder\SplFileInfo $file */
            $filepath = $filepathHelper->getPath($file);

            if (null === $filepath) {
                continue;
            }

            $reader->addLocalFile($filepath);
        }

        foreach ($reader->getAgents($parentMessage, $messageLength) as $agent) {
            yield $agent;
        }
    }
}
