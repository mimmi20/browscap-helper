<?php
/**
 * This file is part of the browscap-helper package.
 *
 * Copyright (c) 2015-2017, Thomas Mueller <mimmi20@live.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);
namespace BrowscapHelper\Source;

use BrowscapHelper\Source\Helper\FilePath;
use BrowscapHelper\Source\Reader\LogFileReader;
use Psr\Log\LoggerInterface;
use Symfony\Component\Finder\Finder;
use UaResult\Browser\Browser;
use UaResult\Device\Device;
use UaResult\Engine\Engine;
use UaResult\Os\Os;
use UaResult\Result\Result;
use Wurfl\Request\GenericRequestFactory;

/**
 * Class DirectorySource
 *
 * @author  Thomas Mueller <mimmi20@live.de>
 */
class LogFileSource implements SourceInterface
{
    /**
     * @var string|null
     */
    private $sourcesDirectory = null;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger = null;

    /**
     * @param \Psr\Log\LoggerInterface $logger
     * @param string                   $sourcesDirectory
     */
    public function __construct(LoggerInterface $logger, $sourcesDirectory)
    {
        $this->logger           = $logger;
        $this->sourcesDirectory = $sourcesDirectory;
    }

    /**
     * @param int $limit
     *
     * @return string[]
     */
    public function getUserAgents(int $limit = 0): iterator
    {
        $counter   = 0;
        $allAgents = [];

        foreach ($this->getAgents() as $agent) {
            if ($limit && $counter >= $limit) {
                return;
            }

            if (empty($agent)) {
                continue;
            }

            if (array_key_exists($agent, $allAgents)) {
                continue;
            }

            yield $agent;
            $allAgents[$agent] = 1;
            ++$counter;
        }
    }

    /**
     * @return \UaResult\Result\Result[]
     */
    public function getTests(): iterator
    {
        $allTests = [];

        foreach ($this->getAgents() as $agent) {
            if (empty($agent)) {
                continue;
            }

            if (array_key_exists($agent, $allTests)) {
                continue;
            }

            $request  = (new GenericRequestFactory())->createRequestForUserAgent($agent);
            $browser  = new Browser(null);
            $device   = new Device(null, null);
            $platform = new Os(null, null);
            $engine   = new Engine(null);

            yield $agent => new Result($request, $device, $platform, $browser, $engine);
            $allTests[$agent] = 1;
        }
    }

    /**
     * @return array
     */
    private function loadFromPath(): iterator
    {
        $finder = new Finder();
        $finder->files();
        $finder->ignoreDotFiles(true);
        $finder->ignoreVCS(true);
        $finder->sortByName();
        $finder->ignoreUnreadableDirs();
        $finder->in($this->sourcesDirectory);

        $filepathHelper = new FilePath();
        $fileCounter    = 0;

        foreach ($finder as $file) {
            /* @var \Symfony\Component\Finder\SplFileInfo $file */
            ++$fileCounter;

            $this->logger->info('    reading file ' . $file->getPathname());

            if (!$file->isFile() || !$file->isReadable()) {
                continue;
            }

            $excludedExtensions = ['filepart', 'sql', 'rename', 'txt', 'zip', 'rar', 'php', 'gitkeep'];

            if (in_array($file->getExtension(), $excludedExtensions)) {
                continue;
            }

            if (null === ($filepath = $filepathHelper->getPath($file))) {
                continue;
            }

            yield $filepath;
        }
    }

    /**
     * @return string[]
     */
    private function getAgents(): iterator
    {
        $reader = new LogFileReader();

        /*******************************************************************************
         * loading files
         ******************************************************************************/

        foreach ($this->loadFromPath() as $filepath) {
            $this->logger->info('    reading file ' . str_pad($filepath, 100, ' ', STR_PAD_RIGHT));

            $reader->setLocalFile($filepath);

            foreach ($reader->getAgents($this->logger) as $agentOfLine) {
                yield trim($agentOfLine);
            }
        }
    }
}
