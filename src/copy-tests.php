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
$targetDirectory = 'vendor/mimmi20/browser-detector/tests/issues/00000-browscap/';

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

    $oldname = $file->getBasename('.php');
    $newname = 'browscap-' . $oldname;

    if (preg_match('/issue\-(\d+)/', $oldname, $matches)) {
        $newname = sprintf('browscap-issue-%1$05d', (int) $matches[1]);
    }

    $tests = require_once $file->getPathname();

    if (empty($tests)) {
        continue;
    }

    handleTests($tests, $newname, $targetDirectory, $counter);
}

echo "\nEs wurden $counter Tests exportiert\n";

function handleTests(array $tests, $newname, $targetDirectory, &$counter)
{
    $chunks = array_chunk($tests, 100, true);

    foreach ($chunks as $chunkId => $chunk) {
        if (!count($chunk)) {
            continue;
        }

        $targetFilename = $newname . '-' . sprintf('%1$05d', (int) $chunkId) . '.json';

        if (file_exists($targetDirectory . $targetFilename)) {
            continue;
        }

        handleChunk($chunk, $targetFilename, $targetDirectory, $counter);
    }
}

function handleChunk(array $chunk, $targetFilename, $targetDirectory, &$counter)
{
    $data = [];
    
    foreach ($chunk as $key => $test) {
        if (isset($test['properties']['Platform'])) {
            $platform = $test['properties']['Platform'];
        } else {
            $platform = 'unknown';
        }

        if (isset($test['properties']['Platform_Version'])) {
            $version = $test['properties']['Platform_Version'];
        } else {
            $version = '0.0.0';
        }

        $codename      = $platform;
        $marketingname = $platform;

        switch ($platform) {
            case 'Win10':
                if ('10.0' === $version) {
                    $codename      = 'Windows NT 10.0';
                    $marketingname = 'Windows 10';
                } else {
                    $codename      = 'Windows NT 6.4';
                    $marketingname = 'Windows 10';
                }
                $version = '0.0.0';
                break;
            case 'Win8.1':
                $codename      = 'Windows NT 6.3';
                $marketingname = 'Windows 8.1';
                $version       = '0.0.0';
                break;
            case 'Win8':
                $codename      = 'Windows NT 6.2';
                $marketingname = 'Windows 8';
                $version       = '0.0.0';
                break;
            case 'Win7':
                $codename      = 'Windows NT 6.1';
                $marketingname = 'Windows 7';
                $version       = '0.0.0';
                break;
            case 'WinVista':
                $codename      = 'Windows NT 6.0';
                $marketingname = 'Windows Vista';
                $version       = '0.0.0';
                break;
            case 'WinXP':
                if ('5.2' === $version) {
                    $codename      = 'Windows NT 5.2';
                    $marketingname = 'Windows XP';
                } else {
                    $codename      = 'Windows NT 5.1';
                    $marketingname = 'Windows XP';
                }
                $version = '0.0.0';
                break;
            case 'Win2000':
                $codename      = 'Windows NT 5.0';
                $marketingname = 'Windows 2000';
                $version       = '0.0.0';
                break;
            case 'WinME':
                $codename      = 'Windows ME';
                $marketingname = 'Windows ME';
                $version       = '0.0.0';
                break;
            case 'Win98':
                $codename      = 'Windows 98';
                $marketingname = 'Windows 98';
                $version       = '0.0.0';
                break;
            case 'Win95':
                $codename      = 'Windows 95';
                $marketingname = 'Windows 95';
                $version       = '0.0.0';
                break;
            case 'Win3.1':
                $codename      = 'Windows 3.1';
                $marketingname = 'Windows 3.1';
                $version       = '0.0.0';
                break;
            case 'WinPhone10':
                $codename      = 'Windows Phone OS';
                $marketingname = 'Windows Phone OS';
                $version       = '10.0.0';
                break;
            case 'WinPhone8.1':
                $codename      = 'Windows Phone OS';
                $marketingname = 'Windows Phone OS';
                $version       = '8.1.0';
                break;
            case 'WinPhone8':
                $codename      = 'Windows Phone OS';
                $marketingname = 'Windows Phone OS';
                $version       = '8.0.0';
                break;
            case 'Win32':
                $codename      = 'Windows';
                $marketingname = 'Windows';
                $version       = '0.0.0';
                break;
            case 'WinNT':
                if ('4.0' === $version) {
                    $codename      = 'Windows NT 4.0';
                    $marketingname = 'Windows NT';
                } elseif ('4.1' === $version) {
                    $codename      = 'Windows NT 4.1';
                    $marketingname = 'Windows NT';
                } elseif ('3.5' === $version) {
                    $codename      = 'Windows NT 3.5';
                    $marketingname = 'Windows NT';
                } elseif ('3.1' === $version) {
                    $codename      = 'Windows NT 3.1';
                    $marketingname = 'Windows NT';
                } else {
                    $codename      = 'Windows NT';
                    $marketingname = 'Windows NT';
                }
                $version = '0.0.0';
                break;
            case 'MacOSX':
                $codename      = 'Mac OS X';
                $marketingname = 'Mac OS X';
                break;
        }
        
        $data['browscap-' . $key] = [
            'ua' => $test['ua'],
            'properties' => [
                'Browser_Name'            => $test['properties']['Browser'],
                'Browser_Type'            => $test['properties']['Browser_Type'],
                'Browser_Bits'            => $test['properties']['Browser_Bits'],
                'Browser_Maker'           => $test['properties']['Browser_Maker'],
                'Browser_Modus'           => $test['properties']['Browser_Modus'],
                'Browser_Version'         => $test['properties']['Version'],
                'Platform_Codename'       => $codename,
                'Platform_Marketingname'  => $marketingname,
                'Platform_Version'        => $version,
                'Platform_Bits'           => $test['properties']['Platform_Bits'],
                'Platform_Maker'          => $test['properties']['Platform_Maker'],
                'Platform_Brand_Name'     => $test['properties']['Platform_Maker'],
                'Device_Name'             => $test['properties']['Device_Name'],
                'Device_Maker'            => $test['properties']['Device_Maker'],
                'Device_Type'             => $test['properties']['Device_Type'],
                'Device_Pointing_Method'  => $test['properties']['Device_Pointing_Method'],
                'Device_Dual_Orientation' => false,
                'Device_Code_Name'        => $test['properties']['Device_Code_Name'],
                'Device_Brand_Name'       => $test['properties']['Device_Brand_Name'],
                'RenderingEngine_Name'    => $test['properties']['RenderingEngine_Name'],
                'RenderingEngine_Version' => $test['properties']['RenderingEngine_Version'],
                'RenderingEngine_Maker'   => $test['properties']['RenderingEngine_Maker'],
            ],
        ];

        ++$counter;
    }

    echo 'writing file ', $targetFilename, ' ...', PHP_EOL;

    file_put_contents(
        $targetDirectory . $targetFilename,
        json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
    );
}
