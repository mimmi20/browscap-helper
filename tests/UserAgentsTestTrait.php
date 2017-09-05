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
namespace BrowscapHelperTest;

use BrowserDetector\Detector;
use Monolog\Handler\NullHandler;
use Monolog\Logger;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use UaResult\Result\Result;
use UaResult\Result\ResultFactory;
use UaResult\Result\ResultInterface;

trait UserAgentsTestTrait
{
    /**
     * @var \BrowserDetector\Detector
     */
    private $object = null;

    /**
     * @var \Monolog\Logger
     */
    private static $logger = null;

    /**
     * @var \Psr\Cache\CacheItemPoolInterface
     */
    private static $cache = null;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = new Detector(static::getCache(), static::getLogger());
    }

    /**
     * @return array[]
     */
    public function userAgentDataProvider(): array
    {
        $start = microtime(true);

        echo 'starting provider ', static::class, ' ...';

        $data     = [];
        $iterator = new \DirectoryIterator($this->sourceDirectory);

        foreach ($iterator as $file) {
            /** @var $file \SplFileInfo */
            if (!$file->isFile() || $file->getExtension() !== 'json') {
                continue;
            }

            $tests = json_decode(file_get_contents($file->getPathname()), true);

            foreach ($tests as $key => $test) {
                if (isset($data[$key])) {
                    // Test data is duplicated for key
                    continue;
                }

                $data[$key] = [
                    'ua'     => $test['ua'],
                    'result' => (new ResultFactory())->fromArray(static::getCache(), static::getLogger(), $test['result']),
                ];
            }
        }

        echo ' finished (', str_pad(number_format(microtime(true) - $start, 4), 8, ' ', STR_PAD_LEFT), ' sec., ', str_pad((string) count($data), 6, ' ', STR_PAD_LEFT), ' test', (count($data) !== 1 ? 's' : ''), ')', PHP_EOL;

        return $data;
    }

    /**
     * @dataProvider userAgentDataProvider
     *
     * @param string                           $userAgent
     * @param \UaResult\Result\ResultInterface $expectedResult
     *
     * @throws \Exception
     */
    public function testUserAgents(string $userAgent, ResultInterface $expectedResult): void
    {
        $result = $this->object->getBrowser($userAgent);

        static::assertInstanceOf(
            \UaResult\Result\ResultInterface::class,
            $result,
            'Expected result is not an instance of "\UaResult\Result\ResultInterface" for useragent "' . $userAgent . '"'
        );

        $foundBrowser = $result->getBrowser();

        static::assertInstanceOf(
            \UaResult\Browser\BrowserInterface::class,
            $foundBrowser,
            'Expected browser is not an instance of "\UaResult\Browser\BrowserInterface" for useragent "' . $userAgent . '"'
        );

        self::assertEquals($expectedResult->getBrowser(), $foundBrowser);

        $foundEngine = $result->getEngine();

        static::assertInstanceOf(
            \UaResult\Engine\EngineInterface::class,
            $foundEngine,
            'Expected engine is not an instance of "\UaResult\Engine\EngineInterface" for useragent "' . $userAgent . '"'
        );

        self::assertEquals($expectedResult->getEngine(), $foundEngine);

        $foundPlatform = $result->getOs();

        static::assertInstanceOf(
            \UaResult\Os\OsInterface::class,
            $foundPlatform,
            'Expected platform is not an instance of "\UaResult\Os\OsInterface" for useragent "' . $userAgent . '"'
        );

        self::assertEquals($expectedResult->getOs(), $foundPlatform);

        $foundDevice = $result->getDevice();

        static::assertInstanceOf(
            \UaResult\Device\DeviceInterface::class,
            $foundDevice,
            'Expected result is not an instance of "\UaResult\Device\DeviceInterface" for useragent "' . $userAgent . '"'
        );

        self::assertEquals($expectedResult->getDevice(), $foundDevice);

        //self::assertEquals($expectedResult, $result);
    }

    /**
     * @return \Psr\Cache\CacheItemPoolInterface
     */
    private static function getCache(): CacheItemPoolInterface
    {
        if (null !== static::$cache) {
            return static::$cache;
        }

        static::$cache = new FilesystemAdapter('', 0, __DIR__ . '/../cache/');

        return static::$cache;
    }

    /**
     * @return \Psr\Log\LoggerInterface
     */
    private static function getLogger(): LoggerInterface
    {
        if (null !== static::$logger) {
            return static::$logger;
        }

        static::$logger = new Logger('browscap-helper');
        static::$logger->pushHandler(new NullHandler());

        return static::$logger;
    }
}
