<?php
/*******************************************************************************
 * INIT
 ******************************************************************************/
ini_set('memory_limit', '-1');
ini_set('max_execution_time', '-1');
ini_set('max_input_time', '-1');
ini_set('display_errors', 1);
error_reporting(E_ALL);

date_default_timezone_set('Europe/Berlin');

chdir(dirname(__DIR__));

require 'vendor/autoload.php';

$logger = new \Monolog\Logger('browser-detector-tests');
$logger->pushHandler(new \Monolog\Handler\NullHandler());

$cache    = new \WurflCache\Adapter\NullStorage();
$detector = new \BrowserDetector\BrowserDetector($cache, $logger);

$sourceDirectory = 'vendor/mimmi20/browser-detector/tests/issues/';

$filesArray = scandir($sourceDirectory, SCANDIR_SORT_ASCENDING);
$files      = [];

foreach ($filesArray as $filename) {
    if (in_array($filename, ['.', '..'])) {
        continue;
    }

    if (!is_dir($sourceDirectory . DIRECTORY_SEPARATOR . $filename)) {
        $files[] = $filename;
        continue;
    }

    $subdirFilesArray = scandir($sourceDirectory . DIRECTORY_SEPARATOR . $filename, SCANDIR_SORT_ASCENDING);

    foreach ($subdirFilesArray as $subdirFilename) {
        if (in_array($subdirFilename, ['.', '..'])) {
            continue;
        }

        $files[] = $filename . DIRECTORY_SEPARATOR . $subdirFilename;
    }
}

$checks = [];
$data   = [];

foreach ($files as $filename) {
    $file = new \SplFileInfo($sourceDirectory . DIRECTORY_SEPARATOR . $filename);

    echo 'checking file ', $file->getBasename(), ' ...', PHP_EOL;

    /** @var $file \SplFileInfo */
    if (!$file->isFile() || $file->getExtension() !== 'php') {
        continue;
    }

    echo 'reading file ', $file->getBasename(), ' ...', PHP_EOL;

    $tests = require_once $file->getPathname();

    if (empty($tests)) {
        echo 'removing empty file ', $file->getBasename(), ' ...', PHP_EOL;
        unlink($file->getPathname());

        continue;
    }

    handleFile($tests, $file, $detector, $data, $checks);
}

function handleFile(array $tests, \SplFileInfo $file, \BrowserDetector\BrowserDetector $detector, array &$data, array &$checks)
{
    $outputDetector = "<?php\n\nreturn [\n";

    foreach ($tests as $key => $test) {
        if (isset($data[$key])) {
            // Test data is duplicated for key
            echo 'Test data is duplicated for key "' . $key . '"', PHP_EOL;
            unset($tests[$key]);
            continue;
        }

        if (isset($checks[$test['ua']])) {
            // UA was added more than once
            echo 'UA "' . $test['ua'] . '" added more than once, now for key "' . $key . '", before for key "'
                . $checks[$test['ua']] . '"', PHP_EOL;
            unset($tests[$key]);
            continue;
        }

        $data[$key]          = $test;
        $checks[$test['ua']] = $key;

        $outputDetector .= handleTest($test, $detector, $key);
    }

    $basename = $file->getBasename();

    if (empty($tests)) {
        echo 'removing empty file ', $basename, ' ...', PHP_EOL;
        unlink($file->getPathname());

        return;
    }

    $outputDetector .= "];\n";

    echo 'writing file ', $basename, ' ...', PHP_EOL;

    file_put_contents($file->getPath() . '/' . $basename, $outputDetector);
}

function handleTest(array $test, \BrowserDetector\BrowserDetector $detector, $key)
{
    /** rewrite platforms */

    list($platformCodename, $platformMarketingname, $platformVersion, $platformBits, $platformMaker, $platformBrandname, $platform) = rewritePlatforms($test, $detector, $key);
    /** @var $platform \UaResult\Os\OsInterface */

    /** rewrite devices */

    list($deviceBrand, $deviceCode, $devicePointing, $deviceType, $deviceMaker, $deviceName, $deviceOrientation, $device) = rewriteDevice($test, $detector, $platform);
    /** @var $device \BrowserDetector\Matcher\Device\DeviceHasSpecificPlatformInterface */

    if (null !== ($platform = $device->detectOs())) {
        $platformCodename      = $platform->getName();
        $platformMarketingname = $platform->getMarketingName();
        $platformVersion       = $platform->getVersion()->getVersion();
        $platformBits          = $platform->getBits();
        $platformMaker         = $platform->getManufacturer();
        $platformBrandname     = $platform->getBrand();
    }

    /** rewrite test numbers */

    if (preg_match('/^test\-(\d+)\-(\d+)$/', $key, $matches)) {
        $key = 'test-' . sprintf('%1$05d', (int) $matches[1]) . '-' . sprintf('%1$05d', (int) $matches[2]);
    } elseif (preg_match('/^test\-(\d+)$/', $key, $matches)) {
        $key = 'test-' . sprintf('%1$05d', (int) $matches[1]) . '-00000';
    } elseif (preg_match('/^test\-(\d+)\-test(\d+)$/', $key, $matches)) {
        $key = 'test-' . sprintf('%1$05d', (int) $matches[1]) . '-' . sprintf('%1$05d', (int) $matches[2]);
    }

    return "    '$key' => [
        'ua'         => '" . str_replace(['\\', "'"], ['\\\\', "\\'"], $test['ua']) . "',
        'properties' => [
            'Browser_Name'            => '" . str_replace(['\\', "'"], ['\\\\', "\\'"], $test['properties']['Browser_Name']) . "',
            'Browser_Type'            => '" . str_replace(['\\', "'"], ['\\\\', "\\'"], $test['properties']['Browser_Type']) . "',
            'Browser_Bits'            => " . str_replace(['\\', "'"], ['\\\\', "\\'"], $test['properties']['Browser_Bits']) . ",
            'Browser_Maker'           => '" . str_replace(['\\', "'"], ['\\\\', "\\'"], $test['properties']['Browser_Maker']) . "',
            'Browser_Modus'           => '" . str_replace(['\\', "'"], ['\\\\', "\\'"], $test['properties']['Browser_Modus']) . "',
            'Browser_Version'         => '" . str_replace(['\\', "'"], ['\\\\', "\\'"], $test['properties']['Browser_Version']) . "',
            'Platform_Codename'       => '" . str_replace(['\\', "'"], ['\\\\', "\\'"], $platformCodename) . "',
            'Platform_Marketingname'  => '" . str_replace(['\\', "'"], ['\\\\', "\\'"], $platformMarketingname) . "',
            'Platform_Version'        => '" . str_replace(['\\', "'"], ['\\\\', "\\'"], $platformVersion) . "',
            'Platform_Bits'           => " . str_replace(['\\', "'"], ['\\\\', "\\'"], $platformBits) . ",
            'Platform_Maker'          => '" . str_replace(['\\', "'"], ['\\\\', "\\'"], $platformMaker) . "',
            'Platform_Brand_Name'     => '" . str_replace(['\\', "'"], ['\\\\', "\\'"], $platformBrandname) . "',
            'Device_Name'             => '" . str_replace(['\\', "'"], ['\\\\', "\\'"], $deviceName) . "',
            'Device_Maker'            => '" . str_replace(['\\', "'"], ['\\\\', "\\'"], $deviceMaker) . "',
            'Device_Type'             => " . ($deviceType === null ? 'null' : "'" . str_replace(['\\', "'"], ['\\\\', "\\'"], $deviceType) . "'") . ",
            'Device_Pointing_Method'  => " . ($devicePointing === null ? 'null' : "'" . str_replace(['\\', "'"], ['\\\\', "\\'"], $devicePointing) . "'") . ",
            'Device_Dual_Orientation' => " . ($deviceOrientation === null ? 'null' : ($deviceOrientation ? 'true' : 'false')) . ",
            'Device_Code_Name'        => '" . str_replace(['\\', "'"], ['\\\\', "\\'"], $deviceCode) . "',
            'Device_Brand_Name'       => '" . str_replace(['\\', "'"], ['\\\\', "\\'"], $deviceBrand) . "',
            'RenderingEngine_Name'    => '" . str_replace(['\\', "'"], ['\\\\', "\\'"], $test['properties']['RenderingEngine_Name']) . "',
            'RenderingEngine_Version' => '" . str_replace(['\\', "'"], ['\\\\', "\\'"], $test['properties']['RenderingEngine_Version']) . "',
            'RenderingEngine_Maker'   => '" . str_replace(['\\', "'"], ['\\\\', "\\'"], $test['properties']['RenderingEngine_Maker']) . "',
        ],
    ],\n";
}

