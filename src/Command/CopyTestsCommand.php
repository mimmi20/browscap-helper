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
namespace BrowscapHelper\Command;

use BrowscapHelper\Helper\TargetDirectory;
use BrowscapHelper\Source\BrowscapSource;
use BrowscapHelper\Source\CollectionSource;
use BrowscapHelper\Source\DetectorSource;
use BrowscapHelper\Source\PiwikSource;
use BrowscapHelper\Source\UapCoreSource;
use BrowscapHelper\Source\WhichBrowserSource;
use BrowscapHelper\Source\WootheeSource;
use League\Flysystem\UnreadableFileException;
use Monolog\Handler\PsrHandler;
use Monolog\Logger;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class CopyTestsCommand
 *
 * @category   Browscap Helper
 *
 * @author     Thomas Müller <mimmi20@live.de>
 */
class CopyTestsCommand extends Command
{
    /**
     * @var \Monolog\Logger
     */
    private $logger = null;

    /**
     * @var \Psr\Cache\CacheItemPoolInterface
     */
    private $cache = null;

    /**
     * @param \Monolog\Logger                   $logger
     * @param \Psr\Cache\CacheItemPoolInterface $cache
     */
    public function __construct(Logger $logger, CacheItemPoolInterface $cache)
    {
        $this->logger = $logger;
        $this->cache  = $cache;

        parent::__construct();
    }

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this
            ->setName('copy-tests')
            ->setDescription('Copies tests from browscap and other libraries to browser-detector');
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
     *
     * @return null|int null or 0 if everything went fine, or an error code
     *
     * @see    setCode()
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $consoleLogger = new ConsoleLogger($output);
        $this->logger->pushHandler(new PsrHandler($consoleLogger));

        $targetDirectoryHelper = new TargetDirectory();

        $output->writeln('detect next test number ...');
        try {
            $number = $targetDirectoryHelper->getNextTest($output);
        } catch (UnreadableFileException $e) {
            $this->logger->critical($e);
            $output->writeln($e->getMessage());

            return;
        }

        $output->writeln('detect directory to write new tests ...');
        try {
            $targetDirectory = $targetDirectoryHelper->getPath($output);
        } catch (UnreadableFileException $e) {
            $this->logger->critical($e);
            $output->writeln($e->getMessage());

            return;
        }

        if (!file_exists($targetDirectory)) {
            mkdir($targetDirectory);
        }

        $output->writeln('read existing tests ...');
        $existingTests = [];
        foreach ((new DetectorSource($this->logger, $output, $this->cache))->getUserAgents() as $ua) {
            $ua = trim($ua);

            if (isset($existingTests[$ua])) {
                continue;
            }

            $existingTests[$ua] = 1;
        }

        $output->writeln('init sources ...');
        $counter = 0;
        $source  = new CollectionSource(
            [
                new BrowscapSource($this->logger, $output, $this->cache),
                new PiwikSource($this->logger, $output),
                new UapCoreSource($this->logger, $output),
                new WhichBrowserSource($this->logger, $output),
                new WootheeSource($this->logger, $output),
            ]
        );

        $output->writeln('import tests ...');
        $chunkCounter = 0;
        $fileCounter  = 0;
        $data         = [];

        foreach ($source->getTests() as $ua => $result) {
            $ua = trim($ua);

            if (isset($existingTests[$ua])) {
                continue;
            }

            $targetFilename = 'test-' . sprintf('%1$05d', $number) . '-' . sprintf('%1$05d', (int) $fileCounter) . '.json';

            if (file_exists($targetDirectory . $targetFilename)) {
                $output->writeln('    target file for chunk ' . $fileCounter . ' already exists');
                continue;
            }

            $key = 'test-' . sprintf('%1$08d', $number) . '-' . sprintf('%1$08d', $chunkCounter);

            $data[$key] = [
                'ua'     => $ua,
                'result' => $result->toArray(),
            ];

            ++$counter;
            ++$chunkCounter;

            if ($chunkCounter >= 100) {
                $output->writeln('    writing file ' . $targetFilename);

                file_put_contents(
                    $targetDirectory . $targetFilename,
                    json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
                );

                $chunkCounter = 0;
                $data         = [];
                ++$fileCounter;
            }

            if ($fileCounter >= 200) {
                $fileCounter     = 0;
                $number          = $targetDirectoryHelper->getNextTest($output);
                $targetDirectory = $targetDirectoryHelper->getPath($output);
            }
        }

        $output->writeln('');
        $output->writeln('Es wurden ' . $counter . ' Tests exportiert');
    }
}
