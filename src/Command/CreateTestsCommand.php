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
use BrowscapHelper\Source\JsonFileSource;
use BrowscapHelper\Source\TxtCounterFileSource;
use BrowscapHelper\Source\TxtFileSource;
use BrowscapHelper\Source\Ua\UserAgent;
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
     * @param string $sourcesDirectory
     * @param string $targetDirectory
     */
    public function __construct(string $sourcesDirectory, string $targetDirectory)
    {
        $this->sourcesDirectory = $sourcesDirectory;
        $this->targetDirectory  = $targetDirectory;

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

        $testSource = 'tests';

        $output->writeln('reading already existing tests ...');
        $browscapChecks = [];

        foreach ($this->getHelper('existing-tests-reader')->getHeaders($consoleLogger, [new BrowscapSource($consoleLogger)]) as $seachHeader) {
            if (array_key_exists($seachHeader, $browscapChecks)) {
                $consoleLogger->debug('    Header "' . $seachHeader . '" added more than once --> skipped');

                continue;
            }

            $browscapChecks[$seachHeader] = 1;
        }

        $output->writeln('detect next test numbers ...');

        $txtNumber = $this->getHelper('target-directory')->getNextTest($testSource);

        $output->writeln('next test for Browscap: ' . $txtNumber);
        $output->writeln('init sources ...');

        $sourcesDirectory      = $input->getOption('resources');
        $browscapTotalCounter  = 0;
        $genericRequestFactory = new GenericRequestFactory();
        $browser               = new Browser(null);
        $device                = new Device(null, null);
        $platform              = new Os(null, null);
        $engine                = new Engine(null);

        $sources = [
            new JsonFileSource($consoleLogger, $testSource),
            new TxtFileSource($consoleLogger, $sourcesDirectory),
            new TxtCounterFileSource($consoleLogger, $sourcesDirectory),
        ];

        $output->writeln('copy tests from sources ...');

        foreach ($this->getHelper('existing-tests-reader')->getHeaders($consoleLogger, $sources) as $seachHeader) {
            if (array_key_exists($seachHeader, $browscapChecks)) {
                $consoleLogger->info('    Header "' . $seachHeader . '" added more than once --> skipped');

                continue;
            }

//            if (false === mb_stripos($seachHeader, 'EdgA')) {
//                $consoleLogger->info('    Header "' . $seachHeader . '" does not match search --> skipped');
//
//                continue;
//            }

//            if (false === preg_match('/TA\-\d{4}/', $seachHeader)) {
//                $consoleLogger->info('    Header "' . $seachHeader . '" does not match search --> skipped');
//
//                continue;
//            }

            $headers = UserAgent::fromString($seachHeader)->getHeader();
            $request = $genericRequestFactory->createRequestFromArray($headers);
            $result  = new Result($request->getHeaders(), $device, $platform, $browser, $engine);

            $this->getHelper('browscap-test-writer')->write($result, $txtNumber, $headers['user-agent'], $browscapTotalCounter);
            $browscapChecks[$seachHeader] = 1;
        }

        $output->writeln('');
        $output->writeln('tests created for Browscap: ' . $browscapTotalCounter);

        return 0;
    }
}
