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

    $outputDetector  = "<?php\n\nreturn [\n";

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

        if (isset($test['properties']['Platform_Name'])) {
            $platformName = $test['properties']['Platform_Name'];
        } else {
            $platformName = 'unknown';
        }

        if (isset($test['properties']['Platform_Version'])) {
            $platformVersion = $test['properties']['Platform_Version'];
        } else {
            $platformVersion = 'unknown';
        }

        if (isset($test['properties']['Platform_Bits'])) {
            $platformBits = $test['properties']['Platform_Bits'];
        } else {
            $platformBits = 'unknown';
        }

        if (isset($test['properties']['Platform_Maker'])) {
            $platformMaker = $test['properties']['Platform_Maker'];
        } else {
            $platformMaker = 'unknown';
        }

        // rewrite Darwin platform
        if ('Darwin' === $platformName) {
            echo '["' . $key . '"] platform name for UA "' . $test['ua'] . '" was written as Darwin, rewriting', PHP_EOL;

            $platform = \BrowserDetector\Detector\Factory\Platform\DarwinFactory::detect($test['ua']);

            $platformName    = $platform->getName();
            $platformVersion = $platform->getVersion();
            $platformBits    = $platform->getBits();
            $platformMaker   = $platform->getManufacturer();
        } elseif ('Android' === $platformName && preg_match('/windows phone/i', $test['ua'])) {
            echo '["' . $key . '"] platform name for UA "' . $test['ua'] . '" was written as Android, but is mobile windows, rewriting', PHP_EOL;

            $platform = new \BrowserDetector\Detector\Os\WindowsPhoneOs($test['ua']);

            $platformName    = $platform->getName();
            $platformVersion = $platform->getVersion();
            $platformBits    = $platform->getBits();
            $platformMaker   = $platform->getManufacturer();
        } elseif ('Windows' === $platformName && preg_match('/wpdesktop/i', $test['ua'])) {
            echo '["' . $key . '"] platform name for UA "' . $test['ua'] . '" was written as Windows, but is mobile, rewriting', PHP_EOL;

            $platform = new \BrowserDetector\Detector\Os\WindowsPhoneOs($test['ua']);

            $platformName    = $platform->getName();
            $platformVersion = $platform->getVersion();
            $platformBits    = $platform->getBits();
            $platformMaker   = $platform->getManufacturer();
        } elseif ('Linux' === $platformName && preg_match('/Puffin\/[\d\.]+I(T|P)/', $test['ua'])) {
            echo '["' . $key . '"] platform name for UA "' . $test['ua'] . '" was written as Linux, but is iOS, rewriting', PHP_EOL;

            $platform = new \BrowserDetector\Detector\Os\Ios($test['ua']);

            $platformName    = $platform->getName();
            $platformVersion = $platform->getVersion();
            $platformBits    = $platform->getBits();
            $platformMaker   = $platform->getManufacturer();
        } elseif ('Linux' === $platformName && preg_match('/Puffin\/[\d\.]+A(T|P)/', $test['ua'])) {
            echo '["' . $key . '"] platform name for UA "' . $test['ua'] . '" was written as Linux, but is Android, rewriting', PHP_EOL;

            $platform = new \BrowserDetector\Detector\Os\AndroidOs($test['ua']);

            $platformName    = $platform->getName();
            $platformVersion = $platform->getVersion();
            $platformBits    = $platform->getBits();
            $platformMaker   = $platform->getManufacturer();
        } elseif ('Linux' === $platformName && preg_match('/Puffin\/[\d\.]+W(T|P)/', $test['ua'])) {
            echo '["' . $key . '"] platform name for UA "' . $test['ua'] . '" was written as Linux, but is Windows Phone, rewriting', PHP_EOL;

            $platform = new \BrowserDetector\Detector\Os\WindowsPhoneOs($test['ua']);

            $platformName    = $platform->getName();
            $platformVersion = $platform->getVersion();
            $platformBits    = $platform->getBits();
            $platformMaker   = $platform->getManufacturer();
        } elseif ('Linux' === $platformName && preg_match('/kubuntu/i', $test['ua'])) {
            echo '["' . $key . '"] platform name for UA "' . $test['ua'] . '" was written as Linux, but is Kubuntu, rewriting', PHP_EOL;

            $platform = new \BrowserDetector\Detector\Os\Kubuntu($test['ua']);

            $platformName    = $platform->getName();
            $platformVersion = $platform->getVersion();
            $platformBits    = $platform->getBits();
            $platformMaker   = $platform->getManufacturer();
        } elseif ('Linux' === $platformName && preg_match('/ubuntu/i', $test['ua'])) {
            echo '["' . $key . '"] platform name for UA "' . $test['ua'] . '" was written as Linux, but is Ubuntu, rewriting', PHP_EOL;

            $platform = new \BrowserDetector\Detector\Os\Ubuntu($test['ua']);

            $platformName    = $platform->getName();
            $platformVersion = $platform->getVersion();
            $platformBits    = $platform->getBits();
            $platformMaker   = $platform->getManufacturer();
        } elseif ('Linux Smartphone OS (Maemo)' === $platformName && preg_match('/ubuntu/i', $test['ua'])) {
            echo '["' . $key . '"] platform name for UA "' . $test['ua'] . '" was written as Maemo, but is Ubuntu, rewriting', PHP_EOL;

            $platform = new \BrowserDetector\Detector\Os\Ubuntu($test['ua']);

            $platformName    = $platform->getName();
            $platformVersion = $platform->getVersion();
            $platformBits    = $platform->getBits();
            $platformMaker   = $platform->getManufacturer();
        } elseif ('Linux' === $platformName && preg_match('/linux arm/i', $test['ua'])) {
            echo '["' . $key . '"] platform name for UA "' . $test['ua'] . '" was written as Linux, but is Maemo, rewriting', PHP_EOL;

            $platform = new \BrowserDetector\Detector\Os\Maemo($test['ua']);

            $platformName    = $platform->getName();
            $platformVersion = $platform->getVersion();
            $platformBits    = $platform->getBits();
            $platformMaker   = $platform->getManufacturer();
        } elseif ('Linux' === $platformName && preg_match('/HP\-UX/', $test['ua'])) {
            echo '["' . $key . '"] platform name for UA "' . $test['ua'] . '" was written as Linux, but is HP-UX, rewriting', PHP_EOL;

            $platform = new \BrowserDetector\Detector\Os\Hpux($test['ua']);

            $platformName    = $platform->getName();
            $platformVersion = $platform->getVersion();
            $platformBits    = $platform->getBits();
            $platformMaker   = $platform->getManufacturer();
        } elseif ('Windows' === $platformName && preg_match('/windows ce/i', $test['ua'])) {
            echo '["' . $key . '"] platform name for UA "' . $test['ua'] . '" was written as Windows, but is Windows CE, rewriting', PHP_EOL;

            $platform = new \BrowserDetector\Detector\Os\WindowsCe($test['ua']);

            $platformName    = $platform->getName();
            $platformVersion = $platform->getVersion();
            $platformBits    = $platform->getBits();
            $platformMaker   = $platform->getManufacturer();
        } elseif ('Linux' === $platformName && preg_match('/(red hat|redhat)/i', $test['ua'])) {
            echo '["' . $key . '"] platform name for UA "' . $test['ua'] . '" was written as Linux, but is Red Hut, rewriting', PHP_EOL;

            $platform = new \BrowserDetector\Detector\Os\Redhat($test['ua']);

            $platformName    = $platform->getName();
            $platformVersion = $platform->getVersion();
            $platformBits    = $platform->getBits();
            $platformMaker   = $platform->getManufacturer();
        } elseif ('Windows Mobile OS' === $platformName && preg_match('/Windows Mobile; WCE/', $test['ua'])) {
            echo '["' . $key . '"] platform name for UA "' . $test['ua'] . '" was written as Windows Mobile, but is Windows CE, rewriting', PHP_EOL;

            $platform = new \BrowserDetector\Detector\Os\WindowsCe($test['ua']);

            $platformName    = $platform->getName();
            $platformVersion = $platform->getVersion();
            $platformBits    = $platform->getBits();
            $platformMaker   = $platform->getManufacturer();
        } elseif ('Linux' === $platformName && preg_match('/SUSE/', $test['ua'])) {
            echo '["' . $key . '"] platform name for UA "' . $test['ua'] . '" was written as Linux, but is Suse, rewriting', PHP_EOL;

            $platform = new \BrowserDetector\Detector\Os\Suse($test['ua']);

            $platformName    = $platform->getName();
            $platformVersion = $platform->getVersion();
            $platformBits    = $platform->getBits();
            $platformMaker   = $platform->getManufacturer();
        } elseif ('Linux' === $platformName && preg_match('/centos/i', $test['ua'])) {
            echo '["' . $key . '"] platform name for UA "' . $test['ua'] . '" was written as Linux, but is Cent OS, rewriting', PHP_EOL;

            $platform = new \BrowserDetector\Detector\Os\CentOs($test['ua']);

            $platformName    = $platform->getName();
            $platformVersion = $platform->getVersion();
            $platformBits    = $platform->getBits();
            $platformMaker   = $platform->getManufacturer();
        } elseif ('Linux' === $platformName && preg_match('/mint/i', $test['ua'])) {
            echo '["' . $key . '"] platform name for UA "' . $test['ua'] . '" was written as Linux, but is Linux Mint, rewriting', PHP_EOL;

            $platform = new \BrowserDetector\Detector\Os\Mint($test['ua']);

            $platformName    = $platform->getName();
            $platformVersion = $platform->getVersion();
            $platformBits    = $platform->getBits();
            $platformMaker   = $platform->getManufacturer();
        } else {
            $result = $detector->getBrowser($test['ua']);

            if ($platformName === $result->getOs()->getName()) {
                echo '["' . $key . '"] platform name for UA "' . $test['ua'] . '", rewriting platform details', PHP_EOL;

                $detectVersion = $result->getOs()->getVersion();
                $platformBits  = $result->getOs()->getBits();
                $platformMaker = $result->getOs()->getManufacturer();
            }
        }

        if (preg_match('/test\-(\d+)\-(\d+)/', $key, $matches)) {
            $key = 'test-' . sprintf('%1$05d', (int) $matches[1]) . '-' . sprintf('%1$05d', (int) $matches[2]);
        } elseif (preg_match('/test\-(\d+)/', $key, $matches)) {
            $key = 'test-' . sprintf('%1$05d', (int) $matches[1]) . '-00000';
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
            'Platform_Name'           => '" . str_replace(['\\', "'"], ['\\\\', "\\'"], $platformName) . "',
            'Platform_Version'        => '" . str_replace(['\\', "'"], ['\\\\', "\\'"], $platformVersion) . "',
            'Platform_Bits'           => " . str_replace(['\\', "'"], ['\\\\', "\\'"], $platformBits) . ",
            'Platform_Maker'          => '" . str_replace(['\\', "'"], ['\\\\', "\\'"], $platformMaker) . "',
            'Device_Name'             => '" . str_replace(['\\', "'"], ['\\\\', "\\'"], $test['properties']['Device_Name']) . "',
            'Device_Maker'            => '" . str_replace(['\\', "'"], ['\\\\', "\\'"], $test['properties']['Device_Maker']) . "',
            'Device_Type'             => '" . str_replace(['\\', "'"], ['\\\\', "\\'"], $test['properties']['Device_Type']) . "',
            'Device_Pointing_Method'  => '" . str_replace(['\\', "'"], ['\\\\', "\\'"], $test['properties']['Device_Pointing_Method']) . "',
            'Device_Code_Name'        => '" . str_replace(['\\', "'"], ['\\\\', "\\'"], $test['properties']['Device_Code_Name']) . "',
            'Device_Brand_Name'       => '" . str_replace(['\\', "'"], ['\\\\', "\\'"], $test['properties']['Device_Brand_Name']) . "',
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
