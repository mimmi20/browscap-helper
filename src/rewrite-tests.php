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

$checks          = [];
$data            = [];
$sourceDirectory = 'vendor/mimmi20/browser-detector/tests/issues/';

$files = scandir($sourceDirectory, SCANDIR_SORT_ASCENDING);

$logger = new \Monolog\Logger('browser-detector-tests');
$logger->pushHandler(new \Monolog\Handler\NullHandler());

$cache    = new \WurflCache\Adapter\NullStorage();
$detector = new \BrowserDetector\BrowserDetector($cache, $logger);

foreach ($files as $filename) {
    $file = new \SplFileInfo($sourceDirectory . DIRECTORY_SEPARATOR . $filename);

    echo 'checking file ', $file->getBasename(), ' ...', PHP_EOL;

    /** @var $file \SplFileInfo */
    if (!$file->isFile() || $file->getExtension() !== 'php') {
        continue;
    }

    echo 'reading file ', $file->getBasename(), ' ...', PHP_EOL;

    $tests = require_once $file->getPathname();

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

        $platformName    = str_replace("'", "\\'", $test['properties']['Platform_Name']);
        $platformVersion = str_replace("'", "\\'", $test['properties']['Platform_Version']);
        $platformBits    = str_replace("'", "\\'", $test['properties']['Platform_Bits']);
        $platformMaker   = str_replace("'", "\\'", $test['properties']['Platform_Maker']);

        if ('unknown' === $platformName) {
            $result = $detector->getBrowser($key, true);

            $platformName    = $result->getOs()->getName();
            $platformVersion = $result->getOs()->getVersion();
            $platformBits    = $result->getOs()->getBits();
            $platformMaker   = $result->getOs()->getManufacturer();
        }

        $outputDetector .= "    '$key' => [
        'ua'         => '" . str_replace("'", "\\'", $test['ua']) . "',
        'properties' => [
            'Browser_Name'            => '" . str_replace("'", "\\'", $test['properties']['Browser_Name']) . "',
            'Browser_Type'            => '" . str_replace("'", "\\'", $test['properties']['Browser_Type']) . "',
            'Browser_Bits'            => " . str_replace("'", "\\'", $test['properties']['Browser_Bits']) . ",
            'Browser_Maker'           => '" . str_replace("'", "\\'", $test['properties']['Browser_Maker']) . "',
            'Browser_Modus'           => '" . str_replace("'", "\\'", $test['properties']['Browser_Modus']) . "',
            'Browser_Version'         => '" . str_replace("'", "\\'", $test['properties']['Browser_Version']) . "',
            'Platform_Name'           => '" . $platformName . "',
            'Platform_Version'        => '" . $platformVersion . "',
            'Platform_Bits'           => " . $platformBits . ",
            'Platform_Maker'          => '" . $platformMaker . "',
            'Device_Name'             => '" . str_replace("'", "\\'", $test['properties']['Device_Name']) . "',
            'Device_Maker'            => '" . str_replace("'", "\\'", $test['properties']['Device_Maker']) . "',
            'Device_Type'             => '" . str_replace("'", "\\'", $test['properties']['Device_Type']) . "',
            'Device_Pointing_Method'  => '" . str_replace("'", "\\'", $test['properties']['Device_Pointing_Method']) . "',
            'Device_Code_Name'        => '" . str_replace("'", "\\'", $test['properties']['Device_Code_Name']) . "',
            'Device_Brand_Name'       => '" . str_replace("'", "\\'", $test['properties']['Device_Brand_Name']) . "',
            'RenderingEngine_Name'    => '" . str_replace("'", "\\'", $test['properties']['RenderingEngine_Name']) . "',
            'RenderingEngine_Version' => '" . str_replace("'", "\\'", $test['properties']['RenderingEngine_Version']) . "',
            'RenderingEngine_Maker'   => '" . str_replace("'", "\\'", $test['properties']['RenderingEngine_Maker']) . "',
        ],
    ],\n";

    }

    $outputDetector .= "];\n";

    file_put_contents($file->getPathname(), $outputDetector);
}