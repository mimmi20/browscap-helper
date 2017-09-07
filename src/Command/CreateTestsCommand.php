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
use BrowscapHelper\Source\DetectorSource;
use BrowscapHelper\Source\DirectorySource;
use BrowscapHelper\Writer\BrowscapTestWriter;
use BrowscapHelper\Writer\DetectorTestWriter;
use BrowserDetector\Detector;
use BrowserDetector\Helper\GenericRequestFactory;
use BrowserDetector\Version\VersionInterface;
use Monolog\Handler\PsrHandler;
use Monolog\Logger;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;
use UaResult\Browser\Browser;
use UaResult\Device\Device;
use UaResult\Engine\Engine;
use UaResult\Os\Os;
use UaResult\Result\Result;

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
     * @var \Monolog\Logger
     */
    private $logger = null;

    /**
     * @var \Psr\Cache\CacheItemPoolInterface
     */
    private $cache = null;

    /**
     * @var \BrowserDetector\Detector
     */
    private $detector = null;

    /**
     * @param \Monolog\Logger                   $logger
     * @param \Psr\Cache\CacheItemPoolInterface $cache
     * @param \BrowserDetector\Detector         $detector
     * @param string                            $sourcesDirectory
     */
    public function __construct(Logger $logger, CacheItemPoolInterface $cache, Detector $detector, $sourcesDirectory)
    {
        $this->sourcesDirectory = $sourcesDirectory;
        $this->logger           = $logger;
        $this->cache            = $cache;
        $this->detector         = $detector;

        parent::__construct();
    }

    /**
     * Configures the current command.
     */
    protected function configure()
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
     * @return null|int null or 0 if everything went fine, or an error code
     *
     * @see    setCode()
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $consoleLogger = new ConsoleLogger($output);
        $this->logger->pushHandler(new PsrHandler($consoleLogger));

        $output->writeln('reading already existing tests ...');
        $checks = [];

        foreach ((new DetectorSource($this->logger, $this->cache))->getUserAgents() as $useragent) {
            if (isset($checks[$useragent])) {
                $this->logger->alert('    UA "' . $useragent . '" added more than once --> skipped');
                continue;
            }

            $checks[$useragent] = 1;
        }

        $targetDirectoryHelper = new TargetDirectory();

        $output->writeln('detect next test number ...');
        try {
            $number = $targetDirectoryHelper->getNextTest();
        } catch (\UnexpectedValueException $e) {
            $this->logger->critical($e);
            $output->writeln($e->getMessage());

            return 1;
        }

        $output->writeln('next test: ' . $number);
        $output->writeln('detect directory to write new tests ...');

        try {
            $targetDirectory = $targetDirectoryHelper->getPath();
        } catch (\UnexpectedValueException $e) {
            $this->logger->critical($e);
            $output->writeln($e->getMessage());

            return 1;
        }

        $output->writeln('target directory: ' . $targetDirectory);

        if (!file_exists($targetDirectory)) {
            mkdir($targetDirectory);
        }

        $output->writeln('reading new files ...');

        $sourcesDirectory = $input->getOption('resources');
        $totalCounter     = 0;
        $detectorTestWriter = new DetectorTestWriter($this->logger, $targetDirectory);
        $browscapTestWriter = new BrowscapTestWriter($this->logger, 'results/');

        foreach ((new DirectorySource($this->logger, $sourcesDirectory))->getUserAgents() as $useragent) {
            if (isset($checks[$useragent])) {
                $this->logger->error('    UA "' . $useragent . '" added more than once --> skipped');
                continue;
            }

            $checks[$useragent] = $number;

            $platform = new Os(null, null);
            $device   = new Device(null, null);
            $engine   = new Engine(null);
            $browser  = new Browser(null);
            $request = (new GenericRequestFactory())->createRequestFromString($useragent);
            $result  = new Result($request->getHeaders(), $device, $platform, $browser, $engine);

            $browscapTestWriter->write($result, $number);

            if ($detectorTestWriter->write($result, $number, $totalCounter)) {
                $number          = $targetDirectoryHelper->getNextTest();
                $targetDirectory = $targetDirectoryHelper->getPath();

                $output->writeln('next test: ' . $number);
                $output->writeln('target directory: ' . $targetDirectory);

                if (!file_exists($targetDirectory)) {
                    mkdir($targetDirectory);
                }
            }
        }

        $output->writeln('');
        $output->writeln($totalCounter . ' tests exported');

        return 0;
    }
}