function rewritePlatforms(array $test, \BrowserDetector\BrowserDetector $detector, $key)
{
    if (isset($test['properties']['Platform_Codename'])) {
        $platformCodename = $test['properties']['Platform_Codename'];
    } elseif (isset($test['properties']['Platform_Name'])) {
        $platformCodename = $test['properties']['Platform_Name'];
    } else {
        echo '["' . $key . '"] platform name for UA "' . $test['ua'] . '" is missing, using "unknown" instead', PHP_EOL;

        $platformCodename = 'unknown';
    }

    if (isset($test['properties']['Platform_Marketingname'])) {
        $platformMarketingname = $test['properties']['Platform_Marketingname'];
    } else {
        $platformMarketingname = $platformCodename;
    }

    if (isset($test['properties']['Platform_Version'])) {
        $platformVersion = $test['properties']['Platform_Version'];
    } else {
        echo '["' . $key . '"] platform version for UA "' . $test['ua'] . '" is missing, using "unknown" instead', PHP_EOL;

        $platformVersion = 'unknown';
    }

    if (isset($test['properties']['Platform_Bits'])) {
        $platformBits = $test['properties']['Platform_Bits'];
    } else {
        echo '["' . $key . '"] platform bits for UA "' . $test['ua'] . '" are missing, using "unknown" instead', PHP_EOL;

        $platformBits = 'unknown';
    }

    if (isset($test['properties']['Platform_Maker'])) {
        $platformMaker = $test['properties']['Platform_Maker'];
    } else {
        echo '["' . $key . '"] platform maker for UA "' . $test['ua'] . '" is missing, using "unknown" instead', PHP_EOL;

        $platformMaker = 'unknown';
    }

    if (isset($test['properties']['Platform_Brand_Name'])) {
        $platformBrandname = $test['properties']['Platform_Brand_Name'];
    } else {
        echo '["' . $key . '"] platform brand for UA "' . $test['ua'] . '" is missing, using "unknown" instead', PHP_EOL;

        $platformBrandname = 'unknown';
    }

    // rewrite Darwin platform
    if ('Darwin' === $platformCodename) {
        $platform = \BrowserDetector\Detector\Factory\Platform\DarwinFactory::detect($test['ua']);

        $platformCodename      = $platform->getName();
        $platformMarketingname = $platform->getMarketingName();
        $platformVersion       = $platform->getVersion()->getVersion();
        $platformBits          = $platform->getBits();
        $platformMaker         = $platform->getManufacturer();
        $platformBrandname     = $platform->getBrand();
    } elseif ('Windows' === $platformCodename) {
        $platform = \BrowserDetector\Detector\Factory\Platform\WindowsFactory::detect($test['ua']);

        $platformCodename      = $platform->getName();
        $platformMarketingname = $platform->getMarketingName();
        $platformVersion       = $platform->getVersion()->getVersion();
        $platformBits          = $platform->getBits();
        $platformMaker         = $platform->getManufacturer();
        $platformBrandname     = $platform->getBrand();
    } elseif ('Android' === $platformCodename && preg_match('/windows phone/i', $test['ua'])) {
        $platform = new \BrowserDetector\Detector\Os\WindowsPhoneOs($test['ua']);

        $platformCodename      = $platform->getName();
        $platformMarketingname = $platform->getMarketingName();
        $platformVersion       = $platform->getVersion()->getVersion();
        $platformBits          = $platform->getBits();
        $platformMaker         = $platform->getManufacturer();
        $platformBrandname     = $platform->getBrand();
    } elseif ('Windows' === $platformCodename && preg_match('/wpdesktop/i', $test['ua'])) {
        $platform = new \BrowserDetector\Detector\Os\WindowsPhoneOs($test['ua']);

        $platformCodename      = $platform->getName();
        $platformMarketingname = $platform->getMarketingName();
        $platformVersion       = $platform->getVersion()->getVersion();
        $platformBits          = $platform->getBits();
        $platformMaker         = $platform->getManufacturer();
        $platformBrandname     = $platform->getBrand();
    } elseif ('Linux' === $platformCodename && preg_match('/Puffin\/[\d\.]+I(T|P)/', $test['ua'])) {
        $platform = new \BrowserDetector\Detector\Os\Ios($test['ua']);

        $platformCodename      = $platform->getName();
        $platformMarketingname = $platform->getMarketingName();
        $platformVersion       = $platform->getVersion()->getVersion();
        $platformBits          = $platform->getBits();
        $platformMaker         = $platform->getManufacturer();
        $platformBrandname     = $platform->getBrand();
    } elseif ('Linux' === $platformCodename && preg_match('/Puffin\/[\d\.]+A(T|P)/', $test['ua'])) {
        $platform = new \BrowserDetector\Detector\Os\AndroidOs($test['ua']);

        $platformCodename      = $platform->getName();
        $platformMarketingname = $platform->getMarketingName();
        $platformVersion       = $platform->getVersion()->getVersion();
        $platformBits          = $platform->getBits();
        $platformMaker         = $platform->getManufacturer();
        $platformBrandname     = $platform->getBrand();
    } elseif ('Linux' === $platformCodename && preg_match('/Puffin\/[\d\.]+W(T|P)/', $test['ua'])) {
        $platform = new \BrowserDetector\Detector\Os\WindowsPhoneOs($test['ua']);

        $platformCodename      = $platform->getName();
        $platformMarketingname = $platform->getMarketingName();
        $platformVersion       = $platform->getVersion()->getVersion();
        $platformBits          = $platform->getBits();
        $platformMaker         = $platform->getManufacturer();
        $platformBrandname     = $platform->getBrand();
    } elseif ('Linux' === $platformCodename && preg_match('/kubuntu/i', $test['ua'])) {
        $platform = new \BrowserDetector\Detector\Os\Kubuntu($test['ua']);

        $platformCodename      = $platform->getName();
        $platformMarketingname = $platform->getMarketingName();
        $platformVersion       = $platform->getVersion()->getVersion();
        $platformBits          = $platform->getBits();
        $platformMaker         = $platform->getManufacturer();
        $platformBrandname     = $platform->getBrand();
    } elseif ('Linux' === $platformCodename && preg_match('/ubuntu/i', $test['ua'])) {
        $platform = new \BrowserDetector\Detector\Os\Ubuntu($test['ua']);

        $platformCodename      = $platform->getName();
        $platformMarketingname = $platform->getMarketingName();
        $platformVersion       = $platform->getVersion()->getVersion();
        $platformBits          = $platform->getBits();
        $platformMaker         = $platform->getManufacturer();
        $platformBrandname     = $platform->getBrand();
    } elseif ('Linux Smartphone OS (Maemo)' === $platformCodename && preg_match('/ubuntu/i', $test['ua'])) {
        $platform = new \BrowserDetector\Detector\Os\Ubuntu($test['ua']);

        $platformCodename      = $platform->getName();
        $platformMarketingname = $platform->getMarketingName();
        $platformVersion       = $platform->getVersion()->getVersion();
        $platformBits          = $platform->getBits();
        $platformMaker         = $platform->getManufacturer();
        $platformBrandname     = $platform->getBrand();
    } elseif ('Linux' === $platformCodename && preg_match('/linux arm/i', $test['ua'])) {
        $platform = new \BrowserDetector\Detector\Os\Maemo($test['ua']);

        $platformCodename      = $platform->getName();
        $platformMarketingname = $platform->getMarketingName();
        $platformVersion       = $platform->getVersion()->getVersion();
        $platformBits          = $platform->getBits();
        $platformMaker         = $platform->getManufacturer();
        $platformBrandname     = $platform->getBrand();
    } elseif ('Linux' === $platformCodename && preg_match('/HP\-UX/', $test['ua'])) {
        $platform = new \BrowserDetector\Detector\Os\Hpux($test['ua']);

        $platformCodename      = $platform->getName();
        $platformMarketingname = $platform->getMarketingName();
        $platformVersion       = $platform->getVersion()->getVersion();
        $platformBits          = $platform->getBits();
        $platformMaker         = $platform->getManufacturer();
        $platformBrandname     = $platform->getBrand();
    } elseif ('Windows' === $platformCodename && preg_match('/windows ce/i', $test['ua'])) {
        $platform = new \BrowserDetector\Detector\Os\WindowsCe($test['ua']);

        $platformCodename      = $platform->getName();
        $platformMarketingname = $platform->getMarketingName();
        $platformVersion       = $platform->getVersion()->getVersion();
        $platformBits          = $platform->getBits();
        $platformMaker         = $platform->getManufacturer();
        $platformBrandname     = $platform->getBrand();
    } elseif ('Linux' === $platformCodename && preg_match('/(red hat|redhat)/i', $test['ua'])) {
        $platform = new \BrowserDetector\Detector\Os\Redhat($test['ua']);

        $platformCodename      = $platform->getName();
        $platformMarketingname = $platform->getMarketingName();
        $platformVersion       = $platform->getVersion()->getVersion();
        $platformBits          = $platform->getBits();
        $platformMaker         = $platform->getManufacturer();
        $platformBrandname     = $platform->getBrand();
    } elseif ('Windows Mobile OS' === $platformCodename && preg_match('/Windows Mobile; WCE/', $test['ua'])) {
        $platform = new \BrowserDetector\Detector\Os\WindowsCe($test['ua']);

        $platformCodename      = $platform->getName();
        $platformMarketingname = $platform->getMarketingName();
        $platformVersion       = $platform->getVersion()->getVersion();
        $platformBits          = $platform->getBits();
        $platformMaker         = $platform->getManufacturer();
        $platformBrandname     = $platform->getBrand();
    } elseif ('Linux' === $platformCodename && preg_match('/SUSE/', $test['ua'])) {
        $platform = new \BrowserDetector\Detector\Os\Suse($test['ua']);

        $platformCodename      = $platform->getName();
        $platformMarketingname = $platform->getMarketingName();
        $platformVersion       = $platform->getVersion()->getVersion();
        $platformBits          = $platform->getBits();
        $platformMaker         = $platform->getManufacturer();
        $platformBrandname     = $platform->getBrand();
    } elseif ('Linux' === $platformCodename && preg_match('/centos/i', $test['ua'])) {
        $platform = new \BrowserDetector\Detector\Os\CentOs($test['ua']);

        $platformCodename      = $platform->getName();
        $platformMarketingname = $platform->getMarketingName();
        $platformVersion       = $platform->getVersion()->getVersion();
        $platformBits          = $platform->getBits();
        $platformMaker         = $platform->getManufacturer();
        $platformBrandname     = $platform->getBrand();
    } elseif ('Linux' === $platformCodename && preg_match('/mint/i', $test['ua'])) {
        $platform = new \BrowserDetector\Detector\Os\Mint($test['ua']);

        $platformCodename      = $platform->getName();
        $platformMarketingname = $platform->getMarketingName();
        $platformVersion       = $platform->getVersion()->getVersion();
        $platformBits          = $platform->getBits();
        $platformMaker         = $platform->getManufacturer();
        $platformBrandname     = $platform->getBrand();
    } elseif (false !== strpos($test['ua'], 'Windows Phone')) {
        $platform = new \BrowserDetector\Detector\Os\WindowsPhoneOs($test['ua']);

        $platformCodename      = $platform->getName();
        $platformMarketingname = $platform->getMarketingName();
        $platformVersion       = $platform->getVersion()->getVersion();
        $platformBits          = $platform->getBits();
        $platformMaker         = $platform->getManufacturer();
        $platformBrandname     = $platform->getBrand();
    } elseif (false !== strpos($test['ua'], 'wds')) {
        $platform = new \BrowserDetector\Detector\Os\WindowsPhoneOs($test['ua']);

        $platformCodename      = $platform->getName();
        $platformMarketingname = $platform->getMarketingName();
        $platformVersion       = $platform->getVersion()->getVersion();
        $platformBits          = $platform->getBits();
        $platformMaker         = $platform->getManufacturer();
        $platformBrandname     = $platform->getBrand();
    } elseif (false !== stripos($test['ua'], 'wpdesktop')) {
        $platform = new \BrowserDetector\Detector\Os\WindowsPhoneOs($test['ua']);

        $platformCodename      = $platform->getName();
        $platformMarketingname = $platform->getMarketingName();
        $platformVersion       = $platform->getVersion()->getVersion();
        $platformBits          = $platform->getBits();
        $platformMaker         = $platform->getManufacturer();
        $platformBrandname     = $platform->getBrand();
    } elseif (false !== strpos($test['ua'], 'Tizen')) {
        $platform = new \BrowserDetector\Detector\Os\Tizen($test['ua']);

        $platformCodename      = $platform->getName();
        $platformMarketingname = $platform->getMarketingName();
        $platformVersion       = $platform->getVersion()->getVersion();
        $platformBits          = $platform->getBits();
        $platformMaker         = $platform->getManufacturer();
        $platformBrandname     = $platform->getBrand();
    } elseif (false !== strpos($test['ua'], 'Windows CE')) {
        $platform = new \BrowserDetector\Detector\Os\WindowsCe($test['ua']);

        $platformCodename      = $platform->getName();
        $platformMarketingname = $platform->getMarketingName();
        $platformVersion       = $platform->getVersion()->getVersion();
        $platformBits          = $platform->getBits();
        $platformMaker         = $platform->getManufacturer();
        $platformBrandname     = $platform->getBrand();
    } elseif (false !== strpos($test['ua'], 'Linux; Android')) {
        $platform = new \BrowserDetector\Detector\Os\AndroidOs($test['ua']);

        $platformCodename      = $platform->getName();
        $platformMarketingname = $platform->getMarketingName();
        $platformVersion       = $platform->getVersion()->getVersion();
        $platformBits          = $platform->getBits();
        $platformMaker         = $platform->getManufacturer();
        $platformBrandname     = $platform->getBrand();
    } elseif (false !== strpos($test['ua'], 'Linux; U; Android')) {
        $platform = new \BrowserDetector\Detector\Os\AndroidOs($test['ua']);

        $platformCodename      = $platform->getName();
        $platformMarketingname = $platform->getMarketingName();
        $platformVersion       = $platform->getVersion()->getVersion();
        $platformBits          = $platform->getBits();
        $platformMaker         = $platform->getManufacturer();
        $platformBrandname     = $platform->getBrand();
    } elseif (false !== strpos($test['ua'], 'U; Adr')) {
        $platform = new \BrowserDetector\Detector\Os\AndroidOs($test['ua']);

        $platformCodename      = $platform->getName();
        $platformMarketingname = $platform->getMarketingName();
        $platformVersion       = $platform->getVersion()->getVersion();
        $platformBits          = $platform->getBits();
        $platformMaker         = $platform->getManufacturer();
        $platformBrandname     = $platform->getBrand();
    } elseif (false !== strpos($test['ua'], 'Android') || false !== strpos($test['ua'], 'MTK')) {
        $platform = new \BrowserDetector\Detector\Os\AndroidOs($test['ua']);

        $platformCodename      = $platform->getName();
        $platformMarketingname = $platform->getMarketingName();
        $platformVersion       = $platform->getVersion()->getVersion();
        $platformBits          = $platform->getBits();
        $platformMaker         = $platform->getManufacturer();
        $platformBrandname     = $platform->getBrand();
    } elseif (false !== strpos($test['ua'], 'OpenBSD')) {
        $platform = new \BrowserDetector\Detector\Os\OpenBsd($test['ua']);

        $platformCodename      = $platform->getName();
        $platformMarketingname = $platform->getMarketingName();
        $platformVersion       = $platform->getVersion()->getVersion();
        $platformBits          = $platform->getBits();
        $platformMaker         = $platform->getManufacturer();
        $platformBrandname     = $platform->getBrand();
    } elseif (false !== strpos($test['ua'], 'Symbian') || false !== strpos($test['ua'], 'Series 60')) {
        $platform = new \BrowserDetector\Detector\Os\Symbianos($test['ua']);

        $platformCodename      = $platform->getName();
        $platformMarketingname = $platform->getMarketingName();
        $platformVersion       = $platform->getVersion()->getVersion();
        $platformBits          = $platform->getBits();
        $platformMaker         = $platform->getManufacturer();
        $platformBrandname     = $platform->getBrand();
    } elseif (false !== strpos($test['ua'], 'MIDP')) {
        $platform = new \BrowserDetector\Detector\Os\Java($test['ua']);

        $platformCodename      = $platform->getName();
        $platformMarketingname = $platform->getMarketingName();
        $platformVersion       = $platform->getVersion()->getVersion();
        $platformBits          = $platform->getBits();
        $platformMaker         = $platform->getManufacturer();
        $platformBrandname     = $platform->getBrand();
    } elseif (false !== strpos($test['ua'], 'Windows NT 10.0')) {
        $platform = new \BrowserDetector\Detector\Os\Windows10($test['ua'], '10.0');

        $platformCodename      = $platform->getName();
        $platformMarketingname = $platform->getMarketingName();
        $platformVersion       = $platform->getVersion()->getVersion();
        $platformBits          = $platform->getBits();
        $platformMaker         = $platform->getManufacturer();
        $platformBrandname     = $platform->getBrand();
    } elseif (false !== strpos($test['ua'], 'Windows NT 6.4')) {
        $platform = new \BrowserDetector\Detector\Os\Windows10($test['ua'], '6.4');

        $platformCodename      = $platform->getName();
        $platformMarketingname = $platform->getMarketingName();
        $platformVersion       = $platform->getVersion()->getVersion();
        $platformBits          = $platform->getBits();
        $platformMaker         = $platform->getManufacturer();
        $platformBrandname     = $platform->getBrand();
    } elseif (false !== strpos($test['ua'], 'Windows NT 6.3') && false !== strpos($test['ua'], 'ARM')) {
        $platform = new \BrowserDetector\Detector\Os\WindowsRt($test['ua']);

        $platformCodename      = $platform->getName();
        $platformMarketingname = $platform->getMarketingName();
        $platformVersion       = $platform->getVersion()->getVersion();
        $platformBits          = $platform->getBits();
        $platformMaker         = $platform->getManufacturer();
        $platformBrandname     = $platform->getBrand();
    } elseif (false !== strpos($test['ua'], 'Windows NT 6.3')) {
        $platform = new \BrowserDetector\Detector\Os\Windows81($test['ua']);

        $platformCodename      = $platform->getName();
        $platformMarketingname = $platform->getMarketingName();
        $platformVersion       = $platform->getVersion()->getVersion();
        $platformBits          = $platform->getBits();
        $platformMaker         = $platform->getManufacturer();
        $platformBrandname     = $platform->getBrand();
    } elseif (false !== strpos($test['ua'], 'Windows NT 6.2') && false !== strpos($test['ua'], 'ARM')) {
        $platform = new \BrowserDetector\Detector\Os\WindowsRt($test['ua']);

        $platformCodename      = $platform->getName();
        $platformMarketingname = $platform->getMarketingName();
        $platformVersion       = $platform->getVersion()->getVersion();
        $platformBits          = $platform->getBits();
        $platformMaker         = $platform->getManufacturer();
        $platformBrandname     = $platform->getBrand();
    } elseif (false !== strpos($test['ua'], 'Windows NT 6.2')) {
        $platform = new \BrowserDetector\Detector\Os\Windows8($test['ua']);

        $platformCodename      = $platform->getName();
        $platformMarketingname = $platform->getMarketingName();
        $platformVersion       = $platform->getVersion()->getVersion();
        $platformBits          = $platform->getBits();
        $platformMaker         = $platform->getManufacturer();
        $platformBrandname     = $platform->getBrand();
    } elseif (false !== strpos($test['ua'], 'Windows NT 6.1')) {
        $platform = new \BrowserDetector\Detector\Os\Windows7($test['ua']);

        $platformCodename      = $platform->getName();
        $platformMarketingname = $platform->getMarketingName();
        $platformVersion       = $platform->getVersion()->getVersion();
        $platformBits          = $platform->getBits();
        $platformMaker         = $platform->getManufacturer();
        $platformBrandname     = $platform->getBrand();
    } elseif (false !== strpos($test['ua'], 'Windows NT 6.0')) {
        $platform = new \BrowserDetector\Detector\Os\WindowsVista($test['ua']);

        $platformCodename      = $platform->getName();
        $platformMarketingname = $platform->getMarketingName();
        $platformVersion       = $platform->getVersion()->getVersion();
        $platformBits          = $platform->getBits();
        $platformMaker         = $platform->getManufacturer();
        $platformBrandname     = $platform->getBrand();
    } elseif (false !== strpos($test['ua'], 'Windows NT 5.3')) {
        $platform = new \BrowserDetector\Detector\Os\WindowsXp($test['ua'], '5.3');

        $platformCodename      = $platform->getName();
        $platformMarketingname = $platform->getMarketingName();
        $platformVersion       = $platform->getVersion()->getVersion();
        $platformBits          = $platform->getBits();
        $platformMaker         = $platform->getManufacturer();
        $platformBrandname     = $platform->getBrand();
    } elseif (false !== strpos($test['ua'], 'Windows NT 5.2')) {
        $platform = new \BrowserDetector\Detector\Os\WindowsXp($test['ua'], '5.2');

        $platformCodename      = $platform->getName();
        $platformMarketingname = $platform->getMarketingName();
        $platformVersion       = $platform->getVersion()->getVersion();
        $platformBits          = $platform->getBits();
        $platformMaker         = $platform->getManufacturer();
        $platformBrandname     = $platform->getBrand();
    } elseif (false !== strpos($test['ua'], 'Windows NT 5.1')) {
        $platform = new \BrowserDetector\Detector\Os\WindowsXp($test['ua'], '5.1');

        $platformCodename      = $platform->getName();
        $platformMarketingname = $platform->getMarketingName();
        $platformVersion       = $platform->getVersion()->getVersion();
        $platformBits          = $platform->getBits();
        $platformMaker         = $platform->getManufacturer();
        $platformBrandname     = $platform->getBrand();
    } elseif (false !== strpos($test['ua'], 'Windows NT 5.01')) {
        $platform = new \BrowserDetector\Detector\Os\Windows2000($test['ua'], '5.01');

        $platformCodename      = $platform->getName();
        $platformMarketingname = $platform->getMarketingName();
        $platformVersion       = $platform->getVersion()->getVersion();
        $platformBits          = $platform->getBits();
        $platformMaker         = $platform->getManufacturer();
        $platformBrandname     = $platform->getBrand();
    } elseif (false !== strpos($test['ua'], 'Windows NT 5.0')) {
        $platform = new \BrowserDetector\Detector\Os\Windows2000($test['ua'], '5.0');

        $platformCodename      = $platform->getName();
        $platformMarketingname = $platform->getMarketingName();
        $platformVersion       = $platform->getVersion()->getVersion();
        $platformBits          = $platform->getBits();
        $platformMaker         = $platform->getManufacturer();
        $platformBrandname     = $platform->getBrand();
    } elseif (false !== strpos($test['ua'], 'Windows NT 4.1')) {
        $platform = new \BrowserDetector\Detector\Os\WindowsNt($test['ua'], '4.1');

        $platformCodename      = $platform->getName();
        $platformMarketingname = $platform->getMarketingName();
        $platformVersion       = $platform->getVersion()->getVersion();
        $platformBits          = $platform->getBits();
        $platformMaker         = $platform->getManufacturer();
        $platformBrandname     = $platform->getBrand();
    } elseif (false !== strpos($test['ua'], 'Windows NT 4.0')) {
        $platform = new \BrowserDetector\Detector\Os\WindowsNt($test['ua'], '4.0');

        $platformCodename      = $platform->getName();
        $platformMarketingname = $platform->getMarketingName();
        $platformVersion       = $platform->getVersion()->getVersion();
        $platformBits          = $platform->getBits();
        $platformMaker         = $platform->getManufacturer();
        $platformBrandname     = $platform->getBrand();
    } elseif (false !== strpos($test['ua'], 'Windows NT 3.5')) {
        $platform = new \BrowserDetector\Detector\Os\WindowsNt($test['ua'], '3.5');

        $platformCodename      = $platform->getName();
        $platformMarketingname = $platform->getMarketingName();
        $platformVersion       = $platform->getVersion()->getVersion();
        $platformBits          = $platform->getBits();
        $platformMaker         = $platform->getManufacturer();
        $platformBrandname     = $platform->getBrand();
    } elseif (false !== strpos($test['ua'], 'Windows NT 3.1')) {
        $platform = new \BrowserDetector\Detector\Os\WindowsNt($test['ua'], '3.1');

        $platformCodename      = $platform->getName();
        $platformMarketingname = $platform->getMarketingName();
        $platformVersion       = $platform->getVersion()->getVersion();
        $platformBits          = $platform->getBits();
        $platformMaker         = $platform->getManufacturer();
        $platformBrandname     = $platform->getBrand();
    } elseif (false !== strpos($test['ua'], 'Windows NT')) {
        $platform = new \BrowserDetector\Detector\Os\WindowsNt($test['ua'], '0.0');

        $platformCodename      = $platform->getName();
        $platformMarketingname = $platform->getMarketingName();
        $platformVersion       = $platform->getVersion()->getVersion();
        $platformBits          = $platform->getBits();
        $platformMaker         = $platform->getManufacturer();
        $platformBrandname     = $platform->getBrand();
    } elseif (false !== stripos($test['ua'], 'cygwin')) {
        $platform = new \BrowserDetector\Detector\Os\Cygwin($test['ua']);

        $platformCodename      = $platform->getName();
        $platformMarketingname = $platform->getMarketingName();
        $platformVersion       = $platform->getVersion()->getVersion();
        $platformBits          = $platform->getBits();
        $platformMaker         = $platform->getManufacturer();
        $platformBrandname     = $platform->getBrand();
    } elseif (false !== strpos($test['ua'], 'CPU OS')) {
        $platform = new \BrowserDetector\Detector\Os\Ios($test['ua']);

        $platformCodename      = $platform->getName();
        $platformMarketingname = $platform->getMarketingName();
        $platformVersion       = $platform->getVersion()->getVersion();
        $platformBits          = $platform->getBits();
        $platformMaker         = $platform->getManufacturer();
        $platformBrandname     = $platform->getBrand();
    } elseif (false !== strpos($test['ua'], 'CPU iPhone OS')) {
        $platform = new \BrowserDetector\Detector\Os\Ios($test['ua']);

        $platformCodename      = $platform->getName();
        $platformMarketingname = $platform->getMarketingName();
        $platformVersion       = $platform->getVersion()->getVersion();
        $platformBits          = $platform->getBits();
        $platformMaker         = $platform->getManufacturer();
        $platformBrandname     = $platform->getBrand();
    } elseif (false !== strpos($test['ua'], 'CPU like Mac OS X')) {
        $platform = new \BrowserDetector\Detector\Os\Ios($test['ua']);

        $platformCodename      = $platform->getName();
        $platformMarketingname = $platform->getMarketingName();
        $platformVersion       = $platform->getVersion()->getVersion();
        $platformBits          = $platform->getBits();
        $platformMaker         = $platform->getManufacturer();
        $platformBrandname     = $platform->getBrand();
    } elseif (false !== strpos($test['ua'], 'iOS')) {
        $platform = new \BrowserDetector\Detector\Os\Ios($test['ua']);

        $platformCodename      = $platform->getName();
        $platformMarketingname = $platform->getMarketingName();
        $platformVersion       = $platform->getVersion()->getVersion();
        $platformBits          = $platform->getBits();
        $platformMaker         = $platform->getManufacturer();
        $platformBrandname     = $platform->getBrand();
    } elseif (false !== strpos($test['ua'], 'Mac OS X')) {
        $platform = new \BrowserDetector\Detector\Os\Macosx($test['ua']);

        $platformCodename      = $platform->getName();
        $platformMarketingname = $platform->getMarketingName();
        $platformVersion       = $platform->getVersion()->getVersion();
        $platformBits          = $platform->getBits();
        $platformMaker         = $platform->getManufacturer();
        $platformBrandname     = $platform->getBrand();
    } elseif (false !== strpos($test['ua'], 'hpwOS')) {
        $platform = new \BrowserDetector\Detector\Os\WebOs($test['ua']);

        $platformCodename      = $platform->getName();
        $platformMarketingname = $platform->getMarketingName();
        $platformVersion       = $platform->getVersion()->getVersion();
        $platformBits          = $platform->getBits();
        $platformMaker         = $platform->getManufacturer();
        $platformBrandname     = $platform->getBrand();
    } elseif (false !== stripos($test['ua'], 'kubuntu')) {
        $platform = new \BrowserDetector\Detector\Os\Kubuntu($test['ua']);

        $platformCodename      = $platform->getName();
        $platformMarketingname = $platform->getMarketingName();
        $platformVersion       = $platform->getVersion()->getVersion();
        $platformBits          = $platform->getBits();
        $platformMaker         = $platform->getManufacturer();
        $platformBrandname     = $platform->getBrand();
    } elseif (false !== stripos($test['ua'], 'ubuntu')) {
        $platform = new \BrowserDetector\Detector\Os\Ubuntu($test['ua']);

        $platformCodename      = $platform->getName();
        $platformMarketingname = $platform->getMarketingName();
        $platformVersion       = $platform->getVersion()->getVersion();
        $platformBits          = $platform->getBits();
        $platformMaker         = $platform->getManufacturer();
        $platformBrandname     = $platform->getBrand();
    } elseif (false !== stripos($test['ua'], 'fedora')) {
        $platform = new \BrowserDetector\Detector\Os\Fedora($test['ua']);

        $platformCodename      = $platform->getName();
        $platformMarketingname = $platform->getMarketingName();
        $platformVersion       = $platform->getVersion()->getVersion();
        $platformBits          = $platform->getBits();
        $platformMaker         = $platform->getManufacturer();
        $platformBrandname     = $platform->getBrand();
    } elseif (false !== stripos($test['ua'], 'suse')) {
        $platform = new \BrowserDetector\Detector\Os\Suse($test['ua']);

        $platformCodename      = $platform->getName();
        $platformMarketingname = $platform->getMarketingName();
        $platformVersion       = $platform->getVersion()->getVersion();
        $platformBits          = $platform->getBits();
        $platformMaker         = $platform->getManufacturer();
        $platformBrandname     = $platform->getBrand();
    } elseif (false !== stripos($test['ua'], 'mandriva')) {
        $platform = new \BrowserDetector\Detector\Os\Mandriva($test['ua']);

        $platformCodename      = $platform->getName();
        $platformMarketingname = $platform->getMarketingName();
        $platformVersion       = $platform->getVersion()->getVersion();
        $platformBits          = $platform->getBits();
        $platformMaker         = $platform->getManufacturer();
        $platformBrandname     = $platform->getBrand();
    } elseif (false !== stripos($test['ua'], 'gentoo')) {
        $platform = new \BrowserDetector\Detector\Os\Gentoo($test['ua']);

        $platformCodename      = $platform->getName();
        $platformMarketingname = $platform->getMarketingName();
        $platformVersion       = $platform->getVersion()->getVersion();
        $platformBits          = $platform->getBits();
        $platformMaker         = $platform->getManufacturer();
        $platformBrandname     = $platform->getBrand();
    } elseif (false !== stripos($test['ua'], 'slackware')) {
        $platform = new \BrowserDetector\Detector\Os\Slackware($test['ua']);

        $platformCodename      = $platform->getName();
        $platformMarketingname = $platform->getMarketingName();
        $platformVersion       = $platform->getVersion()->getVersion();
        $platformBits          = $platform->getBits();
        $platformMaker         = $platform->getManufacturer();
        $platformBrandname     = $platform->getBrand();
    } elseif (false !== strpos($test['ua'], 'CrOS')) {
        $platform = new \BrowserDetector\Detector\Os\CrOs($test['ua']);

        $platformCodename      = $platform->getName();
        $platformMarketingname = $platform->getMarketingName();
        $platformVersion       = $platform->getVersion()->getVersion();
        $platformBits          = $platform->getBits();
        $platformMaker         = $platform->getManufacturer();
        $platformBrandname     = $platform->getBrand();
    } elseif (false !== strpos($test['ua'], 'Linux')) {
        $platform = new \BrowserDetector\Detector\Os\Linux($test['ua']);

        $platformCodename      = $platform->getName();
        $platformMarketingname = $platform->getMarketingName();
        $platformVersion       = $platform->getVersion()->getVersion();
        $platformBits          = $platform->getBits();
        $platformMaker         = $platform->getManufacturer();
        $platformBrandname     = $platform->getBrand();
    } elseif (false !== strpos($test['ua'], 'SymbOS')) {
        $platform = new \BrowserDetector\Detector\Os\Symbianos($test['ua']);

        $platformCodename      = $platform->getName();
        $platformMarketingname = $platform->getMarketingName();
        $platformVersion       = $platform->getVersion()->getVersion();
        $platformBits          = $platform->getBits();
        $platformMaker         = $platform->getManufacturer();
        $platformBrandname     = $platform->getBrand();
    } else {
        $result = $detector->getBrowser($test['ua']);

        $platform = $result->getOs();

        if ($platformCodename === $platform->getName()) {
            $platformMarketingname = $platform->getMarketingName();
            $platformVersion       = $platform->getVersion()->getVersion();
            $platformBits          = $platform->getBits();
            $platformMaker         = $platform->getManufacturer();
            $platformBrandname     = $platform->getBrand();
        }
    }

    return [$platformCodename, $platformMarketingname, $platformVersion, $platformBits, $platformMaker, $platformBrandname, $platform];
}

