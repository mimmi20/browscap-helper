<?php
/**
 * Copyright (c) 1998-2014 Browser Capabilities Project
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * Refer to the LICENSE file distributed with this package.
 *
 * @category   Browscap
 * @copyright  1998-2014 Browser Capabilities Project
 * @license    MIT
 */

namespace BrowscapHelper\Command;

use BrowscapHelper\Helper\FilePath;
use BrowscapHelper\Helper\Regex;
use FileLoader\Loader;
use FileLoader\Psr7\Stream;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class DiffCommand
 *
 * @category   Browscap
 * @author     James Titcumb <james@asgrim.com>
 */
class ConvertLogsCommand extends Command
{
    /**
     * @var string
     */
    private $sourcesDirectory = '';

    /**
     * @var string
     */
    private $targetDirectory = '';

    /**
     * @param string $sourcesDirectory
     * @param string $targetDirectory
     */
    public function __construct($sourcesDirectory, $targetDirectory)
    {
        $this->sourcesDirectory = $sourcesDirectory;
        $this->targetDirectory  = $targetDirectory;

        parent::__construct();
    }

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this
            ->setName('convert-logs')
            ->setDescription('Reads the server logs, extracts the useragents and writes them into a file')
            ->addOption(
                'resources',
                null,
                InputOption::VALUE_REQUIRED,
                'Where the resource files are located',
                $this->sourcesDirectory
            )
            ->addOption(
                'target',
                null,
                InputOption::VALUE_REQUIRED,
                'Where the target files should be written',
                $this->targetDirectory
            );
    }

    /**
     * Executes the current command.
     *
     * This method is not abstract because you can use this class
     * as a concrete class. In this case, instead of defining the
     * execute() method, you set the code to execute by passing
     * a Closure to the setCode() method.
     *
     * @param InputInterface  $input  An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     *
     * @throws \LogicException When this abstract method is not implemented
     * @return null|int        null or 0 if everything went fine, or an error code
     *
     * @see    setCode()
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $targetDirectory  = $input->getOption('target');
        $sourcesDirectory = $input->getOption('resources');

        $i = 0;
        $j = 0;

        $targetBulkFile = $targetDirectory . date('Y-m-d') . '-testagents.txt';
        $targetInfoFile = $targetDirectory . date('Y-m-d') . '-testagents.info.txt';

        $output->writeln("reading to directory '" . $sourcesDirectory . "'");
        $output->writeln("writing to file '" . $targetBulkFile . "'");

        $loader = new Loader();

        /*******************************************************************************
         * loading files
         ******************************************************************************/

        $files = scandir($sourcesDirectory, SCANDIR_SORT_ASCENDING);

        foreach ($files as $filename) {
            /** @var $file \SplFileInfo */
            $file = new \SplFileInfo($sourcesDirectory . $filename);

            ++$i;
            $output->write('# ' . sprintf('%1$05d', (int) $i) . ' :' . strtolower($file->getPathname()) . ' [ bisher ' . ($j > 0 ? $j : 'keine') . ' Agent' . ($j !== 1 ? 'en' : '') . ' ]');

            if (!$file->isFile() || !$file->isReadable()) {
                $output->writeln(' - ignoriert');

                continue;
            }

            $excludedExtensions = ['filepart', 'sql', 'rename', 'txt', 'zip', 'rar', 'php', 'gitkeep'];

            if (in_array($file->getExtension(), $excludedExtensions)) {
                $output->writeln(' - ignoriert');

                continue;
            }

            if (null === ($filepath = (new FilePath())->getPath($file))) {
                $output->writeln(' - ignoriert');

                continue;
            }

            $startTime = microtime(true);
            $k         = 0;

            $loader->setLocalFile($filepath);

            /** @var \GuzzleHttp\Psr7\Response $response */
            $response = $loader->load();

            /** @var \FileLoader\Psr7\Stream $stream */
            $stream = $response->getBody();

            $stream->read(1);
            $stream->rewind();

            $agents = $this->handleFile($stream, $targetInfoFile);
            $k      = $this->sortAgents($k, $agents, $targetBulkFile);

            $dauer = microtime(true) - $startTime;
            $output->writeln(' - fertig [ ' . ($k > 0 ? $k . ' neue' : 'keine neuen') . ($k === 1 ? 'r' : '') . ' Agent' . ($k !== 1 ? 'en' : '') . ', ' . number_format($dauer, 4, ',', '.') . ' sec ]');

            unlink($file->getPathname());
            $j += $k;
        }

        if (file_exists($targetBulkFile)) {
            $data = file($targetBulkFile, FILE_SKIP_EMPTY_LINES | FILE_IGNORE_NEW_LINES);
            $data = array_unique($data);

            file_put_contents($targetBulkFile, implode("\n", $data), LOCK_EX);
        }

        $output->writeln('');
        $output->writeln('');
        $output->writeln('lesen der Dateien beendet. ' . $j . ' neue Agenten hinzugefuegt');
    }

    /**
     * @param \FileLoader\Psr7\Stream $stream
     * @param string                  $targetInfoFile
     *
     * @return array
     */
    private function handleFile(Stream $stream, $targetInfoFile)
    {
        $agents = [];
        $regex  = (new Regex())->getRegex();

        while (!$stream->eof()) {
            $line        = $stream->read(8192);
            $lineMatches = [];

            if (!preg_match($regex, $line, $lineMatches)) {
                file_put_contents($targetInfoFile, 'no useragent found in line "' . $line . '"' . "\n", FILE_APPEND | LOCK_EX);

                continue;
            }

            if (isset($lineMatches['userAgentString'])) {
                $agentOfLine = trim($lineMatches['userAgentString']);
            } else {
                $agentOfLine = trim($this->extractAgent($line));
            }

            if (!array_key_exists($agentOfLine, $agents)) {
                $agents[$agentOfLine] = 1;
            } else {
                ++$agents[$agentOfLine];
            }
        }

        return $agents;
    }

    /**
     * @param int    $k
     * @param array  $agents
     * @param string $targetBulkFile
     *
     * @return mixed
     */
    private function sortAgents($k, array $agents, $targetBulkFile)
    {
        $sortCount = [];
        $sortTime  = [];
        $sortAgent = [];

        foreach ($agents as $agentOfLine => $count) {
            $sortCount[$agentOfLine] = $count;
            $sortAgent[$agentOfLine] = $agentOfLine;
        }

        array_multisort($sortCount, SORT_DESC, $sortTime, SORT_DESC, $sortAgent, SORT_ASC, $agents);

        foreach (array_keys($agents) as $agentOfLine) {
            file_put_contents($targetBulkFile, $agentOfLine . "\n", FILE_APPEND | LOCK_EX);
            ++$k;
        }

        return $k;
    }

    /**
     * @param string $text
     *
     * @return string
     */
    private function extractAgent($text)
    {
        $parts = explode('"', $text);
        array_pop($parts);

        $userAgent = array_pop($parts);

        return $userAgent;
    }
}
