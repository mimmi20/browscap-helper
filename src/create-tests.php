<?php
/*******************************************************************************
 * INIT
 ******************************************************************************/
use Symfony\Component\Yaml\Yaml;

ini_set('memory_limit', '3000M');
ini_set('max_execution_time', '-1');
ini_set('max_input_time', '-1');
ini_set('display_errors', 1);
error_reporting(E_ALL);

date_default_timezone_set('Europe/Berlin');

chdir(dirname(__DIR__));

require 'vendor/autoload.php';

ini_set('memory_limit', '-1');

$issue = 994;

/*******************************************************************************
 * loading files
 ******************************************************************************/

$sourceDirectory = 'vendor/browscap/browscap/tests/fixtures/issues/';

$iterator = new \RecursiveDirectoryIterator($sourceDirectory);
$checks   = array();

foreach (new \RecursiveIteratorIterator($iterator) as $file) {
    /** @var $file \SplFileInfo */
    if (!$file->isFile() || $file->getExtension() != 'php') {
        continue;
    }

    $tests = require_once $file->getPathname();

    foreach ($tests as $key => $test) {
        if (isset($data[$key])) {
            throw new \RuntimeException('Test data is duplicated for key "' . $key . '"');
        }

        if (isset($checks[$test['ua']])) {
            throw new \RuntimeException(
                'UA "' . $test['ua'] . '" added more than once, now for key "' . $key . '", before for key "'
                . $checks[$test['ua']] . '"'
            );
        }

        $data[$key]       = $test;
        $checks[$test['ua']] = $key;
    }
}

//var_dump($checks);exit;
if (file_exists('sources/uas-' . $issue . '.txt')) {
    $fileContents = file('sources/uas-' . $issue . '.txt', FILE_SKIP_EMPTY_LINES | FILE_IGNORE_NEW_LINES);
} elseif (file_exists('sources/uas-' . $issue . '.yaml')) {
    $list = Yaml::parse(file_get_contents('sources/uas-' . $issue . '.yaml'));
    $fileContents = [];

    foreach ($list['test_cases'] as $part) {
        $fileContents[] = $part['user_agent_string'];
    }
} else {
    echo "source file not found\n";
    exit;
}

$outputBrowscap = "<?php\n\nreturn [\n";
$outputDetector = "<?php\n\nreturn [\n";
$counter        = 0;

