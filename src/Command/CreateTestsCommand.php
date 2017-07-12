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
use BrowserDetector\Detector;
use BrowserDetector\Version\VersionInterface;
use League\Flysystem\UnreadableFileException;
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
use Wurfl\Request\GenericRequestFactory;

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
                continue;
            }

            $checks[$useragent] = $useragent;
        }

        $targetDirectoryHelper = new TargetDirectory();

        $output->writeln('detect next test number ...');
        try {
            $number = $targetDirectoryHelper->getNextTest($output);
        } catch (UnreadableFileException $e) {
            $this->logger->critical($e);
            $output->writeln($e->getMessage());

            return 1;
        }

        $output->writeln('next test: ' . $number);
        $output->writeln('detect directory to write new tests ...');

        try {
            $targetDirectory = $targetDirectoryHelper->getPath($output);
        } catch (UnreadableFileException $e) {
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
        $outputBrowscap   = "<?php\n\nreturn [\n";
        $outputDetector   = [];
        $counter          = 0;
        $issue            = 'test-' . sprintf('%1$08d', $number);
        $fileCounter      = 0;
        $chunkCounter     = 0;
        $totalCounter     = 0;

        foreach ((new DirectorySource($this->logger, $sourcesDirectory))->getUserAgents() as $useragent) {
            $useragent = trim($useragent);

            if (isset($checks[$useragent])) {
                continue;
            }

            $this->parseLine($useragent, $counter, $outputBrowscap, $outputDetector, $number);
            $checks[$useragent] = $issue;

            ++$counter;
            ++$chunkCounter;
            ++$totalCounter;

            file_put_contents(
                $targetDirectory . 'test-' . sprintf('%1$07d', $number) . '-' . sprintf('%1$03d', (int) $fileCounter) . '.json',
                json_encode(
                    $outputDetector,
                    JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_FORCE_OBJECT
                ) . PHP_EOL
            );

            if ($chunkCounter >= 100) {
                $chunkCounter   = 0;
                $outputDetector = [];
                ++$fileCounter;
            }

            if ($fileCounter >= 10) {
                $chunkCounter    = 0;
                $outputDetector  = [];
                $fileCounter     = 0;
                $counter         = 0;
                $number          = $targetDirectoryHelper->getNextTest($output);
                $targetDirectory = $targetDirectoryHelper->getPath($output);

                $output->writeln('next test: ' . $number);
                $output->writeln('target directory: ' . $targetDirectory);

                if (!file_exists($targetDirectory)) {
                    mkdir($targetDirectory);
                }
            }
        }

        $outputBrowscap .= "];\n";

        file_put_contents('results/issue-' . sprintf('%1$05d', $number) . '.php', $outputBrowscap);

        $output->writeln('');
        $output->writeln($totalCounter . ' tests exported');

        return 0;
    }

    /**
     * @param string $ua
     * @param int    $counter
     * @param string &$outputBrowscap
     * @param array  &$outputDetector
     * @param int    $testNumber
     */
    private function parseLine($ua, $counter, &$outputBrowscap, array &$outputDetector, $testNumber)
    {
        $this->logger->info('      detecting');

        $result = (new Detector($this->cache, $this->logger))->getBrowser($ua);

        $this->logger->info('      detecting platform ...');

        /** @var \UaResult\Os\OsInterface $platform */
        $platform = $result->getOs();

        if (null === $platform) {
            $platform = new Os(null, null);
        }

        $this->logger->info('      detecting device ...');

        /** @var \UaResult\Device\DeviceInterface $device */
        $device = $result->getDevice();

        if (null === $device
            || in_array($device->getDeviceName(), [null, 'unknown'])
            || (!in_array($device->getDeviceName(), ['general Desktop', 'general Apple Device'])
                && false !== mb_stripos($device->getDeviceName(), 'general'))
        ) {
            $device = new Device(null, null);
        }

        /** @var \UaResult\Engine\EngineInterface $engine */
        $engine = $result->getEngine();

        if (null === $engine) {
            $engine = new Engine(null);
        }

        $this->logger->info('      detecting browser ...');

        /** @var \UaResult\Browser\BrowserInterface $browser */
        $browser = $result->getBrowser();

        if (null === $browser) {
            $browser = new Browser(null);
        }

        $formatedIssue   = sprintf('%1$05d', (int) $testNumber);
        $formatedCounter = sprintf('%1$05d', (int) $counter);

        $this->logger->info('      writing browscap data ...');

        $outputBrowscap .= "    'issue-$formatedIssue-$formatedCounter' => [
        'ua' => '" . str_replace(['\\', "'"], ['\\\\', "\\'"], $ua) . "',
        'properties' => [
            'Comment' => 'Default Browser',
            'Browser' => '" . str_replace(['\\', "'"], ['\\\\', "\\'"], $browser->getName()) . "',
            'Browser_Type' => '" . $browser->getType()->getName() . "',
            'Browser_Bits' => '" . $browser->getBits() . "',
            'Browser_Maker' => '" . $browser->getManufacturer()->getName() . "',
            'Browser_Modus' => '" . $browser->getModus() . "',
            'Version' => '" . $browser->getVersion()->getVersion() . "',
            'Platform' => '" . $platform->getName() . "',
            'Platform_Version' => '" . $platform->getVersion()->getVersion(VersionInterface::IGNORE_MICRO) . "',
            'Platform_Description' => '',
            'Platform_Bits' => '" . $platform->getBits() . "',
            'Platform_Maker' => '" . $platform->getManufacturer()->getName() . "',
            'Alpha' => false,
            'Beta' => false,
            'isMobileDevice' => " . ($device->getType()->isMobile() ? 'true' : 'false') . ",
            'isTablet' => " . ($device->getType()->isTablet() ? 'true' : 'false') . ",
            'isSyndicationReader' => false,
            'Crawler' => " . ($browser->getType()->isBot() ? 'true' : 'false') . ",
            'isFake' => false,
            'isAnonymized' => false,
            'isModified' => false,
            'Device_Name' => '" . $device->getMarketingName() . "',
            'Device_Maker' => '" . $device->getManufacturer()->getName() . "',
            'Device_Type' => '" . $device->getType()->getName() . "',
            'Device_Pointing_Method' => '" . $device->getPointingMethod() . "',
            'Device_Code_Name' => '" . $device->getDeviceName() . "',
            'Device_Brand_Name' => '" . $device->getBrand()->getBrandName() . "',
            'RenderingEngine_Name' => '" . $engine->getName() . "',
            'RenderingEngine_Version' => 'unknown',
            'RenderingEngine_Maker' => '" . $engine->getManufacturer()->getName() . "',
        ],
        'full' => true,
        'lite' => true,
        'standard' => true,
    ],\n";

        $this->logger->info('      detecting test name ...');

        $formatedIssue   = sprintf('%1$07d', (int) $testNumber);
        $formatedCounter = sprintf('%1$05d', (int) $counter);

        $this->logger->info('      detecting request ...');

        $request = (new GenericRequestFactory())->createRequestFromString($ua);

        $result = new Result($request, $device, $platform, $browser, $engine);

        $outputDetector['test-' . $formatedIssue . '-' . $formatedCounter] = [
            'ua'     => $ua,
            'result' => $result->toArray(false),
        ];
    }
}
