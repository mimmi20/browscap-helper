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
namespace BrowscapHelper\Command;

use BrowscapHelper\Source\BrowscapSource;
use BrowscapHelper\Source\CrawlerDetectSource;
use BrowscapHelper\Source\DonatjSource;
use BrowscapHelper\Source\JsonFileSource;
use BrowscapHelper\Source\MobileDetectSource;
use BrowscapHelper\Source\PiwikSource;
use BrowscapHelper\Source\SinergiSource;
use BrowscapHelper\Source\TxtCounterFileSource;
use BrowscapHelper\Source\TxtFileSource;
use BrowscapHelper\Source\Ua\UserAgent;
use BrowscapHelper\Source\UaParserJsSource;
use BrowscapHelper\Source\UapCoreSource;
use BrowscapHelper\Source\WhichBrowserSource;
use BrowscapHelper\Source\WootheeSource;
use BrowscapHelper\Source\YzalisSource;
use BrowscapHelper\Source\ZsxsoftSource;
use JsonClass\Json;
use Symfony\Component\Cache\Adapter\NullAdapter;
use Symfony\Component\Cache\Psr16Cache;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;

final class CopyTestsCommand extends Command
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
     *
     * @throws \Symfony\Component\Console\Exception\LogicException
     */
    public function __construct(string $sourcesDirectory, string $targetDirectory)
    {
        $this->sourcesDirectory = $sourcesDirectory;
        $this->targetDirectory  = $targetDirectory;

        parent::__construct();
    }

    /**
     * Configures the current command.
     *
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     */
    protected function configure(): void
    {
        $this
            ->setName('copy-tests')
            ->setDescription('Copies tests from browscap and other libraries')
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
     * @throws \Symfony\Component\Console\Exception\LogicException           When this abstract method is not implemented
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     *
     * @return int 0 if everything went fine, or an error code
     *
     * @see    setCode()
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $consoleLogger = new ConsoleLogger($output);

        $sourcesDirectory = $input->getOption('resources');

        $testSource = 'tests';
        $txtChecks  = [];

        $output->writeln('reading already existing tests ...');

        foreach ($this->getHelper('existing-tests-loader')->getHeaders($output, [new JsonFileSource($output, $testSource)]) as $header) {
            $seachHeader = (string) UserAgent::fromHeaderArray($header);

            if (array_key_exists($seachHeader, $txtChecks)) {
                $consoleLogger->alert('    Header "' . $seachHeader . '" added more than once --> skipped');

                continue;
            }

            $txtChecks[$seachHeader] = 1;
        }

        $this->getHelper('existing-tests-remover')->remove($output, $testSource);

        $output->writeln('init sources ...');

        $cache   = new Psr16Cache(new NullAdapter());
        $sources = [
            new BrowscapSource($output),
            new PiwikSource($output),
            new UapCoreSource($output, $cache),
            new WhichBrowserSource($output),
            new WootheeSource($output),
            new MobileDetectSource($output),
            new YzalisSource($output),
            new CrawlerDetectSource($output),
            new DonatjSource($output),
            new SinergiSource($output),
            new UaParserJsSource($output),
            new ZsxsoftSource($output),
            new TxtFileSource($output, $sourcesDirectory),
            new TxtCounterFileSource($output, $sourcesDirectory),
        ];

        $output->writeln('copy tests from sources ...');
        $txtTotalCounter = 0;

        foreach ($this->getHelper('existing-tests-loader')->getHeaders($output, $sources) as $header) {
            $seachHeader = (string) UserAgent::fromHeaderArray($header);

            if (array_key_exists($seachHeader, $txtChecks)) {
                $consoleLogger->debug('Header "' . $seachHeader . '" added more than once --> skipped');

                continue;
            }

            try {
                (new Json())->encode($seachHeader);
            } catch (\ExceptionalJSON\EncodeErrorException $e) {
                $consoleLogger->debug('Header "' . $seachHeader . '" contained illegal characters --> skipped');

                continue;
            }

            $txtChecks[$seachHeader] = 1;
            ++$txtTotalCounter;
        }

        $output->writeln('rewrite tests ...');

        $this->getHelper('rewrite-tests')->rewrite($output, $txtChecks, $testSource);

        $output->writeln('');
        $output->writeln('tests copied for Browscap helper:    ' . $txtTotalCounter);
        $output->writeln('tests available for Browscap helper: ' . count($txtChecks));

        return 0;
    }
}
