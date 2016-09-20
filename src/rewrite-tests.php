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

    $useragent = $test['ua'];

    // rewrite Darwin platform
    if ('Darwin' === $platformCodename) {
        $platform = \BrowserDetector\Detector\Factory\Platform\DarwinFactory::detect($useragent);

        $platformCodename      = $platform->getName();
        $platformMarketingname = $platform->getMarketingName();
        $platformVersion       = $platform->getVersion()->getVersion();
        $platformBits          = $platform->getBits();
        $platformMaker         = $platform->getManufacturer();
        $platformBrandname     = $platform->getBrand();
    } elseif ('Windows' === $platformCodename) {
        $platform = \BrowserDetector\Detector\Factory\Platform\WindowsFactory::detect($useragent);

        $platformCodename      = $platform->getName();
        $platformMarketingname = $platform->getMarketingName();
        $platformVersion       = $platform->getVersion()->getVersion();
        $platformBits          = $platform->getBits();
        $platformMaker         = $platform->getManufacturer();
        $platformBrandname     = $platform->getBrand();
    } elseif ('Android' === $platformCodename && preg_match('/windows phone/i', $useragent)) {
        $platform = new \BrowserDetector\Detector\Os\WindowsPhoneOs($useragent);

        $platformCodename      = $platform->getName();
        $platformMarketingname = $platform->getMarketingName();
        $platformVersion       = $platform->getVersion()->getVersion();
        $platformBits          = $platform->getBits();
        $platformMaker         = $platform->getManufacturer();
        $platformBrandname     = $platform->getBrand();
    } elseif (preg_match('/Puffin\/[\d\.]+I(T|P)/', $useragent)) {
        $platform = new \BrowserDetector\Detector\Os\Ios($useragent);

        $platformCodename      = $platform->getName();
        $platformMarketingname = $platform->getMarketingName();
        $platformVersion       = $platform->getVersion()->getVersion();
        $platformBits          = $platform->getBits();
        $platformMaker         = $platform->getManufacturer();
        $platformBrandname     = $platform->getBrand();
    } elseif (preg_match('/Puffin\/[\d\.]+A(T|P)/', $useragent)) {
        $platform = new \BrowserDetector\Detector\Os\AndroidOs($useragent);

        $platformCodename      = $platform->getName();
        $platformMarketingname = $platform->getMarketingName();
        $platformVersion       = $platform->getVersion()->getVersion();
        $platformBits          = $platform->getBits();
        $platformMaker         = $platform->getManufacturer();
        $platformBrandname     = $platform->getBrand();
    } elseif (preg_match('/Puffin\/[\d\.]+W(T|P)/', $useragent)) {
        $platform = new \BrowserDetector\Detector\Os\WindowsPhoneOs($useragent);

        $platformCodename      = $platform->getName();
        $platformMarketingname = $platform->getMarketingName();
        $platformVersion       = $platform->getVersion()->getVersion();
        $platformBits          = $platform->getBits();
        $platformMaker         = $platform->getManufacturer();
        $platformBrandname     = $platform->getBrand();
    } elseif (false !== stripos($useragent, 'linux mint')) {
        $platform = new \BrowserDetector\Detector\Os\Mint($useragent);

        $platformCodename      = $platform->getName();
        $platformMarketingname = $platform->getMarketingName();
        $platformVersion       = $platform->getVersion()->getVersion();
        $platformBits          = $platform->getBits();
        $platformMaker         = $platform->getManufacturer();
        $platformBrandname     = $platform->getBrand();
    } elseif (false !== stripos($useragent, 'kubuntu')) {
        $platform = new \BrowserDetector\Detector\Os\Kubuntu($useragent);

        $platformCodename      = $platform->getName();
        $platformMarketingname = $platform->getMarketingName();
        $platformVersion       = $platform->getVersion()->getVersion();
        $platformBits          = $platform->getBits();
        $platformMaker         = $platform->getManufacturer();
        $platformBrandname     = $platform->getBrand();
    } elseif (false !== stripos($useragent, 'ubuntu')) {
        $platform = new \BrowserDetector\Detector\Os\Ubuntu($useragent);

        $platformCodename      = $platform->getName();
        $platformMarketingname = $platform->getMarketingName();
        $platformVersion       = $platform->getVersion()->getVersion();
        $platformBits          = $platform->getBits();
        $platformMaker         = $platform->getManufacturer();
        $platformBrandname     = $platform->getBrand();
    } elseif (preg_match('/HP\-UX/', $useragent)) {
        $platform = new \BrowserDetector\Detector\Os\Hpux($useragent);

        $platformCodename      = $platform->getName();
        $platformMarketingname = $platform->getMarketingName();
        $platformVersion       = $platform->getVersion()->getVersion();
        $platformBits          = $platform->getBits();
        $platformMaker         = $platform->getManufacturer();
        $platformBrandname     = $platform->getBrand();
    } elseif ('Windows' === $platformCodename && preg_match('/windows ce/i', $useragent)) {
        $platform = new \BrowserDetector\Detector\Os\WindowsCe($useragent);

        $platformCodename      = $platform->getName();
        $platformMarketingname = $platform->getMarketingName();
        $platformVersion       = $platform->getVersion()->getVersion();
        $platformBits          = $platform->getBits();
        $platformMaker         = $platform->getManufacturer();
        $platformBrandname     = $platform->getBrand();
    } elseif (preg_match('/(red hat|redhat)/i', $useragent)) {
        $platform = new \BrowserDetector\Detector\Os\Redhat($useragent);

        $platformCodename      = $platform->getName();
        $platformMarketingname = $platform->getMarketingName();
        $platformVersion       = $platform->getVersion()->getVersion();
        $platformBits          = $platform->getBits();
        $platformMaker         = $platform->getManufacturer();
        $platformBrandname     = $platform->getBrand();
    } elseif ('Windows Mobile OS' === $platformCodename && preg_match('/Windows Mobile; WCE/', $useragent)) {
        $platform = new \BrowserDetector\Detector\Os\WindowsCe($useragent);

        $platformCodename      = $platform->getName();
        $platformMarketingname = $platform->getMarketingName();
        $platformVersion       = $platform->getVersion()->getVersion();
        $platformBits          = $platform->getBits();
        $platformMaker         = $platform->getManufacturer();
        $platformBrandname     = $platform->getBrand();
    } elseif ('Linux' === $platformCodename && preg_match('/SUSE/', $useragent)) {
        $platform = new \BrowserDetector\Detector\Os\Suse($useragent);

        $platformCodename      = $platform->getName();
        $platformMarketingname = $platform->getMarketingName();
        $platformVersion       = $platform->getVersion()->getVersion();
        $platformBits          = $platform->getBits();
        $platformMaker         = $platform->getManufacturer();
        $platformBrandname     = $platform->getBrand();
    } elseif ('Linux' === $platformCodename && preg_match('/centos/i', $useragent)) {
        $platform = new \BrowserDetector\Detector\Os\CentOs($useragent);

        $platformCodename      = $platform->getName();
        $platformMarketingname = $platform->getMarketingName();
        $platformVersion       = $platform->getVersion()->getVersion();
        $platformBits          = $platform->getBits();
        $platformMaker         = $platform->getManufacturer();
        $platformBrandname     = $platform->getBrand();
    } elseif (false !== strpos($useragent, 'Windows Phone')) {
        $platform = new \BrowserDetector\Detector\Os\WindowsPhoneOs($useragent);

        $platformCodename      = $platform->getName();
        $platformMarketingname = $platform->getMarketingName();
        $platformVersion       = $platform->getVersion()->getVersion();
        $platformBits          = $platform->getBits();
        $platformMaker         = $platform->getManufacturer();
        $platformBrandname     = $platform->getBrand();
    } elseif (false !== strpos($useragent, 'wds')) {
        $platform = new \BrowserDetector\Detector\Os\WindowsPhoneOs($useragent);

        $platformCodename      = $platform->getName();
        $platformMarketingname = $platform->getMarketingName();
        $platformVersion       = $platform->getVersion()->getVersion();
        $platformBits          = $platform->getBits();
        $platformMaker         = $platform->getManufacturer();
        $platformBrandname     = $platform->getBrand();
    } elseif (false !== stripos($useragent, 'wpdesktop')) {
        $platform = new \BrowserDetector\Detector\Os\WindowsPhoneOs($useragent);

        $platformCodename      = $platform->getName();
        $platformMarketingname = $platform->getMarketingName();
        $platformVersion       = $platform->getVersion()->getVersion();
        $platformBits          = $platform->getBits();
        $platformMaker         = $platform->getManufacturer();
        $platformBrandname     = $platform->getBrand();
    } elseif (false !== stripos($useragent, 'xblwp7')) {
        $platform = new \BrowserDetector\Detector\Os\WindowsPhoneOs($useragent);

        $platformCodename      = $platform->getName();
        $platformMarketingname = $platform->getMarketingName();
        $platformVersion       = $platform->getVersion()->getVersion();
        $platformBits          = $platform->getBits();
        $platformMaker         = $platform->getManufacturer();
        $platformBrandname     = $platform->getBrand();
    } elseif (false !== stripos($useragent, 'zunewp7')) {
        $platform = new \BrowserDetector\Detector\Os\WindowsPhoneOs($useragent);

        $platformCodename      = $platform->getName();
        $platformMarketingname = $platform->getMarketingName();
        $platformVersion       = $platform->getVersion()->getVersion();
        $platformBits          = $platform->getBits();
        $platformMaker         = $platform->getManufacturer();
        $platformBrandname     = $platform->getBrand();
    } elseif (false !== strpos($useragent, 'Tizen')) {
        $platform = new \BrowserDetector\Detector\Os\Tizen($useragent);

        $platformCodename      = $platform->getName();
        $platformMarketingname = $platform->getMarketingName();
        $platformVersion       = $platform->getVersion()->getVersion();
        $platformBits          = $platform->getBits();
        $platformMaker         = $platform->getManufacturer();
        $platformBrandname     = $platform->getBrand();
    } elseif (false !== strpos($useragent, 'Windows CE')) {
        $platform = new \BrowserDetector\Detector\Os\WindowsCe($useragent);

        $platformCodename      = $platform->getName();
        $platformMarketingname = $platform->getMarketingName();
        $platformVersion       = $platform->getVersion()->getVersion();
        $platformBits          = $platform->getBits();
        $platformMaker         = $platform->getManufacturer();
        $platformBrandname     = $platform->getBrand();
    } elseif (preg_match('/MIUI/', $useragent)) {
        $platform = new \BrowserDetector\Detector\Os\MiuiOs($useragent);

        $platformCodename      = $platform->getName();
        $platformMarketingname = $platform->getMarketingName();
        $platformVersion       = $platform->getVersion()->getVersion();
        $platformBits          = $platform->getBits();
        $platformMaker         = $platform->getManufacturer();
        $platformBrandname     = $platform->getBrand();
    } elseif (false !== strpos($useragent, 'Linux; Android')) {
        $platform = new \BrowserDetector\Detector\Os\AndroidOs($useragent);

        $platformCodename      = $platform->getName();
        $platformMarketingname = $platform->getMarketingName();
        $platformVersion       = $platform->getVersion()->getVersion();
        $platformBits          = $platform->getBits();
        $platformMaker         = $platform->getManufacturer();
        $platformBrandname     = $platform->getBrand();
    } elseif (false !== strpos($useragent, 'Linux; U; Android')) {
        $platform = new \BrowserDetector\Detector\Os\AndroidOs($useragent);

        $platformCodename      = $platform->getName();
        $platformMarketingname = $platform->getMarketingName();
        $platformVersion       = $platform->getVersion()->getVersion();
        $platformBits          = $platform->getBits();
        $platformMaker         = $platform->getManufacturer();
        $platformBrandname     = $platform->getBrand();
    } elseif (false !== strpos($useragent, 'U; Adr')) {
        $platform = new \BrowserDetector\Detector\Os\AndroidOs($useragent);

        $platformCodename      = $platform->getName();
        $platformMarketingname = $platform->getMarketingName();
        $platformVersion       = $platform->getVersion()->getVersion();
        $platformBits          = $platform->getBits();
        $platformMaker         = $platform->getManufacturer();
        $platformBrandname     = $platform->getBrand();
    } elseif (false !== strpos($useragent, 'Android') || false !== strpos($useragent, 'MTK')) {
        $platform = new \BrowserDetector\Detector\Os\AndroidOs($useragent);

        $platformCodename      = $platform->getName();
        $platformMarketingname = $platform->getMarketingName();
        $platformVersion       = $platform->getVersion()->getVersion();
        $platformBits          = $platform->getBits();
        $platformMaker         = $platform->getManufacturer();
        $platformBrandname     = $platform->getBrand();
    } elseif (false !== strpos($useragent, 'UCWEB/2.0 (Linux; U; Opera Mini')) {
        $platform = new \BrowserDetector\Detector\Os\AndroidOs($useragent);

        $platformCodename      = $platform->getName();
        $platformMarketingname = $platform->getMarketingName();
        $platformVersion       = $platform->getVersion()->getVersion();
        $platformBits          = $platform->getBits();
        $platformMaker         = $platform->getManufacturer();
        $platformBrandname     = $platform->getBrand();
    } elseif (false !== strpos($useragent, 'Linux; GoogleTV')) {
        $platform = new \BrowserDetector\Detector\Os\AndroidOs($useragent);

        $platformCodename      = $platform->getName();
        $platformMarketingname = $platform->getMarketingName();
        $platformVersion       = $platform->getVersion()->getVersion();
        $platformBits          = $platform->getBits();
        $platformMaker         = $platform->getManufacturer();
        $platformBrandname     = $platform->getBrand();
    } elseif (false !== strpos($useragent, 'OpenBSD')) {
        $platform = new \BrowserDetector\Detector\Os\OpenBsd($useragent);

        $platformCodename      = $platform->getName();
        $platformMarketingname = $platform->getMarketingName();
        $platformVersion       = $platform->getVersion()->getVersion();
        $platformBits          = $platform->getBits();
        $platformMaker         = $platform->getManufacturer();
        $platformBrandname     = $platform->getBrand();
    } elseif (false !== strpos($useragent, 'Symbian') || false !== strpos($useragent, 'Series 60')) {
        $platform = new \BrowserDetector\Detector\Os\Symbianos($useragent);

        $platformCodename      = $platform->getName();
        $platformMarketingname = $platform->getMarketingName();
        $platformVersion       = $platform->getVersion()->getVersion();
        $platformBits          = $platform->getBits();
        $platformMaker         = $platform->getManufacturer();
        $platformBrandname     = $platform->getBrand();
    } elseif (false !== strpos($useragent, 'MIDP')) {
        $platform = new \BrowserDetector\Detector\Os\Java($useragent);

        $platformCodename      = $platform->getName();
        $platformMarketingname = $platform->getMarketingName();
        $platformVersion       = $platform->getVersion()->getVersion();
        $platformBits          = $platform->getBits();
        $platformMaker         = $platform->getManufacturer();
        $platformBrandname     = $platform->getBrand();
    } elseif (false !== strpos($useragent, 'Windows NT 10.0')) {
        $platform = new \BrowserDetector\Detector\Os\Windows10($useragent, '10.0');

        $platformCodename      = $platform->getName();
        $platformMarketingname = $platform->getMarketingName();
        $platformVersion       = $platform->getVersion()->getVersion();
        $platformBits          = $platform->getBits();
        $platformMaker         = $platform->getManufacturer();
        $platformBrandname     = $platform->getBrand();
    } elseif (false !== strpos($useragent, 'Windows NT 6.4')) {
        $platform = new \BrowserDetector\Detector\Os\Windows10($useragent, '6.4');

        $platformCodename      = $platform->getName();
        $platformMarketingname = $platform->getMarketingName();
        $platformVersion       = $platform->getVersion()->getVersion();
        $platformBits          = $platform->getBits();
        $platformMaker         = $platform->getManufacturer();
        $platformBrandname     = $platform->getBrand();
    } elseif (false !== strpos($useragent, 'Windows NT 6.3') && false !== strpos($useragent, 'ARM')) {
        $platform = new \BrowserDetector\Detector\Os\WindowsRt($useragent);

        $platformCodename      = $platform->getName();
        $platformMarketingname = $platform->getMarketingName();
        $platformVersion       = $platform->getVersion()->getVersion();
        $platformBits          = $platform->getBits();
        $platformMaker         = $platform->getManufacturer();
        $platformBrandname     = $platform->getBrand();
    } elseif (false !== strpos($useragent, 'Windows NT 6.3')) {
        $platform = new \BrowserDetector\Detector\Os\Windows81($useragent);

        $platformCodename      = $platform->getName();
        $platformMarketingname = $platform->getMarketingName();
        $platformVersion       = $platform->getVersion()->getVersion();
        $platformBits          = $platform->getBits();
        $platformMaker         = $platform->getManufacturer();
        $platformBrandname     = $platform->getBrand();
    } elseif (false !== strpos($useragent, 'Windows NT 6.2') && false !== strpos($useragent, 'ARM')) {
        $platform = new \BrowserDetector\Detector\Os\WindowsRt($useragent);

        $platformCodename      = $platform->getName();
        $platformMarketingname = $platform->getMarketingName();
        $platformVersion       = $platform->getVersion()->getVersion();
        $platformBits          = $platform->getBits();
        $platformMaker         = $platform->getManufacturer();
        $platformBrandname     = $platform->getBrand();
    } elseif (false !== strpos($useragent, 'Windows NT 6.2')) {
        $platform = new \BrowserDetector\Detector\Os\Windows8($useragent);

        $platformCodename      = $platform->getName();
        $platformMarketingname = $platform->getMarketingName();
        $platformVersion       = $platform->getVersion()->getVersion();
        $platformBits          = $platform->getBits();
        $platformMaker         = $platform->getManufacturer();
        $platformBrandname     = $platform->getBrand();
    } elseif (false !== strpos($useragent, 'Windows NT 6.1')) {
        $platform = new \BrowserDetector\Detector\Os\Windows7($useragent);

        $platformCodename      = $platform->getName();
        $platformMarketingname = $platform->getMarketingName();
        $platformVersion       = $platform->getVersion()->getVersion();
        $platformBits          = $platform->getBits();
        $platformMaker         = $platform->getManufacturer();
        $platformBrandname     = $platform->getBrand();
    } elseif (false !== strpos($useragent, 'Windows NT 6')) {
        $platform = new \BrowserDetector\Detector\Os\WindowsVista($useragent);

        $platformCodename      = $platform->getName();
        $platformMarketingname = $platform->getMarketingName();
        $platformVersion       = $platform->getVersion()->getVersion();
        $platformBits          = $platform->getBits();
        $platformMaker         = $platform->getManufacturer();
        $platformBrandname     = $platform->getBrand();
    } elseif (false !== strpos($useragent, 'Windows NT 5.3')) {
        $platform = new \BrowserDetector\Detector\Os\WindowsXp($useragent, '5.3');

        $platformCodename      = $platform->getName();
        $platformMarketingname = $platform->getMarketingName();
        $platformVersion       = $platform->getVersion()->getVersion();
        $platformBits          = $platform->getBits();
        $platformMaker         = $platform->getManufacturer();
        $platformBrandname     = $platform->getBrand();
    } elseif (false !== strpos($useragent, 'Windows NT 5.2')) {
        $platform = new \BrowserDetector\Detector\Os\WindowsXp($useragent, '5.2');

        $platformCodename      = $platform->getName();
        $platformMarketingname = $platform->getMarketingName();
        $platformVersion       = $platform->getVersion()->getVersion();
        $platformBits          = $platform->getBits();
        $platformMaker         = $platform->getManufacturer();
        $platformBrandname     = $platform->getBrand();
    } elseif (false !== strpos($useragent, 'Windows NT 5.1')) {
        $platform = new \BrowserDetector\Detector\Os\WindowsXp($useragent, '5.1');

        $platformCodename      = $platform->getName();
        $platformMarketingname = $platform->getMarketingName();
        $platformVersion       = $platform->getVersion()->getVersion();
        $platformBits          = $platform->getBits();
        $platformMaker         = $platform->getManufacturer();
        $platformBrandname     = $platform->getBrand();
    } elseif (false !== strpos($useragent, 'Windows NT 5.01')) {
        $platform = new \BrowserDetector\Detector\Os\Windows2000($useragent, '5.01');

        $platformCodename      = $platform->getName();
        $platformMarketingname = $platform->getMarketingName();
        $platformVersion       = $platform->getVersion()->getVersion();
        $platformBits          = $platform->getBits();
        $platformMaker         = $platform->getManufacturer();
        $platformBrandname     = $platform->getBrand();
    } elseif (false !== strpos($useragent, 'Windows NT 5.0')) {
        $platform = new \BrowserDetector\Detector\Os\Windows2000($useragent, '5.0');

        $platformCodename      = $platform->getName();
        $platformMarketingname = $platform->getMarketingName();
        $platformVersion       = $platform->getVersion()->getVersion();
        $platformBits          = $platform->getBits();
        $platformMaker         = $platform->getManufacturer();
        $platformBrandname     = $platform->getBrand();
    } elseif (false !== strpos($useragent, 'Windows NT 4.10')) {
        $platform = new \BrowserDetector\Detector\Os\WindowsNt($useragent, '4.10');

        $platformCodename      = $platform->getName();
        $platformMarketingname = $platform->getMarketingName();
        $platformVersion       = $platform->getVersion()->getVersion();
        $platformBits          = $platform->getBits();
        $platformMaker         = $platform->getManufacturer();
        $platformBrandname     = $platform->getBrand();
    } elseif (false !== strpos($useragent, 'Windows NT 4.1')) {
        $platform = new \BrowserDetector\Detector\Os\WindowsNt($useragent, '4.1');

        $platformCodename      = $platform->getName();
        $platformMarketingname = $platform->getMarketingName();
        $platformVersion       = $platform->getVersion()->getVersion();
        $platformBits          = $platform->getBits();
        $platformMaker         = $platform->getManufacturer();
        $platformBrandname     = $platform->getBrand();
    } elseif (false !== strpos($useragent, 'Windows NT 4.0')) {
        $platform = new \BrowserDetector\Detector\Os\WindowsNt($useragent, '4.0');

        $platformCodename      = $platform->getName();
        $platformMarketingname = $platform->getMarketingName();
        $platformVersion       = $platform->getVersion()->getVersion();
        $platformBits          = $platform->getBits();
        $platformMaker         = $platform->getManufacturer();
        $platformBrandname     = $platform->getBrand();
    } elseif (false !== strpos($useragent, 'Windows NT 3.5')) {
        $platform = new \BrowserDetector\Detector\Os\WindowsNt($useragent, '3.5');

        $platformCodename      = $platform->getName();
        $platformMarketingname = $platform->getMarketingName();
        $platformVersion       = $platform->getVersion()->getVersion();
        $platformBits          = $platform->getBits();
        $platformMaker         = $platform->getManufacturer();
        $platformBrandname     = $platform->getBrand();
    } elseif (false !== strpos($useragent, 'Windows NT 3.1')) {
        $platform = new \BrowserDetector\Detector\Os\WindowsNt($useragent, '3.1');

        $platformCodename      = $platform->getName();
        $platformMarketingname = $platform->getMarketingName();
        $platformVersion       = $platform->getVersion()->getVersion();
        $platformBits          = $platform->getBits();
        $platformMaker         = $platform->getManufacturer();
        $platformBrandname     = $platform->getBrand();
    } elseif (false !== strpos($useragent, 'Windows[ \-]NT')) {
        $platform = new \BrowserDetector\Detector\Os\WindowsNt($useragent, '0.0');

        $platformCodename      = $platform->getName();
        $platformMarketingname = $platform->getMarketingName();
        $platformVersion       = $platform->getVersion()->getVersion();
        $platformBits          = $platform->getBits();
        $platformMaker         = $platform->getManufacturer();
        $platformBrandname     = $platform->getBrand();
    } elseif (false !== stripos($useragent, 'cygwin')) {
        $platform = new \BrowserDetector\Detector\Os\Cygwin($useragent);

        $platformCodename      = $platform->getName();
        $platformMarketingname = $platform->getMarketingName();
        $platformVersion       = $platform->getVersion()->getVersion();
        $platformBits          = $platform->getBits();
        $platformMaker         = $platform->getManufacturer();
        $platformBrandname     = $platform->getBrand();
    } elseif (false !== strpos($useragent, 'CPU OS')) {
        $platform = new \BrowserDetector\Detector\Os\Ios($useragent);

        $platformCodename      = $platform->getName();
        $platformMarketingname = $platform->getMarketingName();
        $platformVersion       = $platform->getVersion()->getVersion();
        $platformBits          = $platform->getBits();
        $platformMaker         = $platform->getManufacturer();
        $platformBrandname     = $platform->getBrand();
    } elseif (false !== strpos($useragent, 'CPU iPhone OS')) {
        $platform = new \BrowserDetector\Detector\Os\Ios($useragent);

        $platformCodename      = $platform->getName();
        $platformMarketingname = $platform->getMarketingName();
        $platformVersion       = $platform->getVersion()->getVersion();
        $platformBits          = $platform->getBits();
        $platformMaker         = $platform->getManufacturer();
        $platformBrandname     = $platform->getBrand();
    } elseif (false !== strpos($useragent, 'CPU like Mac OS X')) {
        $platform = new \BrowserDetector\Detector\Os\Ios($useragent);

        $platformCodename      = $platform->getName();
        $platformMarketingname = $platform->getMarketingName();
        $platformVersion       = $platform->getVersion()->getVersion();
        $platformBits          = $platform->getBits();
        $platformMaker         = $platform->getManufacturer();
        $platformBrandname     = $platform->getBrand();
    } elseif (false !== strpos($useragent, 'iOS')) {
        $platform = new \BrowserDetector\Detector\Os\Ios($useragent);

        $platformCodename      = $platform->getName();
        $platformMarketingname = $platform->getMarketingName();
        $platformVersion       = $platform->getVersion()->getVersion();
        $platformBits          = $platform->getBits();
        $platformMaker         = $platform->getManufacturer();
        $platformBrandname     = $platform->getBrand();
    } elseif (false !== strpos($useragent, 'Mac OS X')) {
        $platform = new \BrowserDetector\Detector\Os\Macosx($useragent);

        $platformCodename      = $platform->getName();
        $platformMarketingname = $platform->getMarketingName();
        $platformVersion       = $platform->getVersion()->getVersion();
        $platformBits          = $platform->getBits();
        $platformMaker         = $platform->getManufacturer();
        $platformBrandname     = $platform->getBrand();
    } elseif (false !== strpos($useragent, 'hpwOS')) {
        $platform = new \BrowserDetector\Detector\Os\WebOs($useragent);

        $platformCodename      = $platform->getName();
        $platformMarketingname = $platform->getMarketingName();
        $platformVersion       = $platform->getVersion()->getVersion();
        $platformBits          = $platform->getBits();
        $platformMaker         = $platform->getManufacturer();
        $platformBrandname     = $platform->getBrand();
    } elseif (false !== strpos($useragent, 'Debian APT-HTTP')) {
        $platform = new \BrowserDetector\Detector\Os\Debian($useragent);

        $platformCodename      = $platform->getName();
        $platformMarketingname = $platform->getMarketingName();
        $platformVersion       = $platform->getVersion()->getVersion();
        $platformBits          = $platform->getBits();
        $platformMaker         = $platform->getManufacturer();
        $platformBrandname     = $platform->getBrand();
    } elseif (preg_match('/linux arm/i', $useragent)) {
        $platform = new \BrowserDetector\Detector\Os\Maemo($useragent);

        $platformCodename      = $platform->getName();
        $platformMarketingname = $platform->getMarketingName();
        $platformVersion       = $platform->getVersion()->getVersion();
        $platformBits          = $platform->getBits();
        $platformMaker         = $platform->getManufacturer();
        $platformBrandname     = $platform->getBrand();
    } elseif (false !== stripos($useragent, 'fedora')) {
        $platform = new \BrowserDetector\Detector\Os\Fedora($useragent);

        $platformCodename      = $platform->getName();
        $platformMarketingname = $platform->getMarketingName();
        $platformVersion       = $platform->getVersion()->getVersion();
        $platformBits          = $platform->getBits();
        $platformMaker         = $platform->getManufacturer();
        $platformBrandname     = $platform->getBrand();
    } elseif (false !== stripos($useragent, 'suse')) {
        $platform = new \BrowserDetector\Detector\Os\Suse($useragent);

        $platformCodename      = $platform->getName();
        $platformMarketingname = $platform->getMarketingName();
        $platformVersion       = $platform->getVersion()->getVersion();
        $platformBits          = $platform->getBits();
        $platformMaker         = $platform->getManufacturer();
        $platformBrandname     = $platform->getBrand();
    } elseif (false !== stripos($useragent, 'mandriva')) {
        $platform = new \BrowserDetector\Detector\Os\Mandriva($useragent);

        $platformCodename      = $platform->getName();
        $platformMarketingname = $platform->getMarketingName();
        $platformVersion       = $platform->getVersion()->getVersion();
        $platformBits          = $platform->getBits();
        $platformMaker         = $platform->getManufacturer();
        $platformBrandname     = $platform->getBrand();
    } elseif (false !== stripos($useragent, 'gentoo')) {
        $platform = new \BrowserDetector\Detector\Os\Gentoo($useragent);

        $platformCodename      = $platform->getName();
        $platformMarketingname = $platform->getMarketingName();
        $platformVersion       = $platform->getVersion()->getVersion();
        $platformBits          = $platform->getBits();
        $platformMaker         = $platform->getManufacturer();
        $platformBrandname     = $platform->getBrand();
    } elseif (false !== stripos($useragent, 'slackware')) {
        $platform = new \BrowserDetector\Detector\Os\Slackware($useragent);

        $platformCodename      = $platform->getName();
        $platformMarketingname = $platform->getMarketingName();
        $platformVersion       = $platform->getVersion()->getVersion();
        $platformBits          = $platform->getBits();
        $platformMaker         = $platform->getManufacturer();
        $platformBrandname     = $platform->getBrand();
    } elseif (false !== strpos($useragent, 'CrOS')) {
        $platform = new \BrowserDetector\Detector\Os\CrOs($useragent);

        $platformCodename      = $platform->getName();
        $platformMarketingname = $platform->getMarketingName();
        $platformVersion       = $platform->getVersion()->getVersion();
        $platformBits          = $platform->getBits();
        $platformMaker         = $platform->getManufacturer();
        $platformBrandname     = $platform->getBrand();
    } elseif (false !== stripos($useragent, 'debian')) {
        $platform = new \BrowserDetector\Detector\Os\Debian($useragent);

        $platformCodename      = $platform->getName();
        $platformMarketingname = $platform->getMarketingName();
        $platformVersion       = $platform->getVersion()->getVersion();
        $platformBits          = $platform->getBits();
        $platformMaker         = $platform->getManufacturer();
        $platformBrandname     = $platform->getBrand();
    } elseif (false !== stripos($useragent, 'android; linux arm')) {
        $platform = new \BrowserDetector\Detector\Os\AndroidOs($useragent);

        $platformCodename      = $platform->getName();
        $platformMarketingname = $platform->getMarketingName();
        $platformVersion       = $platform->getVersion()->getVersion();
        $platformBits          = $platform->getBits();
        $platformMaker         = $platform->getManufacturer();
        $platformBrandname     = $platform->getBrand();
    } elseif (preg_match('/(maemo|like android|linux\/x2\/r1|linux arm)/i', $useragent)) {
        $platform = new \BrowserDetector\Detector\Os\Maemo($useragent);

        $platformCodename      = $platform->getName();
        $platformMarketingname = $platform->getMarketingName();
        $platformVersion       = $platform->getVersion()->getVersion();
        $platformBits          = $platform->getBits();
        $platformMaker         = $platform->getManufacturer();
        $platformBrandname     = $platform->getBrand();
    } elseif (false !== stripos($useragent, 'moblin')) {
        $platform = new \BrowserDetector\Detector\Os\Moblin($useragent);

        $platformCodename      = $platform->getName();
        $platformMarketingname = $platform->getMarketingName();
        $platformVersion       = $platform->getVersion()->getVersion();
        $platformBits          = $platform->getBits();
        $platformMaker         = $platform->getManufacturer();
        $platformBrandname     = $platform->getBrand();
    } elseif (false !== stripos($useragent, 'infegyatlas') || false !== stripos($useragent, 'jobboerse')) {
        $platform = new \BrowserDetector\Detector\Os\UnknownOs($useragent);

        $platformCodename      = $platform->getName();
        $platformMarketingname = $platform->getMarketingName();
        $platformVersion       = $platform->getVersion()->getVersion();
        $platformBits          = $platform->getBits();
        $platformMaker         = $platform->getManufacturer();
        $platformBrandname     = $platform->getBrand();
    } elseif (preg_match('/Puffin\/[\d\.]+(A|I|W|M)(T|P)?/', $useragent)) {
        $platform = new \BrowserDetector\Detector\Os\UnknownOs($useragent);

        $platformCodename      = $platform->getName();
        $platformMarketingname = $platform->getMarketingName();
        $platformVersion       = $platform->getVersion()->getVersion();
        $platformBits          = $platform->getBits();
        $platformMaker         = $platform->getManufacturer();
        $platformBrandname     = $platform->getBrand();
    } elseif (false !== strpos($useragent, 'Linux')) {
        $platform = new \BrowserDetector\Detector\Os\Linux($useragent);

        $platformCodename      = $platform->getName();
        $platformMarketingname = $platform->getMarketingName();
        $platformVersion       = $platform->getVersion()->getVersion();
        $platformBits          = $platform->getBits();
        $platformMaker         = $platform->getManufacturer();
        $platformBrandname     = $platform->getBrand();
    } elseif (false !== strpos($useragent, 'SymbOS')) {
        $platform = new \BrowserDetector\Detector\Os\Symbianos($useragent);

        $platformCodename      = $platform->getName();
        $platformMarketingname = $platform->getMarketingName();
        $platformVersion       = $platform->getVersion()->getVersion();
        $platformBits          = $platform->getBits();
        $platformMaker         = $platform->getManufacturer();
        $platformBrandname     = $platform->getBrand();
    } elseif (preg_match('/CFNetwork/', $useragent)) {
        $platform = \BrowserDetector\Detector\Factory\Platform\DarwinFactory::detect($useragent);

        $platformCodename      = $platform->getName();
        $platformMarketingname = $platform->getMarketingName();
        $platformVersion       = $platform->getVersion()->getVersion();
        $platformBits          = $platform->getBits();
        $platformMaker         = $platform->getManufacturer();
        $platformBrandname     = $platform->getBrand();
    } else {
        $result = $detector->getBrowser($useragent);

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

    $useragent = $test['ua'];

    if (preg_match('/redmi 3s/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Xiaomi\XiaomiRedmi3s($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/redmi 3/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Xiaomi\XiaomiRedmi3($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/mi max/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Xiaomi\XiaomiMiMax($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/mi 4lte/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Xiaomi\XiaomiMi4lte($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/mi pad/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Xiaomi\XiaomiMiPad($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/one[_ ]m9plus/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Htc\HtcOneM9plus($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/one[_ ]m9/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Htc\HtcOneM9($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/(one[ _]sv|onesv)/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Htc\HtcOneSv($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/(one[ _]x\+|onexplus)/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Htc\HtcOneXplus($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/one[ _]xl/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Htc\HtcOneXl($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/(one[ _]x|onex|PJ83100)/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Htc\HtcOneX($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/(PC36100|EVO 4G)/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Htc\HtcEvo4g($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/Evo 3D GSM/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Htc\HtcEvo3dGsm($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/HTC T328d/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Htc\HtcT328d($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/desire[ _]500/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Htc\HtcDesire500($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/desire[ _]310/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Htc\HtcDesire310($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/(0p4e2|desire[ _]601)/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Htc\Htc0p4e2($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/desire[ _]eye/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Htc\HtcDesireEye($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/desire_a8181/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Htc\HtcA8181Desire($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/nexus 9/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Htc\HtcNexus9($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/htc_amaze/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Htc\HtcAmaze($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/htc_butterfly_s_901s/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Htc\HtcS910s($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/(rm\-1113|lumia 640 lte)/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Nokia\NokiaLumia640lte($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/rm\-1075/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Nokia\NokiaLumia640lteRm1075($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/rm\-1067/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Nokia\NokiaLumia640xlRm1067($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/rm\-1090/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Nokia\NokiaLumia535Rm1090($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/rm\-994/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Nokia\NokiaLumia1320Rm994($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/rm\-1010/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Nokia\NokiaLumia638($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/lumia 720/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Nokia\NokiaLumia720($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/lumia 521/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Nokia\NokiaLumia521($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/lumia 535/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Nokia\NokiaLumia535($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/lumia 540/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Nokia\NokiaLumia540($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/lumia 1320/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Nokia\NokiaLumia1320($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/lumia 930/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Nokia\NokiaLumia930($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/lumia 640 xl/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Nokia\NokiaLumia640xl($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/genm14/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Nokia\NokiaXl2($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/nokia300/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Nokia\Nokia300($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/Nokia5800d/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Nokia\Nokia5800XpressMusic($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/Nokia5230/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Nokia\Nokia5230($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/NokiaC2\-01/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Nokia\NokiaC201($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/NokiaN8\-00/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Nokia\NokiaN800($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/NokiaN95/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Nokia\NokiaN95($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/L50u/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\SonyEricsson\SonyL50uExperiaZ2lte($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/SonyEricssonS312/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\SonyEricsson\SonyEricssonS312($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/(Xperia Z|C6603)/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\SonyEricsson\SonyC6603ExperiaZ($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/LT26ii/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\SonyEricsson\SonyEricssonLT26ii($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/LT26i/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\SonyEricsson\SonyEricssonLT26i($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/LT26w/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\SonyEricsson\SonyLT26w($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/LT30p/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\SonyEricsson\SonyLT30p($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/ST26i/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\SonyEricsson\SonyST26i($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/D6603/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\SonyEricsson\SonyD6603ExperiaZ3($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/D6503/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\SonyEricsson\SonyD6503ExperiaZ2($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/D2005/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\SonyEricsson\SonyD2005($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/D2203/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\SonyEricsson\SonyD2203($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/C5303/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\SonyEricsson\SonyC5303XperiaSp($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/C6903/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\SonyEricsson\SonyC6903ExperiaZ1($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/C1905/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\SonyEricsson\SonyC1905($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/SGP512/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\SonyEricsson\SonyTabletSgp512($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/SGP521/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\SonyEricsson\SonyTabletSgp521($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/SGP771/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\SonyEricsson\SonyTabletSgp771($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/E5823/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\SonyEricsson\SonyE5823($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/E2303/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\SonyEricsson\SonyE2303($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/E2003/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\SonyEricsson\SonyE2003($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/E6653/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\SonyEricsson\SonyE6653($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/ONEPLUS A3000/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Oneplus\OneplusA3000($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/ONE E1003/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Oneplus\OneplusE1003($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/MZ\-MX5/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Meizu\MeizuMx5($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/SM\-G9006V/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSmG9006v($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/SM\-G900F/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSmG900F($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/SM\-G903F/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSmG903F($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/SM\-G901F/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSmG901F($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/SM\-G925F/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSmG925F($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/SM\-G920V/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSmG920V($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/SM\-G920FD/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSmG920Fd($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/SM\-G920F/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSmG920F($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/SM\-G920S/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSmG920S($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/SM\-G920I/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSmG920I($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/SM\-G920A/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSmG920A($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/SM\-G920T1/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSmG920T1($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/SM\-G920T/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSmG920T($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/SM\-G9200/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSmG9200($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/SM\-G9208/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSmG9208($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/SM\-G9209/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSmG9209($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/SM\-G850F/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSmG850F($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/SM\-G870A/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSmG870a($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/SM\-G530H/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSmG530h($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/SM\-G388F/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSmG388F($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/SM\-G360H/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSmG360H($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/SM\-G130H/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSmG130H($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/SM\-T110/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSmT110($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/SM\-T525/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSmT525($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/SM\-T580/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSmT580($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/SM\-T585/i', $useragent)) {

        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSmT585($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/SM\-T810x/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSmT810x($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/SM\-T810/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSmT810($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/SM\-T815y/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSmT815y($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/SM\-T815/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSmT815($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/SM\-T813/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSmT813($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/SM\-T819/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSmT819($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/SM\-T805/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSmT805($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/SM\-T315/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSmT315($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/SM\-C101/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSmC101($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/SM\-N9005/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSmN9005($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/SM\-N9002/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSmN9002($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/SM\-N9008V/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSmN9008V($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/SM\-N9009/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSmN9009($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/SM\-N9007/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSmN9007($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/SM\-N9006/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSmN9006($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/SM\-N900A/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSmN900A($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/SM\-N900V/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSmN900V($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/SM\-N900K/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSmN900K($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/SM\-N900S/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSmN900S($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/SM\-N900T/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSmN900T($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/SM\-N900P/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSmN900P($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/SM\-N900L/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSmN900L($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/SM\-N900W8/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSmN900W8($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/SM\-N900/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSmN900($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/SM\-E500H/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSmE500H($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/SM\-A500FU/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSmA500fu($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/SM\-A500F/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSmA500f($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/SM\-A500H/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSmA500h($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/SM\-A300FU/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSmA300fu($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/SM\-A300F/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSmA300f($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/SM\-A310F/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSmA310f($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/SM\-A510FD/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSmA510fd($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/SM\-A510F/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSmA510f($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/SM\-A510M/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSmA510m($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/SM\-A510Y/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSmA510y($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/SM\-A5100/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSmA5100($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/SM\-A700FD/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSmA700fd($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/SM\-A700F/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSmA700f($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/SM\-A700S/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSmA700s($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/SM\-A700K/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSmA700k($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/SM\-A700L/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSmA700l($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/SM\-A700H/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSmA700h($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/SM\-A700YD/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSmA700yd($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/SM\-A7000/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSmA7000($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/SM\-A7009/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSmA7009($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/SM\-J500FN/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSmJ500fn($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/SM\-J500F/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSmJ500f($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/SM\-J500G/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSmJ500g($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/SM\-J500Y/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSmJ500y($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/SM\-J500M/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSmJ500m($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/SM\-J500H/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSmJ500h($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/SM\-J5007/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSmJ5007($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/SM\-J320g/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSmJ320g($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/SM\-P600/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSmP600($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/Nexus Player/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungGalaxyNexusPlayer($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/NEO\-X5/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Minix\MinixNeoX5($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/vns\-l31/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Huawei\HuaweiVnsL31($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/g750\-u10/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Huawei\HuaweiG750u10($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/g730\-u10/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Huawei\HuaweiG730u10($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/MediaPad 7 Youth/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Huawei\HuaweiMediaPad7Youth($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/PE\-TL10/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Huawei\HuaweiPetl10($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/HUAWEI G6\-L11/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Huawei\HuaweiG6L11($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/HUAWEI P7\-L10/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Huawei\HuaweiP7L10($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/HUAWEI SCL\-L01/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Huawei\HuaweiSclL01($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/EVA\-L09/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Huawei\HuaweiEvaL09($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/mediapad 10 link\+/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Huawei\HuaweiMediaPad10LinkPlus($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/mediapad 10 link/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Huawei\HuaweiMediaPad10Link($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/u8651t/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Huawei\HuaweiU8651t($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/u8651s/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Huawei\HuaweiU8651s($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/u8651/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Huawei\HuaweiU8651($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/F5281/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Hisense\HisenseF5281($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/Aquaris M10/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Bq\BqAquarisM10($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/Aquaris M5/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Bq\BqAquarisM5($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/gt\-i9195i/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungGti9195i($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/gt\-i9195/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungGti9195($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/gt\-i9100g/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungGti9100g($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/gt\-i9100p/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungGti9100p($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/gt\-i9100/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungGti9100($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/gt\-i9300i/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungGti9300i($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/gt\-i9300/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungGti9300($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/gt\-i9301i/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungGti9301i($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/gt\-i9301q/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungGti9301q($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/gt\-i9301/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungGti9301($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/gt\-i9060i/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungGti9060i($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/gt\-i9060l/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungGti9060l($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/gt\-i9060/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungGti9060($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/gt\-i9505g/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungGti9505g($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/gt\-i9505x/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungGti9505x($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/gt\-i9505/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungGti9505($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/gt\-i5500/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungGti5500($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/gt\-i5700/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungGti5700($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/gt\-i8190n/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungGti8190n($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/gt\-i8190/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungGti8190($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/gt\-i8150/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungGti8150($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/gt\-i8200n/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungGti8200n($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/gt\-i8200/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungGti8200($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/gt\-e3309t/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungGte3309t($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/gt\-e2202/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungGte2202($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/gt\-e2252/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungGte2252($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/gt\-b7722/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungGtb7722($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/gt\-s7262/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungGts7262($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/gt\-s7275r/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungGts7275r($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/gt\-s7500/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungGts7500($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/gt\-s3802/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungGts3802($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/gt\-s3653/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungGts3653($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/gt\-s5620/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungGts5620($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/gt\-s5301L/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungGts5301l($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/gt\-s5301/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungGts5301($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/gt\-s5830l/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungGts5830l($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/gt\-s5830i/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungGts5830i($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/gt\-s5830c/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungGts5830c($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/(gt\-s5830|ace)/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungGts5830($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/gt\-s6810b/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungGts6810b($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/gt\-s6810p/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungGts6810p($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/gt\-s6810/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungGts6810($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/gt\-c6712/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungGtc6712($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/gt\-c3262/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungGtc3262($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/gt\-c3322/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungGtc3322($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/gt\-p5110/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungGtp5110($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/gt\-p7510/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungGtp7510($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/gt\-n7100/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungGtn7100($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/gt\-n7105/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungGtn7105($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/gt\-n5110/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungGtn5110($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/gt\-n8010/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungGtn8010($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/gt\-n8005/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungGtn8005($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/gt\-9000/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungGt9000($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/sgh\-e250i/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSghE250i($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/sgh\-e250/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSghE250($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/sgh\-t528g/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSght528g($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/sgh\-t989d/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSghT989d($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/sgh\-t989/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSghT989($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/sgh\-t999/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSghT999($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/sgh\-i257/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSghi257($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/sgh\-m919/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSghm919($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/sch\-r970/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSchr970($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/shv\-e210l/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungShvE210l($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/shv\-e210k/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungShvE210k($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/nexus 10/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungGalaxyNexus10($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/Slate 17/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Hp\HpSlate17($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/H345/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Lg\LgH345($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/H320/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Lg\LgH320($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/H850/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Lg\LgH850($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/LG\-D802TR/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Lg\Lgd802tr($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/LG\-D802/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Lg\Lgd802($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/LG\-D855/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Lg\Lgd855($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/LG\-D320/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Lg\Lgd320($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/LG\-D373/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Lg\Lgd373($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/LG\-D290/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Lg\Lgd290($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/LG\-D955/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Lg\Lgd955($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/LG\-D686/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Lg\LgD686($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/LG\-D682tr/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Lg\LgD682tr($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/LG\-D682/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Lg\LgD682($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/LG\-E425/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Lg\Lge425($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/LG\-E612/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Lg\Lge612($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/LG\-F240K/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Lg\LgF240k($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/LG\-V935/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Lg\Lgv935($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/LG\-X150/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Lg\Lgx150($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/nexus 5x/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Lg\LgNexus5x($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/nexus 5/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Lg\LgNexus5($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/nexus 4/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Lg\LgNexus4($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/LIFETAB_E10316/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Medion\MdLifetabE10316($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/LIFETAB_E10312/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Medion\MdLifetabE10312($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/LIFETAB_E10320/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Medion\MdLifetabE10320($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/LIFETAB_E10310/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Medion\MdLifetabE10310($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/LIFETAB_E7312/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Medion\MdLifetabE7312($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/LIFETAB_P733X/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Medion\MdLifetabP733x($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/LIFETAB_P1034X/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Medion\MdLifetabP1034x($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/LIFETAB_S1034X/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Medion\MdLifetabS1034x($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/P4501/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Medion\MdLifeP4501($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/YUANDA50/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Yuanda\Yuanda50($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/Fly IQ4415/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Fly\FlyIq4415($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/Fly IQ449/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Fly\FlyIq449($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/IQ436i/', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Fly\FlyIq436i($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/(CX919|gxt_dongle_3188)/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Tv\AndoerCx919($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/PAP5000TDUO/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Prestigio\PrestigioPap5000tDuo($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/PAP5000DUO/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Prestigio\PrestigioPap5000Duo($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/PAP7600DUO/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Prestigio\PrestigioPap7600Duo($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/PAP4500DUO/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Prestigio\PrestigioPap4500Duo($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/PAP5503/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Prestigio\PrestigioPap5503($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/PMT3037_3G/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Prestigio\PrestigioPmt30373g($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/PMP7074B3GRU/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Prestigio\PrestigioPmp7074b3gru($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/sprd\-B51\+/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Sprd\SprdB51plus($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/BlackBerry 9790/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\BlackBerry\BlackBerry9790($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/BlackBerry 9720/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\BlackBerry\BlackBerry9720($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/BB10; Kbd/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\BlackBerry\BlackBerryKbd($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/BB10; Touch/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\BlackBerry\BlackBerryZ10($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/XT1068/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Motorola\MotorolaXt1068($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/XT1039/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Motorola\MotorolaXt1039($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/XT1032/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Motorola\MotorolaXt1032($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/MotoG3/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Motorola\MotorolaMotoG3($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/nexus 6p/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Huawei\HuaweiNexus6p($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/nexus 6/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Motorola\MotorolaNexus6($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/ME302KL/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Asus\AsusMe302kl($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/nexus 7/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Asus\AsusGalaxyNexus7($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/K013/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Asus\AsusMemoPadK013($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/K012/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Asus\AsusFoneK012($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/ME302C/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Asus\AsusMe302c($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/WIN HD W510u/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Blu\BluWinHdW510u($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/N9500/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Star\StarN9500($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/tolino tab 8\.9/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Tolino\TolinoTab89($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/tolino tab 8/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Tolino\TolinoTab8($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/Lenovo S660/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Lenovo\LenovoS660($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/Lenovo S920/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Lenovo\LenovoS920($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/IdeaTab S6000\-H/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Lenovo\LenovoS6000hIdeaTab($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/Lenovo A606/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Lenovo\LenovoA606($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/Lenovo A850\+/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Lenovo\LenovoA850Plus($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/Lenovo A766/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Lenovo\LenovoA766($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/SmartTabII10/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Lenovo\VodafoneSmartTabIi10($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/P1032X/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Lenovo\LenovoP1032x($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/Lenovo A7000\-a/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Lenovo\LenovoA7000a($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/Lenovo B6000\-HV/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Lenovo\LenovoB6000hv($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/Lenovo B6000\-H/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Lenovo\LenovoB6000h($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/S208/i', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Cubot\CubotS208($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/306SH/', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Sharp\SH306($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/JERRY/', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Wiko\WikoJerry($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/BLOOM/', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Wiko\WikoBloom($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/DARKSIDE/', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Wiko\WikoDarkside($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/ M3 /', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Gionee\GioneeMarathonM3($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/4034D/', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Alcatel\AlcatelOt4034D($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/7041D/', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Alcatel\AlcatelOt7041d($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/6040D/', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Alcatel\AlcatelOt6040D($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/Archos 50b Platinum/', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Archos\Archos50bPlatinum($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/Archos 50 Titanium/', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Archos\Archos50Titanium($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/Archos 50 Oxygen Plus/', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Archos\Archos50OxygenPlus($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/ARCHOS 101 XS 2/', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Archos\Archos101xs2($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/Archos 101d Neon/', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Archos\Archos101dNeon($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/Archos 101 Copper/', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Archos\Archos101Copper($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/ZTE Blade V6/', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Zte\ZteBladev6($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/ZTE Blade L5 Plus/', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Zte\ZteBladeL5plus($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/ZTE Blade L6/', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Zte\ZteBladeL6($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/ZTE N919/', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Zte\ZteN919($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/Beeline Pro/', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Zte\ZteBeelinePro($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/SmartTab7/', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Zte\ZteSmartTab7($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/ZTE_V829/', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Zte\ZteV829($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/ZTE Geek/', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Zte\ZteV975($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/IEOS_QUAD_10_PRO/', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Odys\OdysIeosQuad10pro($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/IEOS_QUAD_W/', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Odys\OdysIeosQuadw($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/MAVEN_10_PLUS/', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Odys\OdysMaven10plus($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/AT300SE/', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Toshiba\ToshibaAt300SE($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/A3\-A11/', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Acer\AcerIconiaA3A11($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/A3\-A10/', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Acer\AcerIconiaA3A10($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/A700/', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Acer\AcerIconiaA700($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/B1\-711/', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Acer\AcerIconiaB1711($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/B1\-770/', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Acer\AcerIconiaB1770($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/MediPaD13/', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Bewatec\BewatecMediPad13($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/MediPaD/', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Bewatec\BewatecMediPad($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/M7T/', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Pipo\PipoM7t3g($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/ORION7o/', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\GoClever\GoCleverOrion7o($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/GOCLEVER TAB A93\.2/', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\GoClever\GoCleverTabA932($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/QUANTUM 4/', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\GoClever\GoCleverQuantum4($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/NT\-1009T/', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\IconBit\IconBitNt1009t($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/Philips W336/', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Philips\PhilipsW336($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/KianoIntelect7/', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Kiano\KianoIntelect7($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/SUPRA_M121G/', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Supra\SupraM121g($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/HW\-W718/', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Haier\HaierW718($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/Micromax A59/', $useragent)) {
        $device = new \BrowserDetector\Detector\Device\Mobile\Micromax\MicromaxA59($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } elseif (preg_match('/CFNetwork/', $useragent)) {
        $device = \BrowserDetector\Detector\Factory\Device\DarwinFactory::detect($useragent);

        $deviceBrand       = $device->getBrand();
        $deviceCode        = $device->getDeviceName();
        $devicePointing    = $device->getPointingMethod();
        $deviceType        = $device->getType()->getName();
        $deviceMaker       = $device->getManufacturer();
        $deviceName        = $device->getMarketingName();
        $deviceOrientation = $device->getDualOrientation();
    } else {
        /** @var \UaResult\Result\Result $result */
        $result = $detector->getBrowser($useragent);

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