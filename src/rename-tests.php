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

    $oldname = $file->getBasename('.php');
    $newname = $oldname;

    if (preg_match('/^test\-\d{5}\-\d{5}$/', $oldname, $matches)) {
        continue;
    }

    if (preg_match('/^browscap\-issue\-\d{5}\-\d{5}$/', $oldname, $matches)) {
        continue;
    }

    if (preg_match('/test\-(\d+)\-(\d+)/', $oldname, $matches)) {
        $newname = 'test-' . sprintf('%1$05d', (int) $matches[1]) . '-' . sprintf('%1$05d', (int) $matches[2]);
    } elseif (preg_match('/test\-(\d+)/', $oldname, $matches)) {
        $newname = 'test-' . sprintf('%1$05d', (int) $matches[1]) . '-00000';
    } elseif (preg_match('/browscap\-issue\-(\d+)\-(\d+)/', $oldname, $matches)) {
        $newname = 'browscap-issue-' . sprintf('%1$05d', (int) $matches[1]) . '-' . sprintf('%1$05d', (int) $matches[2]);
    } elseif (preg_match('/browscap\-issue\-(\d+)/', $oldname, $matches)) {
        $newname = 'browscap-issue-' . sprintf('%1$05d', (int) $matches[1]) . '-00000';
    }

    if ($newname === $oldname) {
        continue;
    }

    echo 'renaming file ', $oldname, '.php', ' => ', $newname, '.php', ' ...', PHP_EOL;

    rename($file->getPath() . '/' . $oldname . '.php', $file->getPath() . '/' . $newname . '.php');
}
