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

/*******************************************************************************
 * loading files
 ******************************************************************************/

$sourceDirectory = 'vendor/browscap/browscap/tests/fixtures/issues/';
$targetDirectory = 'vendor/mimmi20/browser-detector/tests/issues/';

$checks  = array();
$counter = 0;

$files = scandir($sourceDirectory, SCANDIR_SORT_ASCENDING);

foreach ($files as $filename) {
    $file = new \SplFileInfo($sourceDirectory . DIRECTORY_SEPARATOR . $filename);

    echo 'checking file ', $file->getBasename(), ' ...', PHP_EOL;

    /** @var $file \SplFileInfo */
    if (!$file->isFile() || $file->getExtension() !== 'php') {
        continue;
    }

    echo 'processing ' . $file->getBasename() . ' ...' . "\n";

    $tests = require_once $file->getPathname();

    $output  = "<?php\n\nreturn [\n";

    foreach ($tests as $key => $test) {
        if (isset($data[$key])) {
            echo 'Test data is duplicated for key "' . $key . '"', PHP_EOL;
            continue;
        }

        if (isset($checks[$test['ua']])) {
            echo 'UA "' . $test['ua'] . '" added more than once, now for key "' . $key . '", before for key "'
                . $checks[$test['ua']] . '"', PHP_EOL;
            continue;
        }

        $data[$key]          = $test;
        $checks[$test['ua']] = $key;

        if (file_exists($targetDirectory . 'browscap-' . $file->getFilename())) {
            continue;
        }

        $key = "browscap-$key";

        $output .= "    '$key' => [
        'ua'         => '" . str_replace(['\\', "'"], ['\\\\', "\\'"], $test['ua']) . "',
        'properties' => [
            'Browser_Name'            => '" . str_replace(['\\', "'"], ['\\\\', "\\'"], $test['properties']['Browser']) . "',
            'Browser_Type'            => '" . str_replace(['\\', "'"], ['\\\\', "\\'"], $test['properties']['Browser_Type']) . "',
            'Browser_Bits'            => " . str_replace(['\\', "'"], ['\\\\', "\\'"], $test['properties']['Browser_Bits']) . ",
            'Browser_Maker'           => '" . str_replace(['\\', "'"], ['\\\\', "\\'"], $test['properties']['Browser_Maker']) . "',
            'Browser_Modus'           => '" . str_replace(['\\', "'"], ['\\\\', "\\'"], $test['properties']['Browser_Modus']) . "',
            'Browser_Version'         => '" . str_replace(['\\', "'"], ['\\\\', "\\'"], $test['properties']['Version']) . "',
            'Platform_Name'           => '" . str_replace(['\\', "'"], ['\\\\', "\\'"], $test['properties']['Platform']) . "',
            'Platform_Version'        => '" . str_replace(['\\', "'"], ['\\\\', "\\'"], $test['properties']['Platform_Version']) . "',
            'Platform_Bits'           => " . str_replace(['\\', "'"], ['\\\\', "\\'"], $test['properties']['Platform_Bits']) . ",
            'Platform_Maker'          => '" . str_replace(['\\', "'"], ['\\\\', "\\'"], $test['properties']['Platform_Maker']) . "',
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

        $counter++;
    }

    $output .= "];\n";

    echo 'writing file ', $file->getBasename(), ' ...', PHP_EOL;

    file_put_contents($targetDirectory . 'browscap-' . $file->getFilename(), $output);
}

echo "\nEs wurden $counter Tests exportiert\n";