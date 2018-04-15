<?php
/**
 * This file is part of the browscap-helper package.
 *
 * Copyright (c) 2015-2018, Thomas Mueller <mimmi20@live.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);
namespace BrowscapHelper\Command;

use BrowscapHelper\Source\BrowscapSource;
use BrowscapHelper\Source\TxtFileSource;
use Monolog\Handler\PsrHandler;
use Monolog\Logger;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;
use UaRequest\GenericRequestFactory;
use UaResult\Browser\Browser;
use UaResult\Device\Device;
use UaResult\Engine\Engine;
use UaResult\Os\Os;
use UaResult\Result\Result;

/**
 * Class CreateTestsCommand
 *
 * @category   Browscap Helper
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
     * @param \Monolog\Logger $logger
     * @param string          $sourcesDirectory
     * @param string          $targetDirectory
     */
    public function __construct(Logger $logger, string $sourcesDirectory, string $targetDirectory)
    {
        $this->sourcesDirectory = $sourcesDirectory;
        $this->targetDirectory  = $targetDirectory;
        $this->logger           = $logger;

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
     * @throws \LogicException       When this abstract method is not implemented
     *
     * @return int|null null or 0 if everything went fine, or an error code
     *
     * @see    setCode()
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $consoleLogger = new ConsoleLogger($output);
        $this->logger->pushHandler(new PsrHandler($consoleLogger));

        $testSource = 'tests';

        $output->writeln('reading already existing tests ...');
        $browscapChecks = [];

        foreach ($this->getHelper('useragent')->getUserAgents(new BrowscapSource($this->logger), false) as $useragent) {
            if (array_key_exists($useragent, $browscapChecks)) {
                $this->logger->alert('    UA "' . $useragent . '" added more than once --> skipped');

                continue;
            }

            $browscapChecks[$useragent] = 1;
        }

        $output->writeln('detect next test numbers ...');

        $txtNumber = $this->getHelper('target-directory')->getNextTest($testSource);

        $output->writeln('next test for Browscap: ' . $txtNumber);
        $output->writeln('writing new browscap tests ...');

        $browscapTotalCounter = 0;
        $genericRequest       = new GenericRequestFactory();
        $browser              = new Browser(null);
        $device               = new Device(null, null);
        $platform             = new Os(null, null);
        $engine               = new Engine(null);

        foreach ($this->getHelper('useragent')->getUserAgents(new TxtFileSource($this->logger, $testSource)) as $useragent) {
            if (array_key_exists($useragent, $browscapChecks)) {
                continue;
            }

            if (false === mb_stripos($useragent, 'bingweb')) {
                continue;
            }

            $request = $genericRequest->createRequestFromString($useragent);
            $result  = new Result($request->getHeaders(), $device, $platform, $browser, $engine);

            $this->getHelper('browscap-test-writer')->write($result, $txtNumber, $useragent, $browscapTotalCounter);
            $browscapChecks[$useragent] = 1;
        }

        $output->writeln('');
        $output->writeln('tests created for Browscap: ' . $browscapTotalCounter);

        return 0;
    }
}