foreach ($fileContents as $i => $ua) {
    if ($i >= 702) {
        //continue;
    }

    $a      = intval(1 + ($i - 702) / 702);
    $numberBrowscap = ($i >= 702 ? chr(65 + intval(($i - 702) / 702)) : '') . ($i >= 26 ? chr(65 + intval(($i - 26 - ($i >= 702 ? (702 * $a - 26) : 0)) / 26)) : '') . chr(65 + ($i % 26));
    $ua     = trim($ua);

    if (isset($checks[$ua])) {
        continue;
    }

    echo "handle useragent $i ...\n";

    $browserName = 'Default Browser';
    $browserType = 'unknown';
    $browserBits = 32;
    $browserMaker = 'unknown';
    $browserVersion = '0.0';

    $platformName = 'unknown';
    $platformVersion = 'unknown';
    $platformBits = 32;
    $platformMaker = 'unknown';

    $engineName = 'unknown';
    $engineVersion = 'unknown';
    $engineMaker = 'unknown';

    $mobileDevice = 'false';
    $applets      = 'false';
    $activex      = 'false';
    $crawler      = 'false';

    $chromeVersion = 0;

    if (false !== strpos($ua, 'Chrome')) {
        if (preg_match('/Chrome\/(\d+\.\d+)/', $ua, $matches)) {
            $chromeVersion = $matches[1];
        }
    }

    if (preg_match('/(WOW64|x86_64|x64|Win64)/', $ua, $matches)) {
        $platformBits = 64;
    }

    if (preg_match('/(x86_64|x64|Win64)/', $ua, $matches)) {
        $browserBits = 64;
    }

    if (false !== strpos($ua, ' U3/')) {
        $engineName = 'U3';
        $engineMaker = 'UC Web';
    } elseif (false !== strpos($ua, ' U2/')) {
        $engineName = 'U2';
        $engineMaker = 'UC Web';
    } elseif (false !== strpos($ua, ' T5/')) {
        $engineName = 'T5';
        $engineMaker = 'Baidu';
    } elseif (false !== strpos($ua, 'AppleWebKit')) {
        if ($chromeVersion >= 28) {
            $engineName = 'Blink';
            $engineMaker = 'Google Inc';
        } else {
            $engineName  = 'WebKit';
            $engineMaker = 'Apple Inc';
            $applets     = 'true';
        }
    } elseif (false !== strpos($ua, 'Presto')) {
        $engineName  = 'Presto';
        $engineMaker = 'Opera Software ASA';
    } elseif (false !== strpos($ua, 'Trident')) {
        $engineName  = 'Trident';
        $engineMaker = 'Microsoft Corporation';
        $applets     = 'true';
        $activex     = 'true';
    } elseif (false !== strpos($ua, 'Gecko')) {
        $engineName  = 'Gecko';
        $engineMaker = 'Mozilla Foundation';
        $applets     = 'true';
    }

    $devices = array(
        '' => array(
            'Device_Name' => 'unknown',
            'Device_Maker' => 'unknown',
            'Device_Type' => 'unknown',
            'Device_Pointing_Method' => 'unknown',
            'Device_Code_Name' => 'unknown',
            'Device_Brand_Name' => 'unknown',
        ),
        'Windows Desktop' => array(
            'Device_Name' => 'Windows Desktop',
            'Device_Maker' => 'Various',
            'Device_Type' => 'Desktop',
            'Device_Pointing_Method' => 'mouse',
            'Device_Code_Name' => 'Windows Desktop',
            'Device_Brand_Name' => 'unknown',
        ),
        'Linux Desktop' => array(
            'Device_Name' => 'Linux Desktop',
            'Device_Maker' => 'Various',
            'Device_Type' => 'Desktop',
            'Device_Pointing_Method' => 'mouse',
            'Device_Code_Name' => 'Linux Desktop',
            'Device_Brand_Name' => 'unknown',
        ),
    );

    $win32    = false;
    $win64    = false;
    $device   = '';
    $lite     = true;
    $standard = true;

    if (false !== strpos($ua, 'Linux; Android')) {
        $platformName = 'Android';
        $platformMaker = 'Google Inc';
        $mobileDevice = 'true';

        if (preg_match('/Linux; Android (\d+\.\d+)/', $ua, $matches)) {
            $platformVersion = $matches[1];
        }
    } elseif (false !== strpos($ua, 'Linux; U; Android')) {
        $platformName = 'Android';
        $platformMaker = 'Google Inc';
        $mobileDevice = 'true';

        if (preg_match('/Linux; U; Android (\d+\.\d+)/', $ua, $matches)) {
            $platformVersion = $matches[1];
        }
    } elseif (false !== strpos($ua, 'U; Adr')) {
        $platformName = 'Android';
        $platformMaker = 'Google Inc';
        $mobileDevice = 'true';

        if (preg_match('/U; Adr (\d+\.\d+)/', $ua, $matches)) {
            $platformVersion = $matches[1];
        }
    } elseif (false !== strpos($ua, 'Android') || false !== strpos($ua, 'MTK')) {
        $platformName = 'Android';
        $platformMaker = 'Google Inc';
        $mobileDevice = 'true';
    } elseif (false !== strpos($ua, 'wds')) {
        $platformName = 'Windows Phone OS';
        $platformMaker = 'Microsoft Corporation';
        $mobileDevice = 'true';

        if (preg_match('/wds (\d+\.\d+)/', $ua, $matches)) {
            $platformVersion = $matches[1];
        }
    } elseif (false !== strpos($ua, 'Windows Phone')) {
        $platformName = 'WinPhone';
        $platformMaker = 'Microsoft Corporation';
        $mobileDevice = 'true';
    } elseif (false !== strpos($ua, 'Tizen')) {
        $platformName = 'Tizen';
        $platformMaker = 'unknown';
        $mobileDevice = 'true';
    } elseif (false !== strpos($ua, 'OpenBSD')) {
        $platformName = 'OpenBSD';
    } elseif (false !== strpos($ua, 'Symbian') || false !== strpos($ua, 'Series 60')) {
        $platformName = 'SymbianOS';
        $platformMaker = 'Symbian Foundation';
        $mobileDevice = 'true';
    } elseif (false !== strpos($ua, 'MIDP')) {
        $platformName = 'JAVA';
        $platformMaker = 'Oracle';
        $mobileDevice = 'true';
    } elseif (false !== strpos($ua, 'Windows NT 10.0')) {
        $platformName = 'Win10';
        $platformVersion = '10.0';
        $platformMaker = 'Microsoft Corporation';
        $mobileDevice = 'false';

        if ($platformBits === 64) {
            $win64 = true;
        } else {
            $win32 = true;
        }

        $device = 'Windows Desktop';
    } elseif (false !== strpos($ua, 'Windows NT 6.4')) {
        $platformName = 'Win10';
        $platformVersion = '6.4';
        $platformMaker = 'Microsoft Corporation';
        $mobileDevice = 'false';

        if ($platformBits === 64) {
            $win64 = true;
        } else {
            $win32 = true;
        }

        $device = 'Windows Desktop';
    } elseif (false !== strpos($ua, 'Windows NT 6.3')) {
        $platformName = 'Win8.1';
        $platformVersion = '6.3';
        $platformMaker = 'Microsoft Corporation';
        $mobileDevice = 'false';

        if ($platformBits === 64) {
            $win64 = true;
        } else {
            $win32 = true;
        }

        $device = 'Windows Desktop';
    } elseif (false !== strpos($ua, 'Windows NT 6.2')) {
        $platformName = 'Win8';
        $platformVersion = '6.2';
        $platformMaker = 'Microsoft Corporation';
        $mobileDevice = 'false';

        if ($platformBits === 64) {
            $win64 = true;
        } else {
            $win32 = true;
        }

        $device = 'Windows Desktop';
    } elseif (false !== strpos($ua, 'Windows NT 6.1')) {
        $platformName = 'Win7';
        $platformVersion = '6.1';
        $platformMaker = 'Microsoft Corporation';
        $mobileDevice = 'false';

        if ($platformBits === 64) {
            $win64 = true;
        } else {
            $win32 = true;
        }

        $device = 'Windows Desktop';
    } elseif (false !== strpos($ua, 'Windows NT 6.0')) {
        $platformName = 'WinVista';
        $platformVersion = '6.0';
        $platformMaker = 'Microsoft Corporation';
        $mobileDevice = 'false';

        if ($platformBits === 64) {
            $win64 = true;
        } else {
            $win32 = true;
        }

        $device = 'Windows Desktop';
    } elseif (false !== strpos($ua, 'Windows NT 5.2')) {
        $platformName = 'WinXP';
        $platformVersion = '5.2';
        $platformMaker = 'Microsoft Corporation';
        $mobileDevice = 'false';

        if ($platformBits === 64) {
            $win64 = true;
        } else {
            $win32 = true;
        }

        $device = 'Windows Desktop';
    } elseif (false !== strpos($ua, 'Windows NT 5.1')) {
        $platformName = 'WinXP';
        $platformVersion = '5.1';
        $platformMaker = 'Microsoft Corporation';
        $mobileDevice = 'false';

        if ($platformBits === 64) {
            $win64 = true;
        } else {
            $win32 = true;
        }

        $device = 'Windows Desktop';
    } elseif (false !== strpos($ua, 'Windows NT 5.0')) {
        $platformName = 'Win2000';
        $platformVersion = '5.0';
        $platformMaker = 'Microsoft Corporation';
        $mobileDevice = 'false';

        if ($platformBits === 64) {
            $win64 = true;
        } else {
            $win32 = true;
        }

        $device = 'Windows Desktop';
        $standard = false;
    } elseif (false !== strpos($ua, 'Windows NT 4.1')) {
        $platformName = 'WinNT';
        $platformVersion = '4.1';
        $platformMaker = 'Microsoft Corporation';
        $mobileDevice = 'false';

        if ($platformBits === 64) {
            $win64 = true;
        } else {
            $win32 = true;
        }

        $device = 'Windows Desktop';
        $standard = false;
    } elseif (false !== strpos($ua, 'Windows NT 4.0')) {
        $platformName = 'WinNT';
        $platformVersion = '4.0';
        $platformMaker = 'Microsoft Corporation';
        $mobileDevice = 'false';

        if ($platformBits === 64) {
            $win64 = true;
        } else {
            $win32 = true;
        }

        $device = 'Windows Desktop';
        $standard = false;
    } elseif (false !== strpos($ua, 'Windows NT 3.5')) {
        $platformName = 'WinNT';
        $platformVersion = '3.5';
        $platformMaker = 'Microsoft Corporation';
        $mobileDevice = 'false';

        if ($platformBits === 64) {
            $win64 = true;
        } else {
            $win32 = true;
        }

        $device = 'Windows Desktop';
        $standard = false;
    } elseif (false !== strpos($ua, 'Windows NT 3.1')) {
        $platformName = 'WinNT';
        $platformVersion = '3.1';
        $platformMaker = 'Microsoft Corporation';
        $mobileDevice = 'false';

        if ($platformBits === 64) {
            $win64 = true;
        } else {
            $win32 = true;
        }

        $device = 'Windows Desktop';
        $standard = false;
    } elseif (false !== strpos($ua, 'Windows NT')) {
        $platformName = 'WinNT';
        $platformVersion = 'unknown';
        $platformMaker = 'Microsoft Corporation';
        $mobileDevice = 'false';

        if ($platformBits === 64) {
            $win64 = true;
        } else {
            $win32 = true;
        }

        $device = 'Windows Desktop';
        $standard = false;
    } elseif (false !== strpos($ua, 'Linux')) {
        $platformName = 'Linux';
        $platformMaker = 'Linux Foundation';
        $mobileDevice = 'false';

        $device = 'Linux Desktop';
    }

    $browserModus = 'unknown';

    if (false !== strpos($ua, 'OPR') && false !== strpos($ua, 'Android')) {
        $browserName = 'Opera Mobile';
        $browserType = 'Browser';
        $browserMaker = 'Opera Software ASA';

        if (preg_match('/OPR\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'Opera Mobi')) {
        $browserName = 'Opera Mobile';
        $browserType = 'Browser';
        $browserMaker = 'Opera Software ASA';

        if (preg_match('/Version\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'OPR')) {
        $browserName = 'Opera';
        $browserType = 'Browser';
        $browserMaker = 'Opera Software ASA';

        if (preg_match('/OPR\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }
    } elseif (false !== strpos($ua, 'Opera')) {
        $browserName = 'Opera';
        $browserType = 'Browser';
        $browserMaker = 'Opera Software ASA';

        if (preg_match('/Version\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        } elseif (preg_match('/Opera\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }
    } elseif (false !== strpos($ua, 'UCBrowser') || false !== strpos($ua, 'UC Browser')) {
        $browserName = 'UC Browser';
        $browserType = 'Browser';
        $browserMaker = 'UC Web';

        if (preg_match('/UCBrowser\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        } elseif (preg_match('/UC Browser(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'iCab')) {
        $browserName = 'iCab';
        $browserType = 'Browser';
        $browserMaker = 'Alexander Clauss';

        if (preg_match('/iCab\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'Lunascape')) {
        $browserName = 'Lunascape';
        $browserType = 'Browser';
        //$browserMaker = 'Alexander Clauss';

        if (preg_match('/Lunascape (\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== stripos($ua, 'midori')) {
        $browserName = 'Midori';
        $browserType = 'Browser';
        //$browserMaker = 'Alexander Clauss';

        if (preg_match('/Midori\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'OmniWeb')) {
        $browserName = 'OmniWeb';
        $browserType = 'Browser';
        //$browserMaker = 'Alexander Clauss';

        if (preg_match('/OmniWeb\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== stripos($ua, 'maxthon') || false !== strpos($ua, 'MyIE2')) {
        $browserName = 'Maxthon';
        $browserType = 'Browser';
        //$browserMaker = 'Alexander Clauss';

        if (preg_match('/maxthon (\d+\.\d+)/i', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'PhantomJS')) {
        $browserName = 'PhantomJS';
        $browserType = 'Browser';
        $browserMaker = 'phantomjs.org';

        if (preg_match('/PhantomJS\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'YaBrowser')) {
        $browserName = 'Yandex Browser';
        $browserType = 'Browser';
        $browserMaker = 'Yandex';

        if (preg_match('/YaBrowser\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'Kamelio')) {
        $browserName = 'Kamelio App';
        $browserType = 'Application';
        $browserMaker = 'Kamelio';

        $lite = false;
    } elseif (false !== strpos($ua, 'FBAV')) {
        $browserName = 'Facebook App';
        $browserType = 'Application';
        $browserMaker = 'Facebook';

        if (preg_match('/FBAV\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'ACHEETAHI')) {
        $browserName = 'CM Browser';
        $browserType = 'Browser';
        $browserMaker = 'Cheetah Mobile';

        $lite = false;
    } elseif (false !== strpos($ua, 'bdbrowser_i18n')) {
        $browserName = 'Baidu Browser';
        $browserType = 'Browser';
        $browserMaker = 'Baidu';

        if (preg_match('/bdbrowser\_i18n\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'bdbrowserhd_i18n')) {
        $browserName = 'Baidu Browser HD';
        $browserType = 'Browser';
        $browserMaker = 'Baidu';

        if (preg_match('/bdbrowserhd\_i18n\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'bdbrowser_mini')) {
        $browserName = 'Baidu Browser Mini';
        $browserType = 'Browser';
        $browserMaker = 'Baidu';

        if (preg_match('/bdbrowser\_mini\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'Puffin')) {
        $browserName = 'Puffin';
        $browserType = 'Browser';
        $browserMaker = 'CloudMosa Inc.';

        if (preg_match('/Puffin\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'SamsungBrowser')) {
        $browserName = 'Samsung Browser';
        $browserType = 'Browser';
        $browserMaker = 'Samsung';

        if (preg_match('/SamsungBrowser\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'Silk')) {
        $browserName = 'Silk';
        $browserType = 'Browser';
        $browserMaker = 'Amazon.com, Inc.';

        if (preg_match('/Silk\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;

        if (false === strpos($ua, 'Android')) {
            $browserModus = 'Desktop Mode';

            $platformName = 'Android';
            $platformMaker = 'Google Inc';
            $mobileDevice = 'true';
        }
    } elseif (false !== strpos($ua, 'coc_coc_browser')) {
        $browserName = 'Coc Coc Browser';
        $browserType = 'Browser';
        $browserMaker = 'Coc Coc Company Limited';

        if (preg_match('/coc_coc_browser\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'NaverMatome')) {
        $browserName = 'NaverMatome';
        $browserType = 'Application';
        $browserMaker = 'Naver';

        if (preg_match('/NaverMatome\-Android\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'Flipboard')) {
        $browserName = 'Flipboard App';
        $browserType = 'Application';
        $browserMaker = 'Flipboard, Inc.';

        if (preg_match('/Flipboard\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'Arora')) {
        $browserName = 'Arora';
        $browserType = 'Browser';
        //$browserMaker = 'Flipboard, Inc.';

        if (preg_match('/Arora\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'Acoo Browser')) {
        $browserName = 'Acoo Browser';
        $browserType = 'Browser';
        //$browserMaker = 'Flipboard, Inc.';

        if (preg_match('/Acoo Browser\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'ABrowse')) {
        $browserName = 'ABrowse';
        $browserType = 'Browser';
        //$browserMaker = 'Flipboard, Inc.';

        if (preg_match('/ABrowse\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'AmigaVoyager')) {
        $browserName = 'AmigaVoyager';
        $browserType = 'Browser';
        //$browserMaker = 'Flipboard, Inc.';

        if (preg_match('/AmigaVoyager\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'Beonex')) {
        $browserName = 'Beonex';
        $browserType = 'Browser';
        //$browserMaker = 'Flipboard, Inc.';

        if (preg_match('/Beonex\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'Stainless')) {
        $browserName = 'Stainless';
        $browserType = 'Browser';
        //$browserMaker = 'Flipboard, Inc.';

        if (preg_match('/Stainless\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'Sundance')) {
        $browserName = 'Sundance';
        $browserType = 'Browser';
        //$browserMaker = 'Flipboard, Inc.';

        if (preg_match('/Sundance\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'Sunrise')) {
        $browserName = 'Sunrise';
        $browserType = 'Browser';
        //$browserMaker = 'Flipboard, Inc.';

        if (preg_match('/Sunrise\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'SunriseBrowser')) {
        $browserName = 'Sunrise';
        $browserType = 'Browser';
        //$browserMaker = 'Flipboard, Inc.';

        if (preg_match('/SunriseBrowser\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'Seznam.cz')) {
        $browserName = 'Seznam Browser';
        $browserType = 'Browser';
        $browserMaker = 'Seznam.cz, a.s.';

        if (preg_match('/Seznam\.cz\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'Aviator')) {
        $browserName = 'WhiteHat Aviator';
        $browserType = 'Browser';
        $browserMaker = 'WhiteHat Security';

        if (preg_match('/Aviator\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'Dragon')) {
        $browserName = 'Dragon';
        $browserType = 'Browser';
        $browserMaker = 'Comodo Group Inc';

        if (preg_match('/Dragon\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'Beamrise')) {
        $browserName = 'Beamrise';
        $browserType = 'Browser';
        $browserMaker = 'Beamrise Team';

        if (preg_match('/Beamrise\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'Diglo')) {
        $browserName = 'Diglo';
        $browserType = 'Browser';
        $browserMaker = 'Diglo Inc';

        if (preg_match('/Diglo\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'APUSBrowser')) {
        $browserName = 'APUSBrowser';
        $browserType = 'Browser';
        $browserMaker = 'APUS-Group';

        if (preg_match('/APUSBrowser\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'Chedot')) {
        $browserName = 'Chedot';
        $browserType = 'Browser';
        $browserMaker = 'Chedot.com';

        if (preg_match('/Chedot\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'Qword')) {
        $browserName = 'Qword Browser';
        $browserType = 'Browser';
        $browserMaker = 'Qword Corporation';

        if (preg_match('/Qword\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'Iridium')) {
        $browserName = 'Iridium Browser';
        $browserType = 'Browser';
        $browserMaker = 'Iridium Browser Team';

        if (preg_match('/Iridium\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'MxNitro')) {
        $browserName = 'Maxthon Nitro';
        $browserType = 'Browser';
        $browserMaker = 'Maxthon International Limited';

        if (preg_match('/MxNitro\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'MxBrowser')) {
        $browserName = 'Maxthon';
        $browserType = 'Browser';
        $browserMaker = 'Maxthon International Limited';

        if (preg_match('/MxBrowser\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'Maxthon')) {
        $browserName = 'Maxthon';
        $browserType = 'Browser';
        $browserMaker = 'Maxthon International Limited';

        if (preg_match('/Maxthon\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'Superbird') || false !== strpos($ua, 'SuperBird')) {
        $browserName = 'SuperBird';
        $browserType = 'Browser';
        $browserMaker = 'superbird-browser.com';

        if (preg_match('/superbird\/(\d+\.\d+)/i', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'TinyBrowser')) {
        $browserName = 'TinyBrowser';
        $browserType = 'Browser';
        $browserMaker = 'unknown';

        if (preg_match('/TinyBrowser\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'Chrome') && false !== strpos($ua, 'Version')) {
        $browserName = 'Android WebView';
        $browserType = 'Browser';
        $browserMaker = 'Google Inc';

        if (preg_match('/Version\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        if ($browserVersion <= 1) {
            $lite = false;
        }
    } elseif (false !== strpos($ua, 'Safari') && false !== strpos($ua, 'Version') && false !== strpos($ua, 'Tizen')) {
        $browserName = 'Samsung WebView';
        $browserType = 'Browser';
        $browserMaker = 'Samsung';

        if (preg_match('/Version\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'Chromium')) {
        $browserName = 'Chromium';
        $browserType = 'Browser';
        $browserMaker = 'Google Inc';

        if (preg_match('/Chromium\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'Flock')) {
        $browserName = 'Flock';
        $browserType = 'Browser';
        //$browserMaker = 'Mozilla Foundation';

        if (preg_match('/Flock\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'Fluid')) {
        $browserName = 'Fluid';
        $browserType = 'Browser';
        //$browserMaker = 'Mozilla Foundation';

        if (preg_match('/Fluid\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'ChromePlus')) {
        $browserName = 'ChromePlus';
        $browserType = 'Browser';
        //$browserMaker = 'Google Inc';

        if (preg_match('/ChromePlus\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'RockMelt')) {
        $browserName = 'RockMelt';
        $browserType = 'Browser';
        //$browserMaker = 'Google Inc';

        if (preg_match('/RockMelt\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'Shiira')) {
        $browserName = 'Shiira';
        $browserType = 'Browser';
        //$browserMaker = 'Google Inc';

        if (preg_match('/Shiira\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'Iron')) {
        $browserName = 'Iron';
        $browserType = 'Browser';
        //$browserMaker = 'Google Inc';

        if (preg_match('/Iron\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'Chrome')) {
        $browserName = 'Chrome';
        $browserType = 'Browser';
        $browserMaker = 'Google Inc';
        $browserVersion = $chromeVersion;

        if ($browserVersion < 30) {
            $lite = false;
        }
    } elseif (false !== strpos($ua, 'Opera Mini')) {
        $browserName = 'Opera Mini';
        $browserType = 'Browser';
        $browserMaker = 'Opera Software ASA';

        if (preg_match('/Opera Mini\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }
    } elseif (false !== strpos($ua, 'FlyFlow')) {
        $browserName = 'FlyFlow';
        $browserType = 'Browser';
        $browserMaker = 'Baidu';

        if (preg_match('/FlyFlow\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'Epiphany') || false !== strpos($ua, 'epiphany')) {
        $browserName = 'Epiphany';
        $browserType = 'Browser';
        //$browserMaker = 'Baidu';

        if (preg_match('/Epiphany\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'Safari') && false !== strpos($ua, 'Version') && false !== strpos($ua, 'Android')) {
        $browserName = 'Android';
        $browserType = 'Browser';
        $browserMaker = 'Google Inc';

        if (preg_match('/Version\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        if ($browserVersion != '4.0') {
            $lite = false;
        }
    } elseif (false !== strpos($ua, 'BlackBerry') && false !== strpos($ua, 'Version')) {
        $browserName = 'BlackBerry';
        $browserType = 'Browser';
        $browserMaker = 'Research In Motion Limited';

        if (preg_match('/Version\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }
        $lite = false;
    } elseif (false !== strpos($ua, 'Safari') && false !== strpos($ua, 'Version')) {
        $browserName = 'Safari';
        $browserType = 'Browser';
        $browserMaker = 'Apple Inc';

        if (preg_match('/Version\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }
    } elseif (false !== strpos($ua, 'PaleMoon')) {
        $browserName = 'PaleMoon';
        $browserType = 'Browser';
        $browserMaker = 'Moonchild Productions';

        if (preg_match('/PaleMoon\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'Phoenix')) {
        $browserName = 'Phoenix';
        $browserType = 'Browser';
        //$browserMaker = 'www.waterfoxproject.org';

        if (preg_match('/Phoenix\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== stripos($ua, 'Prism')) {
        $browserName = 'Prism';
        $browserType = 'Browser';
        //$browserMaker = 'www.waterfoxproject.org';

        if (preg_match('/Prism\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== stripos($ua, 'QtWeb Internet Browser')) {
        $browserName = 'QtWeb Internet Browser';
        $browserType = 'Browser';
        //$browserMaker = 'www.waterfoxproject.org';

        if (preg_match('/QtWeb Internet Browser\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'Waterfox')) {
        $browserName = 'Waterfox';
        $browserType = 'Browser';
        $browserMaker = 'www.waterfoxproject.org';

        if (preg_match('/Waterfox\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'QupZilla')) {
        $browserName = 'QupZilla';
        $browserType = 'Browser';
        $browserMaker = 'David Rosca and Community';

        if (preg_match('/QupZilla\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'Thunderbird')) {
        $browserName = 'Thunderbird';
        $browserType = 'Email Client';
        $browserMaker = 'Mozilla Foundation';

        if (preg_match('/Thunderbird\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'kontact')) {
        $browserName = 'Kontact';
        $browserType = 'Email Client';
        $browserMaker = 'KDE e.V.';

        if (preg_match('/kontact\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'Fennec')) {
        $browserName = 'Fennec';
        $browserType = 'Browser';
        $browserMaker = 'Mozilla Foundation';

        if (preg_match('/Fennec\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'myibrow')) {
        $browserName = 'My Internet Browser';
        $browserType = 'Browser';
        $browserMaker = 'unknown';

        if (preg_match('/myibrow\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'Daumoa')) {
        $browserName = 'Daumoa';
        $browserType = 'Bot/Crawler';
        $browserMaker = 'Daum Communications Corp';
        $crawler      = 'true';

        if (preg_match('/Daumoa (\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'Camino')) {
        $browserName = 'Camino';
        $browserType = 'Browser';
        $browserMaker = 'Mozilla Foundation';

        if (preg_match('/Camino\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'Cheshire')) {
        $browserName = 'Cheshire';
        $browserType = 'Browser';
        //$browserMaker = 'Mozilla Foundation';

        if (preg_match('/Cheshire\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'Classilla')) {
        $browserName = 'Classilla';
        $browserType = 'Browser';
        //$browserMaker = 'Mozilla Foundation';

        //if (preg_match('/Classilla\/(\d+\.\d+)/', $ua, $matches)) {
        //    $browserVersion = $matches[1];
        //}

        $lite = false;
    } elseif (false !== strpos($ua, 'CometBird')) {
        $browserName = 'CometBird';
        $browserType = 'Browser';
        //$browserMaker = 'Mozilla Foundation';

        if (preg_match('/CometBird\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'CometBird')) {
        $browserName = 'CometBird';
        $browserType = 'Browser';
        //$browserMaker = 'Mozilla Foundation';

        if (preg_match('/CometBird\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'EnigmaFox')) {
        $browserName = 'EnigmaFox';
        $browserType = 'Browser';
        //$browserMaker = 'Mozilla Foundation';

        if (preg_match('/EnigmaFox\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'conkeror') || false !== strpos($ua, 'Conkeror')) {
        $browserName = 'Conkeror';
        $browserType = 'Browser';
        $browserMaker = 'Mozilla Foundation';

        if (preg_match('/conkeror\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'Galeon')) {
        $browserName = 'Galeon';
        $browserType = 'Browser';
        //$browserMaker = 'Mozilla Foundation';

        if (preg_match('/Galeon\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'Hana')) {
        $browserName = 'Hana';
        $browserType = 'Browser';
        //$browserMaker = 'Mozilla Foundation';

        if (preg_match('/Hana\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'Iceape')) {
        $browserName = 'Iceape';
        $browserType = 'Browser';
        //$browserMaker = 'Mozilla Foundation';

        if (preg_match('/Iceape\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'IceCat')) {
        $browserName = 'IceCat';
        $browserType = 'Browser';
        //$browserMaker = 'Mozilla Foundation';

        if (preg_match('/IceCat\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'Iceweasel')) {
        $browserName = 'Iceweasel';
        $browserType = 'Browser';
        //$browserMaker = 'Mozilla Foundation';

        if (preg_match('/Iceweasel\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'K-Meleon')) {
        $browserName = 'K-Meleon';
        $browserType = 'Browser';
        //$browserMaker = 'Mozilla Foundation';

        if (preg_match('/K\-Meleon\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'K-Ninja')) {
        $browserName = 'K-Ninja';
        $browserType = 'Browser';
        //$browserMaker = 'Mozilla Foundation';

        if (preg_match('/K\-Ninja\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'Kapiko')) {
        $browserName = 'Kapiko';
        $browserType = 'Browser';
        //$browserMaker = 'Mozilla Foundation';

        if (preg_match('/Kapiko\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'Kazehakase')) {
        $browserName = 'Kazehakase';
        $browserType = 'Browser';
        //$browserMaker = 'Mozilla Foundation';

        if (preg_match('/Kazehakase\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'KMLite')) {
        $browserName = 'KMLite';
        $browserType = 'Browser';
        //$browserMaker = 'Mozilla Foundation';

        if (preg_match('/KMLite\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'lolifox')) {
        $browserName = 'lolifox';
        $browserType = 'Browser';
        //$browserMaker = 'Mozilla Foundation';

        if (preg_match('/lolifox\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'Konqueror')) {
        $browserName = 'Konqueror';
        $browserType = 'Browser';
        //$browserMaker = 'Mozilla Foundation';

        if (preg_match('/Konqueror\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'Leechcraft')) {
        $browserName = 'Leechcraft';
        $browserType = 'Browser';
        //$browserMaker = 'Mozilla Foundation';

        //if (preg_match('/Leechcraft\/(\d+\.\d+)/', $ua, $matches)) {
        //    $browserVersion = $matches[1];
        //}

        $lite = false;
    } elseif (false !== strpos($ua, 'Madfox')) {
        $browserName = 'Madfox';
        $browserType = 'Browser';
        //$browserMaker = 'Mozilla Foundation';

        if (preg_match('/Madfox\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'myibrow')) {
        $browserName = 'myibrow';
        $browserType = 'Browser';
        //$browserMaker = 'Mozilla Foundation';

        if (preg_match('/myibrow\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'Netscape6')) {
        $browserName = 'Netscape';
        $browserType = 'Browser';
        //$browserMaker = 'Mozilla Foundation';

        if (preg_match('/Netscape6\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'Netscape')) {
        $browserName = 'Netscape';
        $browserType = 'Browser';
        //$browserMaker = 'Mozilla Foundation';

        if (preg_match('/Netscape\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'Navigator')) {
        $browserName = 'Netscape Navigator';
        $browserType = 'Browser';
        //$browserMaker = 'Mozilla Foundation';

        if (preg_match('/Navigator\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'Orca')) {
        $browserName = 'Orca';
        $browserType = 'Browser';
        //$browserMaker = 'Mozilla Foundation';

        if (preg_match('/Orca\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'Sylera')) {
        $browserName = 'Sylera';
        $browserType = 'Browser';
        //$browserMaker = 'Mozilla Foundation';

        if (preg_match('/Sylera\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'SeaMonkey')) {
        $browserName = 'SeaMonkey';
        $browserType = 'Browser';
        //$browserMaker = 'Mozilla Foundation';

        if (preg_match('/SeaMonkey\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'Fennec')) {
        $browserName = 'Fennec';
        $browserType = 'Browser';
        $browserMaker = 'Mozilla Foundation';

        if (preg_match('/Fennec\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'GoBrowser')) {
        $browserName = 'GoBrowser';
        $browserType = 'Browser';
        //$browserMaker = 'Mozilla Foundation';

        if (preg_match('/GoBrowser\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'Minimo')) {
        $browserName = 'Minimo';
        $browserType = 'Browser';
        //$browserMaker = 'Mozilla Foundation';

        if (preg_match('/Minimo\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'BonEcho')) {
        $browserName = 'Firefox';
        $browserType = 'Browser';
        $browserMaker = 'Mozilla Foundation';

        if (preg_match('/BonEcho\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        if ($browserVersion < 30) {
            $lite = false;
        }
    } elseif (false !== strpos($ua, 'Shiretoko')) {
        $browserName = 'Firefox';
        $browserType = 'Browser';
        $browserMaker = 'Mozilla Foundation';

        if (preg_match('/Shiretoko\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        if ($browserVersion < 30) {
            $lite = false;
        }
    } elseif (false !== strpos($ua, 'Minefield')) {
        $browserName = 'Firefox';
        $browserType = 'Browser';
        $browserMaker = 'Mozilla Foundation';

        if (preg_match('/Minefield\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        if ($browserVersion < 30) {
            $lite = false;
        }
    } elseif (false !== strpos($ua, 'Namoroka')) {
        $browserName = 'Firefox';
        $browserType = 'Browser';
        $browserMaker = 'Mozilla Foundation';

        if (preg_match('/Namoroka\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        if ($browserVersion < 30) {
            $lite = false;
        }
    } elseif (false !== strpos($ua, 'GranParadiso')) {
        $browserName = 'Firefox';
        $browserType = 'Browser';
        $browserMaker = 'Mozilla Foundation';

        if (preg_match('/GranParadiso\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        if ($browserVersion < 30) {
            $lite = false;
        }
    } elseif (false !== strpos($ua, 'Firebird')) {
        $browserName = 'Firefox';
        $browserType = 'Browser';
        $browserMaker = 'Mozilla Foundation';

        if (preg_match('/Firebird\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        if ($browserVersion < 30) {
            $lite = false;
        }
    } elseif (false !== strpos($ua, 'FxiOS')) {
        $browserName = 'Firefox for iOS';
        $browserType = 'Browser';
        $browserMaker = 'Mozilla Foundation';

        if (preg_match('/FxiOS\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'Browzar')) {
        $browserName = 'Browzar';
        $browserType = 'Browser';
        //$browserMaker = 'Mozilla Foundation';

        $lite = false;
    } elseif (false !== strpos($ua, 'Crazy Browser')) {
        $browserName = 'Crazy Browser';
        $browserType = 'Browser';
        //$browserMaker = 'Mozilla Foundation';

        if (preg_match('/Crazy Browser (\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'GreenBrowser')) {
        $browserName = 'GreenBrowser';
        $browserType = 'Browser';
        //$browserMaker = 'Mozilla Foundation';

        //if (preg_match('/Crazy Browser (\d+\.\d+)/', $ua, $matches)) {
        //    $browserVersion = $matches[1];
        //}

        $lite = false;
    } elseif (false !== strpos($ua, 'KKman')) {
        $browserName = 'KKman';
        $browserType = 'Browser';
        //$browserMaker = 'Mozilla Foundation';

        if (preg_match('/KKman(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'Lobo')) {
        $browserName = 'Lobo';
        $browserType = 'Browser';
        //$browserMaker = 'Mozilla Foundation';

        if (preg_match('/Lobo\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'Sleipnir')) {
        $browserName = 'Sleipnir';
        $browserType = 'Browser';
        //$browserMaker = 'Mozilla Foundation';

        if (preg_match('/Sleipnir\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'SlimBrowser')) {
        $browserName = 'SlimBrowser';
        $browserType = 'Browser';
        //$browserMaker = 'Mozilla Foundation';

        if (preg_match('/SlimBrowser\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'TencentTraveler')) {
        $browserName = 'TencentTraveler';
        $browserType = 'Browser';
        //$browserMaker = 'Mozilla Foundation';

        if (preg_match('/TencentTraveler (\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'TheWorld')) {
        $browserName = 'TheWorld';
        $browserType = 'Browser';
        //$browserMaker = 'Mozilla Foundation';

        if (preg_match('/TheWorld\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'MSIE')) {
        $browserName = 'IE';
        $browserType = 'Browser';
        $browserMaker = 'Microsoft Corporation';

        if (preg_match('/MSIE (\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = true;
    } elseif (false !== strpos($ua, 'SMTBot')) {
        $browserName = 'SMTBot';
        $browserType = 'Bot/Crawler';
        $browserMaker = 'SimilarTech Ltd.';
        $crawler      = 'true';

        if (preg_match('/SMTBot\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'gvfs')) {
        $browserName = 'gvfs';
        $browserType = 'Tool';
        $browserMaker = 'The GNOME Project';

        if (preg_match('/gvfs\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'luakit')) {
        $browserName = 'luakit';
        $browserType = 'Browser';
        $browserMaker = 'Mason Larobina';

        if (preg_match('/WebKitGTK\+\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'Cyberdog')) {
        $browserName = 'Cyberdog';
        $browserType = 'Browser';
        //$browserMaker = 'Mason Larobina';

        if (preg_match('/Cyberdog\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'ELinks')) {
        $browserName = 'ELinks';
        $browserType = 'Browser';
        //$browserMaker = 'Mason Larobina';

        //if (preg_match('/WebKitGTK\+\/(\d+\.\d+)/', $ua, $matches)) {
        //    $browserVersion = $matches[1];
        //}

        $lite = false;
    } elseif (false !== strpos($ua, 'Links')) {
        $browserName = 'Links';
        $browserType = 'Browser';
        //$browserMaker = 'Mason Larobina';

        //if (preg_match('/WebKitGTK\+\/(\d+\.\d+)/', $ua, $matches)) {
        //    $browserVersion = $matches[1];
        //}

        $lite = false;
    } elseif (false !== strpos($ua, 'Galaxy')) {
        $browserName = 'Galaxy';
        $browserType = 'Browser';
        //$browserMaker = 'Mason Larobina';

        if (preg_match('/Galaxy\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'iNet Browser')) {
        $browserName = 'iNet Browser';
        $browserType = 'Browser';
        //$browserMaker = 'Mason Larobina';

        if (preg_match('/iNet Browser (\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'Uzbl')) {
        $browserName = 'Uzbl';
        $browserType = 'Browser';
        //$browserMaker = 'Mason Larobina';

        if (preg_match('/Uzbl (\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'Mozilla')) {
        $browserName = 'Mozilla';
        $browserType = 'Browser';
        //$browserMaker = 'Mason Larobina';

        if (preg_match('/Mozilla\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    }

    $v = explode('.', $browserVersion, 2);
    $maxVersion = $v[0];
    $minVersion = (isset($v[1]) ? $v[1] : '0');

    $outputBrowscap .= "    'issue-$issue-$numberBrowscap' => [
        'ua' => '" . str_replace("'", "\\'", $ua) . "',
        'properties' => [
            'Comment' => 'Default Browser',
            'Browser' => '" . str_replace("'", "\\'", $browserName) . "',
            'Browser_Type' => '$browserType',
            'Browser_Bits' => '$browserBits',
            'Browser_Maker' => '$browserMaker',
            'Browser_Modus' => '$browserModus',
            'Version' => '$browserVersion',
            'MajorVer' => '$maxVersion',
            'MinorVer' => '$minVersion',
            'Platform' => '$platformName',
            'Platform_Version' => '$platformVersion',
            'Platform_Description' => 'unknown',
            'Platform_Bits' => '$platformBits',
            'Platform_Maker' => '$platformMaker',
            'Alpha' => false,
            'Beta' => false,
            'Win16' => false,
            'Win32' => " . ($win32 ? 'true' : 'false') . ",
            'Win64' => " . ($win64 ? 'true' : 'false') . ",
            'Frames' => true,
            'IFrames' => true,
            'Tables' => true,
            'Cookies' => true,
            'BackgroundSounds' => $activex,
            'JavaScript' => true,
            'VBScript' => $activex,
            'JavaApplets' => $applets,
            'ActiveXControls' => $activex,
            'isMobileDevice' => $mobileDevice,
            'isTablet' => false,
            'isSyndicationReader' => false,
            'Crawler' => $crawler,
            'isFake' => false,
            'isAnonymized' => false,
            'isModified' => false,
            'CssVersion' => '0',
            'AolVersion' => '0',
            'Device_Name' => '" . (isset($devices[$device]) ? $devices[$device]['Device_Name'] : 'unknown') . "',
            'Device_Maker' => '" . (isset($devices[$device]) ? $devices[$device]['Device_Maker'] : 'unknown') . "',
            'Device_Type' => '" . (isset($devices[$device]) ? $devices[$device]['Device_Type'] : 'unknown') . "',
            'Device_Pointing_Method' => '" . (isset($devices[$device]) ? $devices[$device]['Device_Pointing_Method'] : 'unknown') . "',
            'Device_Code_Name' => '" . (isset($devices[$device]) ? $devices[$device]['Device_Code_Name'] : 'unknown') . "',
            'Device_Brand_Name' => '" . (isset($devices[$device]) ? $devices[$device]['Device_Brand_Name'] : 'unknown') . "',
            'RenderingEngine_Name' => '$engineName',
            'RenderingEngine_Version' => '$engineVersion',
            'RenderingEngine_Maker' => '$engineMaker',
        ],
        'lite' => " . ($lite ? 'true' : 'false') . ",
        'standard' => " . ($standard ? 'true' : 'false') . ",
    ],\n";

    $outputDetector .= "    'browscap-issue-$issue-test$i' => [
        'ua' => '" . str_replace("'", "\\'", $ua) . "',
        'properties' => [
            'Browser_Name'            => '" . str_replace("'", "\\'", $browserName) . "',
            'Browser_Type'            => '$browserType',
            'Browser_Bits'            => $browserBits,
            'Browser_Maker'           => '$browserMaker',
            'Browser_Modus'           => '$browserModus',
            'Browser_Version'         => '$browserVersion',
            'Platform_Name'           => '$platformName',
            'Platform_Version'        => '$platformVersion',
            'Platform_Bits'           => $platformBits,
            'Platform_Maker'          => '$platformMaker',
            'isMobileDevice'          => $mobileDevice,
            'isTablet'                => false,
            'Crawler'                 => $crawler,
            'Device_Name'             => '" . (isset($devices[$device]) ? $devices[$device]['Device_Name'] : 'unknown') . "',
            'Device_Maker'            => '" . (isset($devices[$device]) ? $devices[$device]['Device_Maker'] : 'unknown') . "',
            'Device_Type'             => '" . (isset($devices[$device]) ? $devices[$device]['Device_Type'] : 'unknown') . "',
            'Device_Pointing_Method'  => '" . (isset($devices[$device]) ? $devices[$device]['Device_Pointing_Method'] : 'unknown') . "',
            'Device_Code_Name'        => '" . (isset($devices[$device]) ? $devices[$device]['Device_Code_Name'] : 'unknown') . "',
            'Device_Brand_Name'       => '" . (isset($devices[$device]) ? $devices[$device]['Device_Brand_Name'] : 'unknown') . "',
            'RenderingEngine_Name'    => '$engineName',
            'RenderingEngine_Version' => '$engineVersion',
            'RenderingEngine_Maker'   => '$engineMaker',
        ],
    ],\n";

    $counter++;

    $checks[$ua] = $i;
}

$outputBrowscap .= "];\n";
$outputDetector .= "];\n";

file_put_contents('results/issue-' . $issue . '.php', $outputBrowscap);
file_put_contents('results/browscap-issue-' . $issue . '.php', $outputDetector);

echo "\nEs wurden $counter Tests exportiert\n";