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

$files = scandir($sourceDirectory, SCANDIR_SORT_ASCENDING);

foreach ($files as $filename) {
    $file = new \SplFileInfo($sourceDirectory . DIRECTORY_SEPARATOR . $filename);

    echo 'checking file ', $file->getBasename(), ' ...', PHP_EOL;

    /** @var $file \SplFileInfo */
    if (!$file->isFile() || $file->getExtension() !== 'php') {
        continue;
    }

    $oldname = $file->getBasename('.php');
    $newname = $oldname;

    if (preg_match('/test\-(\d+)/', $oldname, $matches)) {
        $newname = sprintf('test-%1$05d', (int) $matches[1]);
    } elseif (preg_match('/browscap\-issue\-(\d+)/', $oldname, $matches)) {
        $newname = sprintf('browscap-issue-%1$05d', (int) $matches[1]);
    }

    if ($newname === $oldname) {
        continue;
    }

    echo 'renaming file ', $oldname, '.php', ' => ', $newname, '.php', ' ...', PHP_EOL;

    rename($file->getPath() . '/' . $oldname . '.php', $file->getPath() . '/' . $newname . '.php');
}
