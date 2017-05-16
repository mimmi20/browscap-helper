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

use BrowserDetector\Detector;
use Monolog\Handler\PsrHandler;
use Monolog\Logger;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;
use UaResult\Os\OsInterface;
use UaResult\Result\Result;
use Wurfl\Request\GenericRequestFactory;

/**
 * Class RewriteTestsCommand
 *
 * @category   Browscap Helper
 *
 * @author     Thomas Müller <mimmi20@live.de>
 */
class RewriteTestsCommand extends Command
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
     * @var \BrowserDetector\Detector
     */
    private $detector = null;

    /**
     * @param \Monolog\Logger                   $logger
     * @param \Psr\Cache\CacheItemPoolInterface $cache
     * @param \BrowserDetector\Detector         $detector
     */
    public function __construct(Logger $logger, CacheItemPoolInterface $cache, Detector $detector)
    {
        $this->logger   = $logger;
        $this->cache    = $cache;
        $this->detector = $detector;

        parent::__construct();
    }

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this
            ->setName('rewrite-tests')
            ->setDescription('Rewrites existing tests');
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

        $sourceDirectory = 'vendor/mimmi20/browser-detector-tests/tests/issues/';

        $filesArray  = scandir($sourceDirectory, SCANDIR_SORT_ASCENDING);
        $files       = [];
        $testCounter = [];
        $groups      = [];

        foreach ($filesArray as $filename) {
            if (in_array($filename, ['.', '..'])) {
                continue;
            }

            if (!is_dir($sourceDirectory . DIRECTORY_SEPARATOR . $filename)) {
                $files[] = $filename;
                $this->logger->warn('file ' . $filename . ' is out of strcture');
                continue;
            }

            $subdirFilesArray = scandir($sourceDirectory . DIRECTORY_SEPARATOR . $filename, SCANDIR_SORT_ASCENDING);

            foreach ($subdirFilesArray as $subdirFilename) {
                if (in_array($subdirFilename, ['.', '..'])) {
                    continue;
                }

                $fullFilename = $filename . DIRECTORY_SEPARATOR . $subdirFilename;
                $files[]      = $fullFilename;
                $group        = $filename;

                $groups[$fullFilename]              = $group;
                $testCounter[$group][$fullFilename] = 0;
            }
        }

        $checks       = [];
        $g            = null;
        $groupCounter = 0;

        foreach ($files as $fullFilename) {
            $file  = new \SplFileInfo($sourceDirectory . DIRECTORY_SEPARATOR . $fullFilename);
            $group = $groups[$fullFilename];

            if ($g !== $group) {
                $groupCounter = 0;
                $g            = $group;
            }

            $newCounter = $this->handleFile($output, $file, $checks, $groupCounter, $group);

            if (!$newCounter) {
                continue;
            }

            $testCounter[$group][$fullFilename] += $newCounter;
        }

        $circleFile      = 'vendor/mimmi20/browser-detector-tests/circle.yml';
        $circleciContent = 'machine:
  php:
    version: 7.1.0
  timezone:
    Europe/Berlin

dependencies:
  override:
    - composer update --optimize-autoloader --prefer-dist --prefer-stable --no-interaction --no-progress

