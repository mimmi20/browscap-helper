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

$cache    = new \Cache\Adapter\Void\VoidCachePool();
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
    if (!$file->isFile() || $file->getExtension() !== 'json') {
        continue;
    }

    echo 'reading file ', $file->getBasename(), ' ...', PHP_EOL;

    $tests  = json_decode(file_get_contents($file->getPathname()));
    $chunks = array_chunk($tests, 100, true);

    if (count($chunks) <= 1) {
        continue;
    }

    handleFile($file, $chunks);
}

function handleFile(\SplFileInfo $file, array $chunks)
{
    $oldname  = $file->getBasename('.json');
    $basename = $oldname;

    echo 'removing file ', $file->getBasename(), ' ...', PHP_EOL;

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

        handleChunk($file, $chunk, $chunkId, $basename);
    }
}

function handleChunk(\SplFileInfo $file, array $chunk, $chunkId, $basename)
{
    $chunkNumber = sprintf('%1$05d', (int) $chunkId);

    echo 'writing file ', $basename, '-', $chunkNumber, '.json', ' ...', PHP_EOL;

    file_put_contents(
        $file->getPath() . '/' . $basename . '-' . $chunkNumber . '.json',
        json_encode($chunk, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
    );
}
