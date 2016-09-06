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

$checks          = [];
$data            = [];
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

        /** rewrite platforms */

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
            echo '["' . $key . '"] platform name for UA "' . $test['ua'] . '" was written as Darwin, rewriting', PHP_EOL;

            $platform = \BrowserDetector\Detector\Factory\Platform\DarwinFactory::detect($test['ua']);

            $platformCodename = $platform->getName();
            $platformMarketingname = $platform->getMarketingName();
            $platformVersion = $platform->getVersion()->getVersion();
            $platformBits    = $platform->getBits();
            $platformMaker   = $platform->getManufacturer();
            $platformBrandname = $platform->getBrand();
        } elseif ('Windows' === $platformCodename) {
            echo '["' . $key . '"] platform name for UA "' . $test['ua'] . '" was written as Windows, rewriting', PHP_EOL;

            $platform = \BrowserDetector\Detector\Factory\Platform\WindowsFactory::detect($test['ua']);

            $platformCodename = $platform->getName();
            $platformMarketingname = $platform->getMarketingName();
            $platformVersion = $platform->getVersion()->getVersion();
            $platformBits    = $platform->getBits();
            $platformMaker   = $platform->getManufacturer();
            $platformBrandname = $platform->getBrand();
        } elseif ('Android' === $platformCodename && preg_match('/windows phone/i', $test['ua'])) {
            echo '["' . $key . '"] platform name for UA "' . $test['ua'] . '" was written as Android, but is mobile windows, rewriting', PHP_EOL;

            $platform = new \BrowserDetector\Detector\Os\WindowsPhoneOs($test['ua']);

            $platformCodename    = $platform->getName();
            $platformMarketingname = $platform->getMarketingName();
            $platformVersion = $platform->getVersion()->getVersion();
            $platformBits    = $platform->getBits();
            $platformMaker   = $platform->getManufacturer();
            $platformBrandname = $platform->getBrand();
        } elseif ('Windows' === $platformCodename && preg_match('/wpdesktop/i', $test['ua'])) {
            echo '["' . $key . '"] platform name for UA "' . $test['ua'] . '" was written as Windows, but is mobile, rewriting', PHP_EOL;

            $platform = new \BrowserDetector\Detector\Os\WindowsPhoneOs($test['ua']);

            $platformCodename    = $platform->getName();
            $platformMarketingname = $platform->getMarketingName();
            $platformVersion = $platform->getVersion()->getVersion();
            $platformBits    = $platform->getBits();
            $platformMaker   = $platform->getManufacturer();
            $platformBrandname = $platform->getBrand();
        } elseif ('Linux' === $platformCodename && preg_match('/Puffin\/[\d\.]+I(T|P)/', $test['ua'])) {
            echo '["' . $key . '"] platform name for UA "' . $test['ua'] . '" was written as Linux, but is iOS, rewriting', PHP_EOL;

            $platform = new \BrowserDetector\Detector\Os\Ios($test['ua']);

            $platformCodename    = $platform->getName();
            $platformMarketingname = $platform->getMarketingName();
            $platformVersion = $platform->getVersion()->getVersion();
            $platformBits    = $platform->getBits();
            $platformMaker   = $platform->getManufacturer();
            $platformBrandname = $platform->getBrand();
        } elseif ('Linux' === $platformCodename && preg_match('/Puffin\/[\d\.]+A(T|P)/', $test['ua'])) {
            echo '["' . $key . '"] platform name for UA "' . $test['ua'] . '" was written as Linux, but is Android, rewriting', PHP_EOL;

            $platform = new \BrowserDetector\Detector\Os\AndroidOs($test['ua']);

            $platformCodename    = $platform->getName();
            $platformMarketingname = $platform->getMarketingName();
            $platformVersion = $platform->getVersion()->getVersion();
            $platformBits    = $platform->getBits();
            $platformMaker   = $platform->getManufacturer();
            $platformBrandname = $platform->getBrand();
        } elseif ('Linux' === $platformCodename && preg_match('/Puffin\/[\d\.]+W(T|P)/', $test['ua'])) {
            echo '["' . $key . '"] platform name for UA "' . $test['ua'] . '" was written as Linux, but is Windows Phone, rewriting', PHP_EOL;

            $platform = new \BrowserDetector\Detector\Os\WindowsPhoneOs($test['ua']);

            $platformCodename    = $platform->getName();
            $platformMarketingname = $platform->getMarketingName();
            $platformVersion = $platform->getVersion()->getVersion();
            $platformBits    = $platform->getBits();
            $platformMaker   = $platform->getManufacturer();
            $platformBrandname = $platform->getBrand();
        } elseif ('Linux' === $platformCodename && preg_match('/kubuntu/i', $test['ua'])) {
            echo '["' . $key . '"] platform name for UA "' . $test['ua'] . '" was written as Linux, but is Kubuntu, rewriting', PHP_EOL;

            $platform = new \BrowserDetector\Detector\Os\Kubuntu($test['ua']);

            $platformCodename    = $platform->getName();
            $platformMarketingname = $platform->getMarketingName();
            $platformVersion = $platform->getVersion()->getVersion();
            $platformBits    = $platform->getBits();
            $platformMaker   = $platform->getManufacturer();
            $platformBrandname = $platform->getBrand();
        } elseif ('Linux' === $platformCodename && preg_match('/ubuntu/i', $test['ua'])) {
            echo '["' . $key . '"] platform name for UA "' . $test['ua'] . '" was written as Linux, but is Ubuntu, rewriting', PHP_EOL;

            $platform = new \BrowserDetector\Detector\Os\Ubuntu($test['ua']);

            $platformCodename    = $platform->getName();
            $platformMarketingname = $platform->getMarketingName();
            $platformVersion = $platform->getVersion()->getVersion();
            $platformBits    = $platform->getBits();
            $platformMaker   = $platform->getManufacturer();
            $platformBrandname = $platform->getBrand();
        } elseif ('Linux Smartphone OS (Maemo)' === $platformCodename && preg_match('/ubuntu/i', $test['ua'])) {
            echo '["' . $key . '"] platform name for UA "' . $test['ua'] . '" was written as Maemo, but is Ubuntu, rewriting', PHP_EOL;

            $platform = new \BrowserDetector\Detector\Os\Ubuntu($test['ua']);

            $platformCodename    = $platform->getName();
            $platformMarketingname = $platform->getMarketingName();
            $platformVersion = $platform->getVersion()->getVersion();
            $platformBits    = $platform->getBits();
            $platformMaker   = $platform->getManufacturer();
            $platformBrandname = $platform->getBrand();
        } elseif ('Linux' === $platformCodename && preg_match('/linux arm/i', $test['ua'])) {
            echo '["' . $key . '"] platform name for UA "' . $test['ua'] . '" was written as Linux, but is Maemo, rewriting', PHP_EOL;

            $platform = new \BrowserDetector\Detector\Os\Maemo($test['ua']);

            $platformCodename    = $platform->getName();
            $platformMarketingname = $platform->getMarketingName();
            $platformVersion = $platform->getVersion()->getVersion();
            $platformBits    = $platform->getBits();
            $platformMaker   = $platform->getManufacturer();
            $platformBrandname = $platform->getBrand();
        } elseif ('Linux' === $platformCodename && preg_match('/HP\-UX/', $test['ua'])) {
            echo '["' . $key . '"] platform name for UA "' . $test['ua'] . '" was written as Linux, but is HP-UX, rewriting', PHP_EOL;

            $platform = new \BrowserDetector\Detector\Os\Hpux($test['ua']);

            $platformCodename    = $platform->getName();
            $platformMarketingname = $platform->getMarketingName();
            $platformVersion = $platform->getVersion()->getVersion();
            $platformBits    = $platform->getBits();
            $platformMaker   = $platform->getManufacturer();
            $platformBrandname = $platform->getBrand();
        } elseif ('Windows' === $platformCodename && preg_match('/windows ce/i', $test['ua'])) {
            echo '["' . $key . '"] platform name for UA "' . $test['ua'] . '" was written as Windows, but is Windows CE, rewriting', PHP_EOL;

            $platform = new \BrowserDetector\Detector\Os\WindowsCe($test['ua']);

            $platformCodename    = $platform->getName();
            $platformMarketingname = $platform->getMarketingName();
            $platformVersion = $platform->getVersion()->getVersion();
            $platformBits    = $platform->getBits();
            $platformMaker   = $platform->getManufacturer();
            $platformBrandname = $platform->getBrand();
        } elseif ('Linux' === $platformCodename && preg_match('/(red hat|redhat)/i', $test['ua'])) {
            echo '["' . $key . '"] platform name for UA "' . $test['ua'] . '" was written as Linux, but is Red Hut, rewriting', PHP_EOL;

            $platform = new \BrowserDetector\Detector\Os\Redhat($test['ua']);

            $platformCodename    = $platform->getName();
            $platformMarketingname = $platform->getMarketingName();
            $platformVersion = $platform->getVersion()->getVersion();
            $platformBits    = $platform->getBits();
            $platformMaker   = $platform->getManufacturer();
            $platformBrandname = $platform->getBrand();
        } elseif ('Windows Mobile OS' === $platformCodename && preg_match('/Windows Mobile; WCE/', $test['ua'])) {
            echo '["' . $key . '"] platform name for UA "' . $test['ua'] . '" was written as Windows Mobile, but is Windows CE, rewriting', PHP_EOL;

            $platform = new \BrowserDetector\Detector\Os\WindowsCe($test['ua']);

            $platformCodename    = $platform->getName();
            $platformMarketingname = $platform->getMarketingName();
            $platformVersion = $platform->getVersion()->getVersion();
            $platformBits    = $platform->getBits();
            $platformMaker   = $platform->getManufacturer();
            $platformBrandname = $platform->getBrand();
        } elseif ('Linux' === $platformCodename && preg_match('/SUSE/', $test['ua'])) {
            echo '["' . $key . '"] platform name for UA "' . $test['ua'] . '" was written as Linux, but is Suse, rewriting', PHP_EOL;

            $platform = new \BrowserDetector\Detector\Os\Suse($test['ua']);

            $platformCodename    = $platform->getName();
            $platformMarketingname = $platform->getMarketingName();
            $platformVersion = $platform->getVersion()->getVersion();
            $platformBits    = $platform->getBits();
            $platformMaker   = $platform->getManufacturer();
            $platformBrandname = $platform->getBrand();
        } elseif ('Linux' === $platformCodename && preg_match('/centos/i', $test['ua'])) {
            echo '["' . $key . '"] platform name for UA "' . $test['ua'] . '" was written as Linux, but is Cent OS, rewriting', PHP_EOL;

            $platform = new \BrowserDetector\Detector\Os\CentOs($test['ua']);

            $platformCodename    = $platform->getName();
            $platformMarketingname = $platform->getMarketingName();
            $platformVersion = $platform->getVersion()->getVersion();
            $platformBits    = $platform->getBits();
            $platformMaker   = $platform->getManufacturer();
            $platformBrandname = $platform->getBrand();
        } elseif ('Linux' === $platformCodename && preg_match('/mint/i', $test['ua'])) {
            echo '["' . $key . '"] platform name for UA "' . $test['ua'] . '" was written as Linux, but is Linux Mint, rewriting', PHP_EOL;

            $platform = new \BrowserDetector\Detector\Os\Mint($test['ua']);

            $platformCodename    = $platform->getName();
            $platformMarketingname = $platform->getMarketingName();
            $platformVersion = $platform->getVersion()->getVersion();
            $platformBits    = $platform->getBits();
            $platformMaker   = $platform->getManufacturer();
            $platformBrandname = $platform->getBrand();
        } else {
            $result = $detector->getBrowser($test['ua']);

            $platform = $result->getOs();

            if ($platformCodename === $platform->getName()) {
                echo '["' . $key . '"] platform name for UA "' . $test['ua'] . ' successful detected", rewriting platform details', PHP_EOL;

                $platformMarketingname = $platform->getMarketingName();
                $platformVersion = $platform->getVersion()->getVersion();
                $platformBits    = $platform->getBits();
                $platformMaker   = $platform->getManufacturer();
                $platformBrandname = $platform->getBrand();
            }
        }

        /** rewrite devices */

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

        if (preg_match('/redmi 3s/i', $test['ua'])) {
            echo '["' . $key . '"] device name for UA "' . $test['ua'] . '" is rewritten', PHP_EOL;

            $device = new \BrowserDetector\Detector\Device\Mobile\Xiaomi\XiaomiRedmi3s($test['ua']);

            $deviceBrand = $device->getBrand();
            $deviceCode = $device->getDeviceName();
            $devicePointing = $device->getPointingMethod();
            $deviceType = $device->getType()->getName();
            $deviceMaker = $device->getManufacturer();
            $deviceName = $device->getMarketingName();
        } elseif (preg_match('/redmi 3/i', $test['ua'])) {
            echo '["' . $key . '"] device name for UA "' . $test['ua'] . '" is rewritten', PHP_EOL;

            $device = new \BrowserDetector\Detector\Device\Mobile\Xiaomi\XiaomiRedmi3($test['ua']);

            $deviceBrand = $device->getBrand();
            $deviceCode = $device->getDeviceName();
            $devicePointing = $device->getPointingMethod();
            $deviceType = $device->getType()->getName();
            $deviceMaker = $device->getManufacturer();
            $deviceName = $device->getMarketingName();
        } elseif (preg_match('/mi max/i', $test['ua'])) {
            echo '["' . $key . '"] device name for UA "' . $test['ua'] . '" is rewritten', PHP_EOL;

            $device = new \BrowserDetector\Detector\Device\Mobile\Xiaomi\XiaomiMiMax($test['ua']);

            $deviceBrand = $device->getBrand();
            $deviceCode = $device->getDeviceName();
            $devicePointing = $device->getPointingMethod();
            $deviceType = $device->getType()->getName();
            $deviceMaker = $device->getManufacturer();
            $deviceName = $device->getMarketingName();
        } elseif (preg_match('/one[_ ]m9plus/i', $test['ua'])) {
            echo '["' . $key . '"] device name for UA "' . $test['ua'] . '" is rewritten', PHP_EOL;

            $device = new \BrowserDetector\Detector\Device\Mobile\Htc\HtcOneM9plus($test['ua']);

            $deviceBrand = $device->getBrand();
            $deviceCode = $device->getDeviceName();
            $devicePointing = $device->getPointingMethod();
            $deviceType = $device->getType()->getName();
            $deviceMaker = $device->getManufacturer();
            $deviceName = $device->getMarketingName();
        } elseif (preg_match('/one[_ ]m9/i', $test['ua'])) {
            echo '["' . $key . '"] device name for UA "' . $test['ua'] . '" is rewritten', PHP_EOL;

            $device = new \BrowserDetector\Detector\Device\Mobile\Htc\HtcOneM9($test['ua']);

            $deviceBrand = $device->getBrand();
            $deviceCode = $device->getDeviceName();
            $devicePointing = $device->getPointingMethod();
            $deviceType = $device->getType()->getName();
            $deviceMaker = $device->getManufacturer();
            $deviceName = $device->getMarketingName();
        } elseif (preg_match('/(rm\-1113|lumia 640 lte)/i', $test['ua'])) {
            echo '["' . $key . '"] device name for UA "' . $test['ua'] . '" is rewritten', PHP_EOL;

            $device = new \BrowserDetector\Detector\Device\Mobile\Nokia\NokiaLumia640lte($test['ua']);

            $deviceBrand = $device->getBrand();
            $deviceCode = $device->getDeviceName();
            $devicePointing = $device->getPointingMethod();
            $deviceType = $device->getType()->getName();
            $deviceMaker = $device->getManufacturer();
            $deviceName = $device->getMarketingName();
        } elseif (preg_match('/rm\-1075/i', $test['ua'])) {
            echo '["' . $key . '"] device name for UA "' . $test['ua'] . '" is rewritten', PHP_EOL;

            $device = new \BrowserDetector\Detector\Device\Mobile\Nokia\NokiaLumia640lteRm1075($test['ua']);

            $deviceBrand = $device->getBrand();
            $deviceCode = $device->getDeviceName();
            $devicePointing = $device->getPointingMethod();
            $deviceType = $device->getType()->getName();
            $deviceMaker = $device->getManufacturer();
            $deviceName = $device->getMarketingName();
        } elseif (preg_match('/rm\-1067/i', $test['ua'])) {
            echo '["' . $key . '"] device name for UA "' . $test['ua'] . '" is rewritten', PHP_EOL;

            $device = new \BrowserDetector\Detector\Device\Mobile\Nokia\NokiaLumia640xlRm1067($test['ua']);

            $deviceBrand = $device->getBrand();
            $deviceCode = $device->getDeviceName();
            $devicePointing = $device->getPointingMethod();
            $deviceType = $device->getType()->getName();
            $deviceMaker = $device->getManufacturer();
            $deviceName = $device->getMarketingName();
        } elseif (preg_match('/rm\-1090/i', $test['ua'])) {
            echo '["' . $key . '"] device name for UA "' . $test['ua'] . '" is rewritten', PHP_EOL;

            $device = new \BrowserDetector\Detector\Device\Mobile\Nokia\NokiaLumia535Rm1090($test['ua']);

            $deviceBrand = $device->getBrand();
            $deviceCode = $device->getDeviceName();
            $devicePointing = $device->getPointingMethod();
            $deviceType = $device->getType()->getName();
            $deviceMaker = $device->getManufacturer();
            $deviceName = $device->getMarketingName();
        } elseif (preg_match('/rm\-994/i', $test['ua'])) {
            echo '["' . $key . '"] device name for UA "' . $test['ua'] . '" is rewritten', PHP_EOL;

            $device = new \BrowserDetector\Detector\Device\Mobile\Nokia\NokiaLumia1320Rm994($test['ua']);

            $deviceBrand = $device->getBrand();
            $deviceCode = $device->getDeviceName();
            $devicePointing = $device->getPointingMethod();
            $deviceType = $device->getType()->getName();
            $deviceMaker = $device->getManufacturer();
            $deviceName = $device->getMarketingName();
        } elseif (preg_match('/rm\-1010/i', $test['ua'])) {
            echo '["' . $key . '"] device name for UA "' . $test['ua'] . '" is rewritten', PHP_EOL;

            $device = new \BrowserDetector\Detector\Device\Mobile\Nokia\NokiaLumia638($test['ua']);

            $deviceBrand = $device->getBrand();
            $deviceCode = $device->getDeviceName();
            $devicePointing = $device->getPointingMethod();
            $deviceType = $device->getType()->getName();
            $deviceMaker = $device->getManufacturer();
            $deviceName = $device->getMarketingName();
        } elseif (preg_match('/L50u/i', $test['ua'])) {
            echo '["' . $key . '"] device name for UA "' . $test['ua'] . '" is rewritten', PHP_EOL;

            $device = new \BrowserDetector\Detector\Device\Mobile\SonyEricsson\SonyL50uExperiaZ2lte($test['ua']);

            $deviceBrand = $device->getBrand();
            $deviceCode = $device->getDeviceName();
            $devicePointing = $device->getPointingMethod();
            $deviceType = $device->getType()->getName();
            $deviceMaker = $device->getManufacturer();
            $deviceName = $device->getMarketingName();
        } elseif (preg_match('/ONEPLUS A3000/i', $test['ua'])) {
            echo '["' . $key . '"] device name for UA "' . $test['ua'] . '" is rewritten', PHP_EOL;

            $device = new \BrowserDetector\Detector\Device\Mobile\Oneplus\OneplusA3000($test['ua']);

            $deviceBrand = $device->getBrand();
            $deviceCode = $device->getDeviceName();
            $devicePointing = $device->getPointingMethod();
            $deviceType = $device->getType()->getName();
            $deviceMaker = $device->getManufacturer();
            $deviceName = $device->getMarketingName();
        } elseif (preg_match('/genm14/i', $test['ua'])) {
            echo '["' . $key . '"] device name for UA "' . $test['ua'] . '" is rewritten', PHP_EOL;

            $device = new \BrowserDetector\Detector\Device\Mobile\Nokia\NokiaXl2($test['ua']);

            $deviceBrand = $device->getBrand();
            $deviceCode = $device->getDeviceName();
            $devicePointing = $device->getPointingMethod();
            $deviceType = $device->getType()->getName();
            $deviceMaker = $device->getManufacturer();
            $deviceName = $device->getMarketingName();
        } elseif (preg_match('/MZ\-MX5/i', $test['ua'])) {
            echo '["' . $key . '"] device name for UA "' . $test['ua'] . '" is rewritten', PHP_EOL;

            $device = new \BrowserDetector\Detector\Device\Mobile\Meizu\MeizuMx5($test['ua']);

            $deviceBrand = $device->getBrand();
            $deviceCode = $device->getDeviceName();
            $devicePointing = $device->getPointingMethod();
            $deviceType = $device->getType()->getName();
            $deviceMaker = $device->getManufacturer();
            $deviceName = $device->getMarketingName();
        } elseif (preg_match('/SM\-G925F/i', $test['ua'])) {
            echo '["' . $key . '"] device name for UA "' . $test['ua'] . '" is rewritten', PHP_EOL;

            $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungSmG925F($test['ua']);

            $deviceBrand = $device->getBrand();
            $deviceCode = $device->getDeviceName();
            $devicePointing = $device->getPointingMethod();
            $deviceType = $device->getType()->getName();
            $deviceMaker = $device->getManufacturer();
            $deviceName = $device->getMarketingName();
        } elseif (preg_match('/(PC36100|EVO 4G)/i', $test['ua'])) {
            echo '["' . $key . '"] device name for UA "' . $test['ua'] . '" is rewritten', PHP_EOL;

            $device = new \BrowserDetector\Detector\Device\Mobile\Htc\HtcEvo4g($test['ua']);

            $deviceBrand = $device->getBrand();
            $deviceCode = $device->getDeviceName();
            $devicePointing = $device->getPointingMethod();
            $deviceType = $device->getType()->getName();
            $deviceMaker = $device->getManufacturer();
            $deviceName = $device->getMarketingName();
        } elseif (preg_match('/Nexus Player/i', $test['ua'])) {
            echo '["' . $key . '"] device name for UA "' . $test['ua'] . '" is rewritten', PHP_EOL;

            $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungGalaxyNexusPlayer($test['ua']);

            $deviceBrand = $device->getBrand();
            $deviceCode = $device->getDeviceName();
            $devicePointing = $device->getPointingMethod();
            $deviceType = $device->getType()->getName();
            $deviceMaker = $device->getManufacturer();
            $deviceName = $device->getMarketingName();
        } elseif (preg_match('/NEO\-X5/i', $test['ua'])) {
            echo '["' . $key . '"] device name for UA "' . $test['ua'] . '" is rewritten', PHP_EOL;

            $device = new \BrowserDetector\Detector\Device\Mobile\Minix\MinixNeoX5($test['ua']);

            $deviceBrand = $device->getBrand();
            $deviceCode = $device->getDeviceName();
            $devicePointing = $device->getPointingMethod();
            $deviceType = $device->getType()->getName();
            $deviceMaker = $device->getManufacturer();
            $deviceName = $device->getMarketingName();
        } elseif (preg_match('/vns\-l31/i', $test['ua'])) {
            echo '["' . $key . '"] device name for UA "' . $test['ua'] . '" is rewritten', PHP_EOL;

            $device = new \BrowserDetector\Detector\Device\Mobile\Huawei\HuaweiVnsL31($test['ua']);

            $deviceBrand = $device->getBrand();
            $deviceCode = $device->getDeviceName();
            $devicePointing = $device->getPointingMethod();
            $deviceType = $device->getType()->getName();
            $deviceMaker = $device->getManufacturer();
            $deviceName = $device->getMarketingName();
        } elseif (preg_match('/F5281/i', $test['ua'])) {
            echo '["' . $key . '"] device name for UA "' . $test['ua'] . '" is rewritten', PHP_EOL;

            $device = new \BrowserDetector\Detector\Device\Mobile\Hisense\HisenseF5281($test['ua']);

            $deviceBrand = $device->getBrand();
            $deviceCode = $device->getDeviceName();
            $devicePointing = $device->getPointingMethod();
            $deviceType = $device->getType()->getName();
            $deviceMaker = $device->getManufacturer();
            $deviceName = $device->getMarketingName();
        } elseif (preg_match('/Aquaris M10/i', $test['ua'])) {
            echo '["' . $key . '"] device name for UA "' . $test['ua'] . '" is rewritten', PHP_EOL;

            $device = new \BrowserDetector\Detector\Device\Mobile\Bq\BqAquarisM10($test['ua']);

            $deviceBrand = $device->getBrand();
            $deviceCode = $device->getDeviceName();
            $devicePointing = $device->getPointingMethod();
            $deviceType = $device->getType()->getName();
            $deviceMaker = $device->getManufacturer();
            $deviceName = $device->getMarketingName();
        } elseif (preg_match('/gt\-i9195i/i', $test['ua'])) {
            echo '["' . $key . '"] device name for UA "' . $test['ua'] . '" is rewritten', PHP_EOL;

            $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungGti9195i($test['ua']);

            $deviceBrand = $device->getBrand();
            $deviceCode = $device->getDeviceName();
            $devicePointing = $device->getPointingMethod();
            $deviceType = $device->getType()->getName();
            $deviceMaker = $device->getManufacturer();
            $deviceName = $device->getMarketingName();
        } elseif (preg_match('/gt\-i9195/i', $test['ua'])) {
            echo '["' . $key . '"] device name for UA "' . $test['ua'] . '" is rewritten', PHP_EOL;

            $device = new \BrowserDetector\Detector\Device\Mobile\Samsung\SamsungGti9195($test['ua']);

            $deviceBrand = $device->getBrand();
            $deviceCode = $device->getDeviceName();
            $devicePointing = $device->getPointingMethod();
            $deviceType = $device->getType()->getName();
            $deviceMaker = $device->getManufacturer();
            $deviceName = $device->getMarketingName();
        } elseif (preg_match('/CFNetwork/', $test['ua'])) {
            echo '["' . $key . '"] device name for UA "' . $test['ua'] . '" is rewritten', PHP_EOL;

            $device = \BrowserDetector\Detector\Factory\Device\DarwinFactory::detect($test['ua']);

            $deviceBrand = $device->getBrand();
            $deviceCode = $device->getDeviceName();
            $devicePointing = $device->getPointingMethod();
            $deviceType = $device->getType()->getName();
            $deviceMaker = $device->getManufacturer();
            $deviceName = $device->getMarketingName();
        } elseif (preg_match('/Slate 17/i', $test['ua'])) {
            echo '["' . $key . '"] device name for UA "' . $test['ua'] . '" is rewritten', PHP_EOL;

            $device = new \BrowserDetector\Detector\Device\Mobile\Hp\HpSlate17($test['ua']);

            $deviceBrand = $device->getBrand();
            $deviceCode = $device->getDeviceName();
            $devicePointing = $device->getPointingMethod();
            $deviceType = $device->getType()->getName();
            $deviceMaker = $device->getManufacturer();
            $deviceName = $device->getMarketingName();
        } elseif (preg_match('/H345/i', $test['ua'])) {
            echo '["' . $key . '"] device name for UA "' . $test['ua'] . '" is rewritten', PHP_EOL;

            $device = new \BrowserDetector\Detector\Device\Mobile\Lg\LgH345($test['ua']);

            $deviceBrand = $device->getBrand();
            $deviceCode = $device->getDeviceName();
            $devicePointing = $device->getPointingMethod();
            $deviceType = $device->getType()->getName();
            $deviceMaker = $device->getManufacturer();
            $deviceName = $device->getMarketingName();
        } else {
            $result = $detector->getBrowser($test['ua']);

            $device = $result->getDevice();

            if ($deviceCode === $device->getDeviceName()) {
                echo '["' . $key . '"] device name for UA "' . $test['ua'] . '" was detected successful, rewriting device details', PHP_EOL;

                $deviceBrand = $device->getBrand();
                $devicePointing = $device->getPointingMethod();
                $deviceType = $device->getType()->getName();
                $deviceMaker = $device->getManufacturer();
                $deviceName = $device->getMarketingName();
            }
        }

        /** rewrite test numbers */

        if (preg_match('/^test\-(\d+)\-(\d+)$/', $key, $matches)) {
            $key = 'test-' . sprintf('%1$05d', (int) $matches[1]) . '-' . sprintf('%1$05d', (int) $matches[2]);
        } elseif (preg_match('/^test\-(\d+)$/', $key, $matches)) {
            $key = 'test-' . sprintf('%1$05d', (int) $matches[1]) . '-00000';
        } elseif (preg_match('/^test\-(\d+)\-test(\d+)$/', $key, $matches)) {
            $key = 'test-' . sprintf('%1$05d', (int) $matches[1]) . '-' . sprintf('%1$05d', (int) $matches[2]);
        }

        $outputDetector .= "    '$key' => [
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
            'Device_Type'             => '" . str_replace(['\\', "'"], ['\\\\', "\\'"], $deviceType) . "',
            'Device_Pointing_Method'  => '" . str_replace(['\\', "'"], ['\\\\', "\\'"], $devicePointing) . "',
            'Device_Code_Name'        => '" . str_replace(['\\', "'"], ['\\\\', "\\'"], $deviceCode) . "',
            'Device_Brand_Name'       => '" . str_replace(['\\', "'"], ['\\\\', "\\'"], $deviceBrand) . "',
            'RenderingEngine_Name'    => '" . str_replace(['\\', "'"], ['\\\\', "\\'"], $test['properties']['RenderingEngine_Name']) . "',
            'RenderingEngine_Version' => '" . str_replace(['\\', "'"], ['\\\\', "\\'"], $test['properties']['RenderingEngine_Version']) . "',
            'RenderingEngine_Maker'   => '" . str_replace(['\\', "'"], ['\\\\', "\\'"], $test['properties']['RenderingEngine_Maker']) . "',
        ],
    ],\n";
    }

    $outputDetector .= "];\n";

    $basename = $file->getBasename();

    echo 'writing file ', $basename, ' ...', PHP_EOL;

    file_put_contents($file->getPath() . '/' . $basename, $outputDetector);
}