test:
  override:';

        $circleLines = [];

        foreach ($testCounter as $group => $filesinGroup) {
            $count = 0;

            foreach (array_keys($filesinGroup) as $fileinGroup) {
                $count += $testCounter[$group][$fileinGroup];
            }

            $circleLines[$group] = $count;
        }

        $countArray = [];
        $groupArray = [];

        foreach ($circleLines as $group => $count) {
            $countArray[$group] = $count;
            $groupArray[$group] = $group;
        }

        array_multisort(
            $countArray,
            SORT_NUMERIC,
            SORT_ASC,
            $groupArray,
            SORT_NUMERIC,
            SORT_ASC,
            $circleLines
        );

        foreach ($circleLines as $group => $count) {
            if ($count >= 100) {
                $columns = 111 + 2 * mb_strlen((string) $count);
            } else {
                $columns = $count + 11 + 2 * mb_strlen((string) $count);
            }

            $circleciContent .= PHP_EOL;
            $circleciContent .= '    #' . str_pad((string) $count, 6, ' ', STR_PAD_LEFT) . ' test' . ($count !== 1 ? 's' : '');
            $circleciContent .= PHP_EOL;
            $circleciContent .= '    - php -n vendor/bin/phpunit --no-coverage --colors=auto';
            $circleciContent .= ' --columns ' . $columns . ' tests/UserAgentsTest/T' . $group . 'Test.php';
            $circleciContent .= PHP_EOL;

            $testContent = '<?php
/**
 * This file is part of the browser-detector-tests package.
 *
 * Copyright (c) 2015-2017, Thomas Mueller <mimmi20@live.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);
namespace BrowserDetectorTest\UserAgentsTest;

use BrowserDetectorTest\UserAgentsTest;
use UaResult\Result\Result;

/**
 * Class UserAgentsTest
 *
 * @category   CompareTest
 *
 * @author     Thomas Mueller <mimmi20@live.de>
 * @group      useragenttest
 * @group      integration
 * @group      ' . $group . '
 */
class T' . $group . 'Test extends UserAgentsTest
{
    /**
     * @var string
     */
    protected $sourceDirectory = \'tests/issues/' . $group . '/\';

    /**
     * @dataProvider userAgentDataProvider
     *
     * @param string                  $userAgent
     * @param \UaResult\Result\Result $expectedResult
     *
     * @throws \\Exception
     * @group  integration
     * @group  useragenttest
     * @group  ' . $group . '
     */
    public function testUserAgents($userAgent, Result $expectedResult)
    {
        parent::testUserAgents($userAgent, $expectedResult);
    }
}
';
            $testFile = 'vendor/mimmi20/browser-detector-tests/tests/UserAgentsTest/T' . $group . 'Test.php';
            file_put_contents($testFile, $testContent);
        }

        $output->writeln('writing ' . $circleFile . ' ...');
        file_put_contents($circleFile, $circleciContent);

        $output->writeln('done');
    }

    /**
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param \SplFileInfo                                      $file
     * @param array                                             $checks
     * @param int                                               $groupCounter
     * @param int                                               $group
     *
     * @return int
     */
    private function handleFile(
        OutputInterface $output,
        \SplFileInfo $file,
        array &$checks,
        &$groupCounter,
        $group
    ) {
        $output->writeln('file ' . $file->getBasename());
        $this->logger->info('    checking ...');

        /** @var $file \SplFileInfo */
        if (!$file->isFile() || $file->getExtension() !== 'json') {
            return 0;
        }

        $this->logger->info('    reading ...');

        $tests = json_decode(file_get_contents($file->getPathname()), false);

        if (is_array($tests)) {
            $tests = (object) $tests;
        }

        $oldCounter = count(get_object_vars($tests));

        if ($oldCounter < 1) {
            $this->logger->info('    file does not contain any test');
            unlink($file->getPathname());

            return 0;
        }

        if (1 === $oldCounter) {
            $this->logger->info('    contains 1 test');
        } else {
            $this->logger->info('    contains ' . $oldCounter . ' tests');
        }

        $this->logger->info('    processing ...');
        $outputDetector = [];

        foreach ($tests as $key => $test) {
            if (is_array($test)) {
                $test = (object) $test;
            }

            if (isset($checks[$test->ua])) {
                // UA was added more than once
                $this->logger->info('    UA "' . $test->ua . '" added more than once, now for key "' . $key . '", before for key "' . $checks[$test->ua] . '"');
                unset($tests->$key);
                continue;
            }

            $output->writeln('    processing Test ' . $key . ' ...');

            $checks[$test->ua] = $key;
            $newKey            = 'test-' . sprintf('%1$08d', $group) . '-' . sprintf('%1$08d', $groupCounter);

            $outputDetector += [
                $newKey => [
                    'ua'     => $test->ua,
                    'result' => $this->handleTest($test->ua)->toArray(false),
                ],
            ];
            ++$groupCounter;
        }

        $newCounter = count($outputDetector);

        $this->logger->info('    contains now ' . $newCounter . ' tests');

        if ($newCounter < 1) {
            $this->logger->info('    all tests are removed from the file');
            unlink($file->getPathname());

            return 0;
        }

        if ($newCounter < $oldCounter) {
            $this->logger->info('    ' . ($oldCounter - $newCounter) . ' test(s) is/are removed from the file');
        }

        $output->writeln('    removing old file');
        unlink($file->getPathname());

        $output->writeln('    rewriting file');

        file_put_contents(
            $file->getPathname(),
            json_encode($outputDetector, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_FORCE_OBJECT) . PHP_EOL
        );

        return $newCounter;
    }

    /**
     * @param string $useragent
     *
     * @return \UaResult\Result\Result
     */
    private function handleTest($useragent)
    {
        $this->logger->info('        rewriting');

        $result = (new Detector($this->cache, $this->logger))->getBrowser($useragent);

        $this->logger->info('        rewriting browser');

        /** @var \UaResult\Browser\Browser $browser */
        $browser = $result->getBrowser();

        if (null === $browser || in_array($browser->getName(), [null, 'unknown'])) {
            $browser = new \UaResult\Browser\Browser(null);
        }

        /* rewrite platforms */

        $this->logger->info('        rewriting platform');

        $platform = $result->getOs();

        if (null === $platform) {
            $platform = new \UaResult\Os\Os(null, null);
        }

        /* @var $platform OsInterface|null */

        $this->logger->info('        rewriting device');

        /** rewrite devices */

        $device = $result->getDevice();

        if (null === $device
            || in_array($device->getDeviceName(), [null, 'unknown'])
            || (!in_array($device->getDeviceName(), ['general Desktop', 'general Apple Device'])
                && false !== mb_stripos($device->getDeviceName(), 'general'))
        ) {
            $device = new \UaResult\Device\Device(null, null);
        }

        /** rewrite engines */

        /** @var \UaResult\Engine\EngineInterface $engine */
        $engine = $result->getEngine();

        if (null === $engine) {
            $engine = new \UaResult\Engine\Engine(null);
        }

        $this->logger->info('        generating result');

        $request = (new GenericRequestFactory())->createRequestForUserAgent($useragent);

        return new Result($request, $device, $platform, $browser, $engine);
    }
}
