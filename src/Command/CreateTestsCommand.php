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
use BrowscapHelper\Source\DetectorSource;
use BrowscapHelper\Source\DirectorySource;
use BrowscapHelper\Writer\BrowscapTestWriter;
use BrowscapHelper\Writer\DetectorTestWriter;
use BrowserDetector\Detector;
use Monolog\Handler\PsrHandler;
use Monolog\Logger;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class CreateTestsCommand
 *
 * @category   Browscap Helper
 *
 * @author     Thomas Müller <mimmi20@live.de>
 */
class CreateTestsCommand extends Command
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
     * @var \Monolog\Logger
     */
    private $logger;

    /**
     * @var \Psr\Cache\CacheItemPoolInterface
     */
    private $cache;

    /**
     * @var \BrowserDetector\Detector
     */
    private $detector;

    /**
     * @param \Monolog\Logger                   $logger
     * @param \Psr\Cache\CacheItemPoolInterface $cache
     * @param \BrowserDetector\Detector         $detector
     * @param string                            $sourcesDirectory
     * @param string                            $targetDirectory
     */
    public function __construct(Logger $logger, CacheItemPoolInterface $cache, Detector $detector, string $sourcesDirectory, string $targetDirectory)
    {
        $this->sourcesDirectory = $sourcesDirectory;
        $this->targetDirectory  = $targetDirectory;
        $this->logger           = $logger;
        $this->cache            = $cache;
        $this->detector         = $detector;

        parent::__construct();
    }

    /**
     * Configures the current command.
     */
    protected function configure(): void
    {
        $this
            ->setName('create-tests')
            ->setDescription('Creates tests from the apache log files')
            ->addOption(
                'resources',
                null,
                InputOption::VALUE_REQUIRED,
                'Where the resource files are located',
                $this->sourcesDirectory
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
     *
     * @return int|null null or 0 if everything went fine, or an error code
     *
     * @see    setCode()
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $consoleLogger = new ConsoleLogger($output);
        $this->logger->pushHandler(new PsrHandler($consoleLogger));

        $detectorTargetDirectory = 'vendor/mimmi20/browser-detector-tests/tests/issues/';

        $output->writeln('reading already existing tests ...');
        $detectorChecks = [];
        $browscapChecks = [];

        foreach ((new DetectorSource($this->logger, $this->cache))->getUserAgents() as $useragent) {
            $useragent = trim($useragent);

            if (array_key_exists($useragent, $detectorChecks)) {
                $this->logger->alert('    UA "' . $useragent . '" added more than once --> skipped');

                continue;
            }

            $detectorChecks[$useragent] = 1;
        }

        foreach ((new BrowscapSource($this->logger, $this->cache))->getUserAgents() as $useragent) {
            $useragent = trim($useragent);

            if (array_key_exists($useragent, $browscapChecks)) {
                $this->logger->alert('    UA "' . $useragent . '" added more than once --> skipped');

                continue;
            }

            $browscapChecks[$useragent] = 1;
        }

        $targetDirectoryHelper = new TargetDirectory();

        $output->writeln('detect next test number ...');

        try {
            $number = $targetDirectoryHelper->getNextTest($detectorTargetDirectory);
        } catch (\UnexpectedValueException $e) {
            $this->logger->critical($e);
            $output->writeln($e->getMessage());

            return 1;
        }

        $output->writeln('next test: ' . $number);
        $output->writeln('detect directory to write new tests ...');

        $targetDirectory = $detectorTargetDirectory . sprintf('%1$07d', $number) . '/';

        $output->writeln('target directory: ' . $targetDirectory);

        if (!file_exists($targetDirectory)) {
            mkdir($targetDirectory);
        }

        $output->writeln('reading new files ...');

        $sourcesDirectory     = $input->getOption('resources');
        $detectorTotalCounter = 0;
        $browscapTotalCounter = 0;
        $detectorTestWriter   = new DetectorTestWriter($this->logger);
        $browscapTestWriter   = new BrowscapTestWriter($this->logger, $this->targetDirectory);

        foreach ((new DirectorySource($this->logger, $sourcesDirectory))->getTests() as $useragent => $result) {
            $useragent = trim($useragent);

            if (!array_key_exists($useragent, $browscapChecks)) {
                $browscapTestWriter->write($result, $number, $useragent, $browscapTotalCounter);
            }

            $browscapChecks[$useragent] = $number;

            if (array_key_exists($useragent, $detectorChecks)) {
                $this->logger->info('    UA "' . $useragent . '" added more than once --> skipped');

                continue;
            }

            $detectorChecks[$useragent] = $number;

            if ($detectorTestWriter->write($result, $targetDirectory, $number, $useragent, $detectorTotalCounter)) {
                $number          = $targetDirectoryHelper->getNextTest($detectorTargetDirectory);
                $targetDirectory = $detectorTargetDirectory . sprintf('%1$07d', $number) . '/';

                $output->writeln('next test: ' . $number);
                $output->writeln('target directory: ' . $targetDirectory);

                if (!file_exists($targetDirectory)) {
                    mkdir($targetDirectory);
                }
            }
        }

        $output->writeln('');
        $output->writeln('tests created for BrowserDestector: ' . $detectorTotalCounter);
        $output->writeln('tests created for Browscap:         ' . $browscapTotalCounter);

        return 0;
    }
}
