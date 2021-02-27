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
use BrowscapHelper\Source\TxtFileSource;
use BrowscapHelper\Source\Ua\UserAgent;
use BrowscapPHP\Browscap;
use BrowscapPHP\BrowscapUpdater;
use BrowscapPHP\Exception;
use BrowscapPHP\Helper\IniLoaderInterface;
use BrowserDetector\Version\VersionFactory;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Psr16Cache;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;
use UaBrowserType\TypeLoader;
use UaRequest\GenericRequestFactory;
use UaResult\Browser\Browser;
use UaResult\Company\Company;
use UaResult\Device\Device;
use UaResult\Device\Display;
use UaResult\Engine\Engine;
use UaResult\Os\Os;
use UaResult\Result\Result;

final class CreateTestsCommand extends Command
{
    /** @var string */
    private $sourcesDirectory = '';

    /**
     * @param string $sourcesDirectory
     *
     * @throws \Symfony\Component\Console\Exception\LogicException
     */
    public function __construct(string $sourcesDirectory)
    {
        $this->sourcesDirectory = $sourcesDirectory;

        parent::__construct();
    }

    /**
     * Configures the current command.
     *
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     *
     * @return void
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
     * @see    setCode()
     *
     * @param InputInterface  $input  An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     *
     * @throws \Symfony\Component\Console\Exception\LogicException           When this abstract method is not implemented
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     * @throws \BrowserDetector\Version\NotNumericException
     * @throws \BrowserDetector\Loader\NotFoundException
     * @throws \BrowscapPHP\Exception\FetcherException
     * @throws \BrowscapPHP\Exception\ErrorCachedVersionException
     *
     * @return int 0 if everything went fine, or an error code
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $consoleLogger = new ConsoleLogger($output);

        $output->writeln('preparing browscap ...', OutputInterface::VERBOSITY_NORMAL);

        $browscapChecks = [];

        $cache           = new Psr16Cache(new ArrayAdapter());
        $browscapUpdater = new BrowscapUpdater($cache, $consoleLogger, null, 10);

        try {
            $browscapUpdater->update(IniLoaderInterface::PHP_INI_FULL);
        } catch (\BrowscapPHP\Helper\Exception $e) {
            $consoleLogger->emergency($e);

            return 1;
        }

        $browscap = new Browscap($cache, $consoleLogger);
        $tests    = [];

        $output->writeln('reading already existing tests ...', OutputInterface::VERBOSITY_NORMAL);

        foreach ($this->getHelper('existing-tests-loader')->getHeaders($output, [new BrowscapSource()]) as $header) {
            $seachHeader = (string) UserAgent::fromHeaderArray($header);

            if (array_key_exists($seachHeader, $browscapChecks)) {
                $output->writeln('<error>' . sprintf('Header "%s" added more than once --> skipped', $seachHeader) . '</error>', OutputInterface::VERBOSITY_NORMAL);

                continue;
            }

            $browscapChecks[$seachHeader] = 1;

            $headers = UserAgent::fromString($seachHeader)->getHeaders();

            if (1 < count($headers)) {
                $consoleLogger->debug('    Header "' . $seachHeader . '" has more than one Header --> skipped');

                continue;
            }

            try {
                $result = $browscap->getBrowser($headers['user-agent']);
            } catch (Exception $e) {
                $consoleLogger->error($e);
                continue;
            }

            $keys = [
                (string) $result->browser,
                (string) $result->version,
                (string) $result->renderingengine_name,
                (string) $result->renderingengine_version,
                (string) $result->platform,
                (string) $result->platform_version,
                (string) $result->device_code_name,
                (string) $result->device_name,
                (string) $result->device_maker,
            ];

            $key = implode('-', $keys);

            if (array_key_exists($key, $tests)) {
                $consoleLogger->debug('    Header "' . $seachHeader . '" has is similar to already detected result --> skipped');

                continue;
            }

            $tests[$key] = 1;
        }

        $output->writeln('init sources ...', OutputInterface::VERBOSITY_NORMAL);

        $sourcesDirectory      = $input->getOption('resources');
        $genericRequestFactory = new GenericRequestFactory();
        $sources               = [
            //new JsonFileSource($testSource),
            new TxtFileSource($sourcesDirectory),
            //new TxtCounterFileSource($sourcesDirectory),
        ];

        $output->writeln('selecting tests from sources ...', OutputInterface::VERBOSITY_NORMAL);
        $testResults = [];

        $browserLoader = new TypeLoader();
        $deviceLoader  = new \UaDeviceType\TypeLoader();

        foreach ($this->getHelper('existing-tests-loader')->getHeaders($output, $sources) as $header) {
            $seachHeader = (string) UserAgent::fromHeaderArray($header);

            if (array_key_exists($seachHeader, $browscapChecks)) {
                $consoleLogger->debug('    Header "' . $seachHeader . '" added more than once --> skipped');

                continue;
            }

//            if (false === mb_stripos($seachHeader, 'EdgA')) {
//                $consoleLogger->info('    Header "' . $seachHeader . '" does not match search --> skipped');
//
//                continue;
//            }

//            if (!(bool) preg_match('/ NT-/', $seachHeader)) {
//                $consoleLogger->info('    Header "' . $seachHeader . '" does not match search --> skipped');
//
//                continue;
//            }

            $headers = UserAgent::fromString($seachHeader)->getHeaders();

            if (1 < count($headers)) {
                $consoleLogger->warning('    Header "' . $seachHeader . '" has more than one Header --> skipped');

                continue;
            }

            try {
                $result = $browscap->getBrowser($headers['user-agent']);
            } catch (Exception $e) {
                $consoleLogger->error($e);
                continue;
            }

//            if (in_array($result->device_name, ['general Mobile Phone', 'general Tablet', 'general Mobile Device'], true)) {
//                $consoleLogger->debug('    Header "' . $seachHeader . '" has unknown device --> skipped');
//
//                continue;
//            }
//
//            if (in_array($result->browser, ['Default Browser'], true)) {
//                $consoleLogger->debug('    Header "' . $seachHeader . '" has unknown browser --> skipped');
//
//                continue;
//            }

//            $keys = [
//                (string) $result->browser,
//                (string) $result->version,
//                (string) $result->renderingengine_name,
//                (string) $result->renderingengine_version,
//                (string) $result->platform,
//                (string) $result->platform_version,
//                (string) $result->device_code_name,
//                (string) $result->device_name,
//            ];
//
//            $key = implode('-', $keys);
//
//            if (array_key_exists($key, $tests)) {
//                $consoleLogger->warning('    Header "' . $seachHeader . '" has is similar to already detected result --> skipped');
//
//                continue;
//            }
//
//            $tests[$key] = 1;

            $browser = new Browser(
                $result->browser,
                new Company('Unknown', $result->browser_maker, null),
                (new VersionFactory())->set($result->version),
                $browserLoader->load($result->browser_type),
                0,
                null
            );
            $device = new Device(
                $result->device_code_name,
                $result->device_name,
                new Company('Unknown', $result->device_maker, null),
                new Company('Unknown', null, $result->device_brand_name),
                $deviceLoader->load($result->device_type),
                new Display(null, null, null, null)
            );
            $platform = new Os(
                $result->platform,
                null,
                new Company('Unknown', $result->platform_maker, null),
                (new VersionFactory())->set($result->platform_version),
                null
            );
            $engine = new Engine(
                $result->renderingengine_name,
                new Company('Unknown', $result->renderingengine_maker, null),
                (new VersionFactory())->set($result->renderingengine_version)
            );

            $request = $genericRequestFactory->createRequestFromArray($headers);
            $result  = new Result($request->getHeaders(), $device, $platform, $browser, $engine);

            $browscapChecks[$seachHeader] = 1;
            $testResults[]                = $result;
        }

        $output->writeln('write new test files ...', OutputInterface::VERBOSITY_NORMAL);

        $folderChunks         = array_chunk($testResults, 1000);
        $browscapTotalCounter = 0;

        foreach ($folderChunks as $folderId => $folderChunk) {
            $this->getHelper('browscap-test-writer')->write($folderChunk, $folderId, $browscapTotalCounter);
        }

        $output->writeln('', OutputInterface::VERBOSITY_NORMAL);
        $output->writeln('tests created for Browscap: ' . $browscapTotalCounter, OutputInterface::VERBOSITY_NORMAL);

        return 0;
    }
}