function rewriteDevice(array $test, \BrowserDetector\BrowserDetector $detector, \UaResult\Os\OsInterface $platform)
{
    if (isset($test['properties']['Device_Name'])) {
        $deviceName = $test['properties']['Device_Name'];
    } else {
        $deviceName = 'unknown';
    }

    if (isset($test['properties']['Device_Maker'])) {
        $deviceMaker = $test['properties']['Device_Maker'];
    } else {
        $deviceMaker = 'unknown';
    }

    if (isset($test['properties']['Device_Type'])) {
        $deviceType = $test['properties']['Device_Type'];
    } else {
        $deviceType = 'unknown';
    }

    if (isset($test['properties']['Device_Pointing_Method'])) {
        $devicePointing = $test['properties']['Device_Pointing_Method'];
    } else {
        $devicePointing = 'unknown';
    }

    if (isset($test['properties']['Device_Code_Name'])) {
        $deviceCode = $test['properties']['Device_Code_Name'];
    } else {
        $deviceCode = 'unknown';
    }

    if (isset($test['properties']['Device_Brand_Name'])) {
        $deviceBrand = $test['properties']['Device_Brand_Name'];
    } else {
        $deviceBrand = 'unknown';
    }

    if (isset($test['properties']['Device_Dual_Orientation'])) {
        $deviceOrientation = $test['properties']['Device_Dual_Orientation'];
    } else {
        $deviceOrientation = 'unknown';
    }

    if (preg_match('/redmi 3s/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Xiaomi\XiaomiRedmi3s($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/redmi 3/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Xiaomi\XiaomiRedmi3($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/mi max/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Xiaomi\XiaomiMiMax($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/one[_ ]m9plus/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Htc\HtcOneM9plus($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/one[_ ]m9/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Htc\HtcOneM9($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/(one[ _]sv|onesv)/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Htc\HtcOneSv($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/(one[ _]x\+|onexplus)/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Htc\HtcOneXplus($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/one[ _]xl/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Htc\HtcOneXl($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/(one[ _]x|onex|PJ83100)/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Htc\HtcOneX($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/(PC36100|EVO 4G)/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Htc\HtcEvo4g($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/Evo 3D GSM/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Htc\HtcEvo3dGsm($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/HTC T328d/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Htc\HtcT328d($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/desire[ _]500/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Htc\HtcDesire500($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/desire[ _]310/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Htc\HtcDesire310($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/desire[ _]eye/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Htc\HtcDesireEye($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/nexus 9/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Htc\HtcNexus9($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/(rm\-1113|lumia 640 lte)/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Nokia\NokiaLumia640lte($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/rm\-1075/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Nokia\NokiaLumia640lteRm1075($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/rm\-1067/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Nokia\NokiaLumia640xlRm1067($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/rm\-1090/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Nokia\NokiaLumia535Rm1090($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/rm\-994/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Nokia\NokiaLumia1320Rm994($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/rm\-1010/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Nokia\NokiaLumia638($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/lumia 720/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Nokia\NokiaLumia720($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/lumia 521/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Nokia\NokiaLumia521($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/lumia 535/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Nokia\NokiaLumia535($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/lumia 540/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Nokia\NokiaLumia540($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/lumia 1320/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Nokia\NokiaLumia1320($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/lumia 930/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Nokia\NokiaLumia930($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/lumia 640 xl/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Nokia\NokiaLumia640xl($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/genm14/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Nokia\NokiaXl2($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/nokia300/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Nokia\Nokia300($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/Nokia5800d/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Nokia\Nokia5800XpressMusic($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/Nokia5230/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Nokia\Nokia5230($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/NokiaC2\-01/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Nokia\NokiaC201($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/NokiaN8\-00/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Nokia\NokiaN800($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/NokiaN95/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Nokia\NokiaN95($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/L50u/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\SonyEricsson\SonyL50uExperiaZ2lte($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/SonyEricssonS312/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\SonyEricsson\SonyEricssonS312($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/(Xperia Z|C6603)/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\SonyEricsson\SonyC6603ExperiaZ($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/LT26ii/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\SonyEricsson\SonyEricssonLT26ii($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/LT26i/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\SonyEricsson\SonyEricssonLT26i($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/LT26w/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\SonyEricsson\SonyLT26w($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/LT30p/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\SonyEricsson\SonyLT30p($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/ST26i/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\SonyEricsson\SonyST26i($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/D6603/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\SonyEricsson\SonyD6603ExperiaZ3($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/D6503/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\SonyEricsson\SonyD6503ExperiaZ2($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/D2005/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\SonyEricsson\SonyD2005($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/D2203/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\SonyEricsson\SonyD2203($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/C5303/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\SonyEricsson\SonyC5303XperiaSp($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/C6903/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\SonyEricsson\SonyC6903ExperiaZ1($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/C1905/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\SonyEricsson\SonyC1905($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/SGP512/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\SonyEricsson\SonyTabletSgp512($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/SGP521/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\SonyEricsson\SonyTabletSgp521($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/SGP771/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\SonyEricsson\SonyTabletSgp771($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/E5823/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\SonyEricsson\SonyE5823($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/E2303/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\SonyEricsson\SonyE2303($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/E2003/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\SonyEricsson\SonyE2003($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/E6653/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\SonyEricsson\SonyE6653($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/ONEPLUS A3000/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Oneplus\OneplusA3000($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/ONE E1003/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Oneplus\OneplusE1003($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/MZ\-MX5/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Meizu\MeizuMx5($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/SM\-G9006V/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSmG9006v($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/SM\-G900F/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSmG900F($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/SM\-G903F/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSmG903F($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/SM\-G901F/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSmG901F($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/SM\-G925F/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSmG925F($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/SM\-G920V/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSmG920V($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/SM\-G920FD/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSmG920Fd($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/SM\-G920F/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSmG920F($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/SM\-G920S/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSmG920S($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/SM\-G920I/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSmG920I($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/SM\-G920A/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSmG920A($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/SM\-G920T/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSmG920T($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/SM\-G9200/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSmG9200($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/SM\-G9208/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSmG9208($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/SM\-G9209/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSmG9209($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/SM\-G850F/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSmG850F($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/SM\-G870A/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSmG870a($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/SM\-G530H/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSmG530h($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/SM\-G388F/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSmG388F($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/SM\-G360H/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSmG360H($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/SM\-T110/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSmT110($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/SM\-T525/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSmT525($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/SM\-T580/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSmT580($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/SM\-T585/i', $test['ua'])) {

        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSmT585($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/SM\-T810x/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSmT810x($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/SM\-T810/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSmT810($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/SM\-T815y/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSmT815y($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/SM\-T815/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSmT815($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/SM\-T813/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSmT813($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/SM\-T819/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSmT819($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/SM\-T805/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSmT805($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/SM\-T315/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSmT315($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/SM\-C101/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSmC101($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/SM\-N9005/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSmN9005($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/SM\-N9002/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSmN9002($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/SM\-N9008V/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSmN9008V($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/SM\-N9009/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSmN9009($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/SM\-N9007/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSmN9007($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/SM\-N9006/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSmN9006($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/SM\-N900A/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSmN900A($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/SM\-N900V/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSmN900V($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/SM\-N900K/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSmN900K($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/SM\-N900S/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSmN900S($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/SM\-N900T/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSmN900T($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/SM\-N900P/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSmN900P($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/SM\-N900L/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSmN900L($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/SM\-N900W8/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSmN900W8($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/SM\-N900/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSmN900($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/SM\-E500H/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSmE500H($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/SM\-A500FU/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSmA500fu($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/SM\-A500F/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSmA500f($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/SM\-A300FU/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSmA300fu($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/SM\-A300F/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSmA300f($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/SM\-A310F/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSmA310f($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/SM\-A510FD/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSmA510fd($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/SM\-A510F/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSmA510f($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/SM\-A510M/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSmA510m($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/SM\-A510Y/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSmA510y($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/SM\-A5100/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSmA5100($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/SM\-J500FN/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSmJ500fn($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/SM\-J500F/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSmJ500f($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/SM\-J500G/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSmJ500g($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/SM\-J500Y/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSmJ500y($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/SM\-J500M/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSmJ500m($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/SM\-J500H/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSmJ500h($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/SM\-J5007/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSmJ5007($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/SM\-J320g/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSmJ320g($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/SM\-P600/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSmP600($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/Nexus Player/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungGalaxyNexusPlayer($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/NEO\-X5/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Minix\MinixNeoX5($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/vns\-l31/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Huawei\HuaweiVnsL31($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/g750\-u10/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Huawei\HuaweiG750u10($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/g730\-u10/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Huawei\HuaweiG730u10($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/MediaPad 7 Youth/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Huawei\HuaweiMediaPad7Youth($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/PE\-TL10/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Huawei\HuaweiPetl10($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/HUAWEI G6\-L11/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Huawei\HuaweiG6L11($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/HUAWEI P7\-L10/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Huawei\HuaweiP7L10($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/HUAWEI SCL\-L01/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Huawei\HuaweiSclL01($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/EVA\-L09/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Huawei\HuaweiEvaL09($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/mediapad 10 link\+/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Huawei\HuaweiMediaPad10LinkPlus($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/mediapad 10 link/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Huawei\HuaweiMediaPad10Link($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/F5281/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Hisense\HisenseF5281($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/Aquaris M10/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Bq\BqAquarisM10($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/Aquaris M5/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Bq\BqAquarisM5($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/gt\-i9195i/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungGti9195i($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/gt\-i9195/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungGti9195($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/gt\-i9100g/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungGti9100g($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/gt\-i9100p/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungGti9100p($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/gt\-i9100/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungGti9100($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/gt\-i9300/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungGti9300($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/gt\-i9301i/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungGti9301i($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/gt\-i9301q/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungGti9301q($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/gt\-i9301/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungGti9301($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/gt\-i9060i/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungGti9060i($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/gt\-i9060l/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungGti9060l($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/gt\-i9060/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungGti9060($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/gt\-i9505g/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungGti9505g($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/gt\-i9505x/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungGti9505x($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/gt\-i9505/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungGti9505($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/gt\-i5500/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungGti5500($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/gt\-i5700/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungGti5700($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/gt\-i8190n/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungGti8190n($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/gt\-i8190/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungGti8190($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/gt\-i8150/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungGti8150($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/gt\-i8200n/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungGti8200n($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/gt\-i8200/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungGti8200($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/gt\-e3309t/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungGte3309t($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/gt\-e2202/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungGte2202($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/gt\-e2252/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungGte2252($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/gt\-b7722/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungGtb7722($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/gt\-s7262/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungGts7262($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/gt\-s7275r/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungGts7275r($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/gt\-s7500/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungGts7500($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/gt\-s3802/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungGts3802($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/gt\-s3653/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungGts3653($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/gt\-s5620/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungGts5620($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/gt\-s5301L/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungGts5301l($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/gt\-s5301/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungGts5301($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/gt\-s6810b/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungGts6810b($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/gt\-s6810p/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungGts6810p($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/gt\-s6810/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungGts6810($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/gt\-c6712/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungGtc6712($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/gt\-c3262/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungGtc3262($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/gt\-c3322/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungGtc3322($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/gt\-p5110/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungGtp5110($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/gt\-p7510/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungGtp7510($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/gt\-n7100/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungGtn7100($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/gt\-n7105/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungGtn7105($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/gt\-n5110/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungGtn5110($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/gt\-n8010/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungGtn8010($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/sgh\-e250i/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSghE250i($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/sgh\-e250/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSghE250($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/sgh\-t528g/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSght528g($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/sgh\-t989d/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSghT989d($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/sgh\-t989/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSghT989($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/sgh\-i257/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSghi257($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/sgh\-m919/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSghm919($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/sch\-r970/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSchr970($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/shv\-e210l/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungShvE210l($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/shv\-e210k/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungShvE210k($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/nexus 10/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungGalaxyNexus10($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/Slate 17/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Hp\HpSlate17($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/H345/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Lg\LgH345($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/H320/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Lg\LgH320($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/H850/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Lg\LgH850($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/LG\-D802TR/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Lg\Lgd802tr($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/LG\-D802/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Lg\Lgd802($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/LG\-D855/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Lg\Lgd855($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/LG\-D320/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Lg\Lgd320($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/LG\-D373/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Lg\Lgd373($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/LG\-D290/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Lg\Lgd290($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/LG\-D955/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Lg\Lgd955($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/LG\-D686/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Lg\LgD686($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/LG\-F240K/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Lg\LgF240k($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/LG\-V935/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Lg\Lgv935($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/LG\-X150/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Lg\Lgx150($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/nexus 5x/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Lg\LgNexus5x($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/nexus 5/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Lg\LgNexus5($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/nexus 4/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Lg\LgNexus4($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/LIFETAB_E10316/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Medion\MdLifetabE10316($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/LIFETAB_E10312/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Medion\MdLifetabE10312($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/LIFETAB_E10320/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Medion\MdLifetabE10320($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/LIFETAB_E10310/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Medion\MdLifetabE10310($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/LIFETAB_E7312/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Medion\MdLifetabE7312($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/LIFETAB_P733X/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Medion\MdLifetabP733x($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/LIFETAB_P1034X/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Medion\MdLifetabP1034x($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/LIFETAB_S1034X/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Medion\MdLifetabS1034x($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/P4501/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Medion\MdLifeP4501($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/YUANDA50/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Yuanda\Yuanda50($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/Fly IQ4415/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Fly\FlyIq4415($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/Fly IQ449/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Fly\FlyIq449($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/(CX919|gxt_dongle_3188)/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Tv\AndoerCx919($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/PAP5000TDUO/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Prestigio\PrestigioPap5000tDuo($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/PAP5000DUO/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Prestigio\PrestigioPap5000Duo($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/sprd\-B51\+/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Sprd\SprdB51plus($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/BlackBerry 9790/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\BlackBerry\BlackBerry9790($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/BlackBerry 9720/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\BlackBerry\BlackBerry9720($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/BB10; Kbd/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\BlackBerry\BlackBerryKbd($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/BB10; Touch/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\BlackBerry\BlackBerryZ10($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/XT1068/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Motorola\MotorolaXt1068($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/XT1039/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Motorola\MotorolaXt1039($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/XT1032/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Motorola\MotorolaXt1032($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/MotoG3/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Motorola\MotorolaMotoG3($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/nexus 6p/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Huawei\HuaweiNexus6p($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/nexus 6/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Motorola\MotorolaNexus6($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/ME302KL/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Asus\AsusMe302kl($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/nexus 7/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Asus\AsusGalaxyNexus7($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/K013/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Asus\AsusMemoPadK013($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/K012/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Asus\AsusFoneK012($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/ME302C/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Asus\AsusMe302c($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/WIN HD W510u/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Blu\BluWinHdW510u($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/N9500/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Star\StarN9500($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/tolino tab 8\.9/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Tolino\TolinoTab89($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/tolino tab 8/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Tolino\TolinoTab8($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/Lenovo S660/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Lenovo\LenovoS660($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/Lenovo S920/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Lenovo\LenovoS920($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/IdeaTab S6000\-H/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Lenovo\LenovoS6000hIdeaTab($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/Lenovo A850\+/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Lenovo\LenovoA850Plus($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/SmartTabII10/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Lenovo\VodafoneSmartTabIi10($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/P1032X/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Lenovo\LenovoP1032x($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/Lenovo A7000\-a/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Lenovo\LenovoA7000a($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/Lenovo B6000\-H/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Lenovo\LenovoB6000h($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/S208/i', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Cubot\CubotS208($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/306SH/', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Sharp\SH306($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/JERRY/', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Wiko\WikoJerry($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/BLOOM/', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Wiko\WikoBloom($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/DARKSIDE/', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Wiko\WikoDarkside($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/ M3 /', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Gionee\GioneeMarathonM3($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/4034D/', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Alcatel\AlcatelOt4034D($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/Archos 50b Platinum/', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Archos\Archos50bPlatinum($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/Archos 50 Titanium/', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Archos\Archos50Titanium($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/Archos 50 Oxygen Plus/', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Archos\Archos50OxygenPlus($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/ARCHOS 101 XS 2/', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Archos\Archos101xs2($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/Archos 101d Neon/', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Archos\Archos101dNeon($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/Archos 101 Copper/', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Archos\Archos101Copper($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/ZTE Blade V6/', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Zte\ZteBladev6($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/ZTE Blade L5 Plus/', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Zte\ZteBladeL5plus($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/ZTE Blade L6/', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Zte\ZteBladeL6($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/ZTE N919/', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Zte\ZteN919($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/IEOS_QUAD_10_PRO/', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Odys\OdysIeosQuad10pro($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/IEOS_QUAD_W/', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Odys\OdysIeosQuadw($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/MAVEN_10_PLUS/', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Odys\OdysMaven10plus($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/AT300SE/', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Toshiba\ToshibaAt300SE($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/A3\-A11/', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Acer\AcerIconiaA3A11($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/A3\-A10/', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Acer\AcerIconiaA3A10($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/A700/', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Acer\AcerIconiaA700($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/B1\-711/', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Acer\AcerIconiaB1711($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/B1\-770/', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Acer\AcerIconiaB1770($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/MediPaD13/', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Bewatec\BewatecMediPad13($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/MediPaD/', $test['ua'])) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Bewatec\BewatecMediPad($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/CFNetwork/', $test['ua'])) {
        $device = \BrowserDetector\Detector\Factory\Device\DarwinFactory::detect($test['ua']);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } else {
        /** @var \UaResult\Result\Result $result */
        $result = $detector->getBrowser($test['ua']);

        $device = $result->getDevice();

        if ($deviceCode === $device->getDeviceName()) {
            $deviceBrand       = $device->getBrand();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType()->getName();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif ('general Mobile Device' === $device->getDeviceName() && in_array($deviceCode, ['general Mobile Phone', 'general Tablet'])) {
            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType()->getName();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif ('Windows RT Tablet' === $device->getDeviceName() && $deviceCode === 'general Tablet') {
            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType()->getName();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif ('Windows Desktop' === $device->getDeviceName()
            && $deviceCode === 'unknown'
            && in_array($platform->getMarketingName(), ['Windows 7', 'Windows 8', 'Windows 8.1', 'Windows 10', 'Windows XP', 'Windows Vista'])
        ) {
            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType()->getName();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif ('Linux Desktop' === $device->getDeviceName()
            && $deviceCode === 'unknown'
            && in_array($platform->getMarketingName(), ['Linux'])
        ) {
            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType()->getName();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif ('Macintosh' === $device->getDeviceName()
            && $deviceCode === 'unknown'
            && in_array($platform->getMarketingName(), ['Mac OS X', 'macOS'])
        ) {
            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType()->getName();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        }
    }

    return [$deviceBrand, $deviceCode, $devicePointing, $deviceType, $deviceMaker, $deviceName, $deviceOrientation, $device];
}