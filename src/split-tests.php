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

foreach ($files as $filename) {
    $file = new \SplFileInfo($sourceDirectory . DIRECTORY_SEPARATOR . $filename);

    echo 'checking file ', $file->getBasename(), ' ...', PHP_EOL;

    /** @var $file \SplFileInfo */
    if (!$file->isFile() || $file->getExtension() !== 'php') {
        continue;
    }

    echo 'reading file ', $file->getBasename(), ' ...', PHP_EOL;

    $tests  = require_once $file->getPathname();
    $chunks = array_chunk($tests, 100, true);

    if (count($chunks) <= 1) {
        continue;
    }

    $oldname  = $file->getBasename('.php');
    $basename = $oldname;

    echo 'removing file ', $file->getBasename(), ' ...', PHP_EOL;

    //rename($file->getPathname(), $file->getPathname() . '.old');
    unlink($file->getPathname());

    echo 'splitting file ', $file->getBasename(), ' ...', PHP_EOL;

    if (preg_match('/^test\-(\d{5})\-(\d{5})$/', $oldname, $matches)) {
        $basename = 'test-' . sprintf('%1$05d', (int) $matches[1]);
    } elseif (preg_match('/^test\-(\d{5})$/', $oldname, $matches)) {
        $basename = 'test-' . sprintf('%1$05d', (int) $matches[1]);
    } elseif (preg_match('/^test\-(\d+)\-(\d+)$/', $oldname, $matches)) {
        $basename = 'test-' . sprintf('%1$05d', (int) $matches[1]);
    } elseif (preg_match('/^test\-(\d+)$/', $oldname, $matches)) {
        $basename = 'test-' . sprintf('%1$05d', (int) $matches[1]);
    } elseif (preg_match('/^browscap\-issue\-(\d{5})\-(\d{5})$/', $oldname, $matches)) {
        $basename = 'browscap-issue-' . sprintf('%1$05d', (int) $matches[1]);
    } elseif (preg_match('/^browscap\-issue\-(\d{5})$/', $oldname, $matches)) {
        $basename = 'browscap-issue-' . sprintf('%1$05d', (int) $matches[1]);
    } elseif (preg_match('/^browscap\-issue\-(\d+)\-(\d+)$/', $oldname, $matches)) {
        $basename = 'browscap-issue-' . sprintf('%1$05d', (int) $matches[1]);
    } elseif (preg_match('/^browscap\-issue\-(\d+)$/', $oldname, $matches)) {
        $basename = 'browscap-issue-' . sprintf('%1$05d', (int) $matches[1]);
    }

    foreach ($chunks as $chunkId => $chunk) {
        if (!count($chunk)) {
            continue;
        }

        $outputDetector = "<?php\n\nreturn [\n";

        foreach ($chunk as $key => $test) {
            $outputDetector .= "    '$key' => [
        'ua'         => '" . str_replace(['\\', "'"], ['\\\\', "\\'"], $test['ua']) . "',
        'properties' => [
            'Browser_Name'            => '" . str_replace(['\\', "'"], ['\\\\', "\\'"], $test['properties']['Browser_Name']) . "',
            'Browser_Type'            => '" . str_replace(['\\', "'"], ['\\\\', "\\'"], $test['properties']['Browser_Type']) . "',
            'Browser_Bits'            => " . str_replace(['\\', "'"], ['\\\\', "\\'"], $test['properties']['Browser_Bits']) . ",
            'Browser_Maker'           => '" . str_replace(['\\', "'"], ['\\\\', "\\'"], $test['properties']['Browser_Maker']) . "',
            'Browser_Modus'           => '" . str_replace(['\\', "'"], ['\\\\', "\\'"], $test['properties']['Browser_Modus']) . "',
            'Browser_Version'         => '" . str_replace(['\\', "'"], ['\\\\', "\\'"], $test['properties']['Browser_Version']) . "',
            'Platform_Name'           => '" . str_replace(['\\', "'"], ['\\\\', "\\'"], $test['properties']['Platform_Name']) . "',
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
        }

        $outputDetector .= "];\n";

        $chunkNumber = sprintf('%1$05d', (int) $chunkId);

        echo 'writing file ', $basename, '-', $chunkNumber, '.php', ' ...', PHP_EOL;

        file_put_contents(
            $file->getPath() . '/' . $basename . '-' . $chunkNumber . '.php',
            $outputDetector
        );
    }
}
