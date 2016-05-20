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
            continue;
        }

        if (isset($checks[$test['ua']])) {
            continue;
        }

        $data[$key]       = $test;
        $checks[$test['ua']] = $key;
    }
}

$sourceDirectory = 'vendor/mimmi20/browser-detector/tests/issues/';

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
            continue;
        }

        if (isset($checks[$test['ua']])) {
            continue;
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

    $browserNameBrowscap = 'Default Browser';
    $browserType = 'unknown';
    $browserBits = 32;
    $browserMaker = 'unknown';
    $browserVersion = '0.0';

    $platformNameBrowscap = 'unknown';
    $platformNameDetector = 'unknown';
    $platformVersionBrowscap = 'unknown';
    $platformVersionDetector = 'unknown';
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
        $platformNameBrowscap = 'Android';
        $platformNameDetector = 'Android';
        $platformMaker = 'Google Inc';
        $mobileDevice = 'true';

        if (preg_match('/Linux; Android (\d+\.\d+)/', $ua, $matches)) {
            $platformVersionBrowscap = $matches[1];
            $platformVersionDetector = $matches[1];
        }
    } elseif (false !== strpos($ua, 'Linux; U; Android')) {
        $platformNameBrowscap = 'Android';
        $platformNameDetector = 'Android';
        $platformMaker = 'Google Inc';
        $mobileDevice = 'true';

        if (preg_match('/Linux; U; Android (\d+\.\d+)/', $ua, $matches)) {
            $platformVersionBrowscap = $matches[1];
            $platformVersionDetector = $matches[1];
        }
    } elseif (false !== strpos($ua, 'U; Adr')) {
        $platformNameBrowscap = 'Android';
        $platformNameDetector = 'Android';
        $platformMaker = 'Google Inc';
        $mobileDevice = 'true';

        if (preg_match('/U; Adr (\d+\.\d+)/', $ua, $matches)) {
            $platformVersionBrowscap = $matches[1];
            $platformVersionDetector = $matches[1];
        }
    } elseif (false !== strpos($ua, 'Android') || false !== strpos($ua, 'MTK')) {
        $platformNameBrowscap = 'Android';
        $platformNameDetector = 'Android';
        $platformMaker = 'Google Inc';
        $mobileDevice = 'true';
    } elseif (false !== strpos($ua, 'wds')) {
        $platformNameBrowscap = 'Windows Phone OS';
        $platformNameDetector = 'Windows Phone OS';
        $platformMaker = 'Microsoft Corporation';
        $mobileDevice = 'true';

        if (preg_match('/wds (\d+\.\d+)/', $ua, $matches)) {
            $platformVersionBrowscap = $matches[1];
            $platformVersionDetector = $matches[1];
        }
    } elseif (false !== strpos($ua, 'Windows Phone')) {
        $platformNameBrowscap = 'WinPhone';
        $platformNameDetector = 'Windows Phone OS';
        $platformMaker = 'Microsoft Corporation';
        $mobileDevice = 'true';
    } elseif (false !== strpos($ua, 'Tizen')) {
        $platformNameBrowscap = 'Tizen';
        $platformNameDetector = 'Tizen';
        $platformMaker = 'unknown';
        $mobileDevice = 'true';
    } elseif (false !== strpos($ua, 'OpenBSD')) {
        $platformNameBrowscap = 'OpenBSD';
        $platformNameDetector = 'OpenBSD';
    } elseif (false !== strpos($ua, 'Symbian') || false !== strpos($ua, 'Series 60')) {
        $platformNameBrowscap = 'SymbianOS';
        $platformNameDetector = 'Symbian OS';
        $platformMaker = 'Symbian Foundation';
        $mobileDevice = 'true';
    } elseif (false !== strpos($ua, 'MIDP')) {
        $platformNameBrowscap = 'JAVA';
        $platformNameDetector = 'Java';
        $platformMaker = 'Oracle';
        $mobileDevice = 'true';
    } elseif (false !== strpos($ua, 'Windows NT 10.0')) {
        $platformNameBrowscap = 'Win10';
        $platformNameDetector = 'Windows';
        $platformVersionBrowscap = '10.0';
        $platformVersionDetector = '10';
        $platformMaker = 'Microsoft Corporation';
        $mobileDevice = 'false';

        if ($platformBits === 64) {
            $win64 = true;
        } else {
            $win32 = true;
        }

        $device = 'Windows Desktop';
    } elseif (false !== strpos($ua, 'Windows NT 6.4')) {
        $platformNameBrowscap = 'Win10';
        $platformNameDetector = 'Windows';
        $platformVersionBrowscap = '6.4';
        $platformVersionDetector = '10';
        $platformMaker = 'Microsoft Corporation';
        $mobileDevice = 'false';

        if ($platformBits === 64) {
            $win64 = true;
        } else {
            $win32 = true;
        }

        $device = 'Windows Desktop';
    } elseif (false !== strpos($ua, 'Windows NT 6.3')) {
        $platformNameBrowscap = 'Win8.1';
        $platformNameDetector = 'Windows';
        $platformVersionBrowscap = '6.3';
        $platformVersionDetector = '8.1';
        $platformMaker = 'Microsoft Corporation';
        $mobileDevice = 'false';

        if ($platformBits === 64) {
            $win64 = true;
        } else {
            $win32 = true;
        }

        $device = 'Windows Desktop';
    } elseif (false !== strpos($ua, 'Windows NT 6.2')) {
        $platformNameBrowscap = 'Win8';
        $platformNameDetector = 'Windows';
        $platformVersionBrowscap = '6.2';
        $platformVersionDetector = '8';
        $platformMaker = 'Microsoft Corporation';
        $mobileDevice = 'false';

        if ($platformBits === 64) {
            $win64 = true;
        } else {
            $win32 = true;
        }

        $device = 'Windows Desktop';
    } elseif (false !== strpos($ua, 'Windows NT 6.1')) {
        $platformNameBrowscap = 'Win7';
        $platformNameDetector = 'Windows';
        $platformVersionBrowscap = '6.1';
        $platformVersionDetector = '7';
        $platformMaker = 'Microsoft Corporation';
        $mobileDevice = 'false';

        if ($platformBits === 64) {
            $win64 = true;
        } else {
            $win32 = true;
        }

        $device = 'Windows Desktop';
    } elseif (false !== strpos($ua, 'Windows NT 6.0')) {
        $platformNameBrowscap = 'WinVista';
        $platformNameDetector = 'Windows';
        $platformVersionBrowscap = '6.0';
        $platformVersionDetector = 'Vista';
        $platformMaker = 'Microsoft Corporation';
        $mobileDevice = 'false';

        if ($platformBits === 64) {
            $win64 = true;
        } else {
            $win32 = true;
        }

        $device = 'Windows Desktop';
    } elseif (false !== strpos($ua, 'Windows NT 5.2')) {
        $platformNameBrowscap = 'WinXP';
        $platformNameDetector = 'Windows';
        $platformVersionBrowscap = '5.2';
        $platformVersionDetector = 'XP';
        $platformMaker = 'Microsoft Corporation';
        $mobileDevice = 'false';

        if ($platformBits === 64) {
            $win64 = true;
        } else {
            $win32 = true;
        }

        $device = 'Windows Desktop';
    } elseif (false !== strpos($ua, 'Windows NT 5.1')) {
        $platformNameBrowscap = 'WinXP';
        $platformNameDetector = 'Windows';
        $platformVersionBrowscap = '5.1';
        $platformVersionDetector = 'XP';
        $platformMaker = 'Microsoft Corporation';
        $mobileDevice = 'false';

        if ($platformBits === 64) {
            $win64 = true;
        } else {
            $win32 = true;
        }

        $device = 'Windows Desktop';
    } elseif (false !== strpos($ua, 'Windows NT 5.0')) {
        $platformNameBrowscap = 'Win2000';
        $platformNameDetector = 'Windows';
        $platformVersionBrowscap = '5.0';
        $platformVersionDetector = '2000';
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
        $platformNameBrowscap = 'WinNT';
        $platformNameDetector = 'Windows';
        $platformVersionBrowscap = '4.1';
        $platformVersionDetector = 'NT 4.1';
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
        $platformNameBrowscap = 'WinNT';
        $platformNameDetector = 'Windows';
        $platformVersionBrowscap = '4.0';
        $platformVersionDetector = 'NT 4';
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
        $platformNameBrowscap = 'WinNT';
        $platformNameDetector = 'Windows';
        $platformVersionBrowscap = '3.5';
        $platformVersionDetector = 'NT 3.5';
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
        $platformNameBrowscap = 'WinNT';
        $platformNameDetector = 'Windows';
        $platformVersionBrowscap = '3.1';
        $platformVersionDetector = 'NT 3.1';
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
        $platformNameBrowscap = 'WinNT';
        $platformNameDetector = 'Windows';
        $platformVersionBrowscap = 'unknown';
        $platformVersionDetector = 'NT';
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
        $platformNameBrowscap = 'Linux';
        $platformNameDetector = 'Linux';
        $platformMaker = 'Linux Foundation';
        $mobileDevice = 'false';

        $device = 'Linux Desktop';
    }

    $browserModus = 'unknown';

    if (false !== strpos($ua, 'OPR') && false !== strpos($ua, 'Android')) {
        $browserNameBrowscap = 'Opera Mobile';
        $browserType = 'Browser';
        $browserMaker = 'Opera Software ASA';

        if (preg_match('/OPR\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'Opera Mobi')) {
        $browserNameBrowscap = 'Opera Mobile';
        $browserType = 'Browser';
        $browserMaker = 'Opera Software ASA';

        if (preg_match('/Version\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'OPR')) {
        $browserNameBrowscap = 'Opera';
        $browserType = 'Browser';
        $browserMaker = 'Opera Software ASA';

        if (preg_match('/OPR\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }
    } elseif (false !== strpos($ua, 'Opera')) {
        $browserNameBrowscap = 'Opera';
        $browserType = 'Browser';
        $browserMaker = 'Opera Software ASA';

        if (preg_match('/Version\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        } elseif (preg_match('/Opera\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }
    } elseif (false !== strpos($ua, 'UCBrowser') || false !== strpos($ua, 'UC Browser')) {
        $browserNameBrowscap = 'UC Browser';
        $browserType = 'Browser';
        $browserMaker = 'UC Web';

        if (preg_match('/UCBrowser\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        } elseif (preg_match('/UC Browser(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'iCab')) {
        $browserNameBrowscap = 'iCab';
        $browserType = 'Browser';
        $browserMaker = 'Alexander Clauss';

        if (preg_match('/iCab\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'Lunascape')) {
        $browserNameBrowscap = 'Lunascape';
        $browserType = 'Browser';
        //$browserMaker = 'Alexander Clauss';

        if (preg_match('/Lunascape (\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== stripos($ua, 'midori')) {
        $browserNameBrowscap = 'Midori';
        $browserType = 'Browser';
        //$browserMaker = 'Alexander Clauss';

        if (preg_match('/Midori\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'OmniWeb')) {
        $browserNameBrowscap = 'OmniWeb';
        $browserType = 'Browser';
        //$browserMaker = 'Alexander Clauss';

        if (preg_match('/OmniWeb\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== stripos($ua, 'maxthon') || false !== strpos($ua, 'MyIE2')) {
        $browserNameBrowscap = 'Maxthon';
        $browserType = 'Browser';
        //$browserMaker = 'Alexander Clauss';

        if (preg_match('/maxthon (\d+\.\d+)/i', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'PhantomJS')) {
        $browserNameBrowscap = 'PhantomJS';
        $browserType = 'Browser';
        $browserMaker = 'phantomjs.org';

        if (preg_match('/PhantomJS\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'YaBrowser')) {
        $browserNameBrowscap = 'Yandex Browser';
        $browserType = 'Browser';
        $browserMaker = 'Yandex';

        if (preg_match('/YaBrowser\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'Kamelio')) {
        $browserNameBrowscap = 'Kamelio App';
        $browserType = 'Application';
        $browserMaker = 'Kamelio';

        $lite = false;
    } elseif (false !== strpos($ua, 'FBAV')) {
        $browserNameBrowscap = 'Facebook App';
        $browserType = 'Application';
        $browserMaker = 'Facebook';

        if (preg_match('/FBAV\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'ACHEETAHI')) {
        $browserNameBrowscap = 'CM Browser';
        $browserType = 'Browser';
        $browserMaker = 'Cheetah Mobile';

        $lite = false;
    } elseif (false !== strpos($ua, 'bdbrowser_i18n')) {
        $browserNameBrowscap = 'Baidu Browser';
        $browserType = 'Browser';
        $browserMaker = 'Baidu';

        if (preg_match('/bdbrowser\_i18n\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'bdbrowserhd_i18n')) {
        $browserNameBrowscap = 'Baidu Browser HD';
        $browserType = 'Browser';
        $browserMaker = 'Baidu';

        if (preg_match('/bdbrowserhd\_i18n\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'bdbrowser_mini')) {
        $browserNameBrowscap = 'Baidu Browser Mini';
        $browserType = 'Browser';
        $browserMaker = 'Baidu';

        if (preg_match('/bdbrowser\_mini\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'Puffin')) {
        $browserNameBrowscap = 'Puffin';
        $browserType = 'Browser';
        $browserMaker = 'CloudMosa Inc.';

        if (preg_match('/Puffin\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'SamsungBrowser')) {
        $browserNameBrowscap = 'Samsung Browser';
        $browserType = 'Browser';
        $browserMaker = 'Samsung';

        if (preg_match('/SamsungBrowser\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'Silk')) {
        $browserNameBrowscap = 'Silk';
        $browserType = 'Browser';
        $browserMaker = 'Amazon.com, Inc.';

        if (preg_match('/Silk\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;

        if (false === strpos($ua, 'Android')) {
            $browserModus = 'Desktop Mode';

            $platformNameBrowscap = 'Android';
            $platformNameDetector = 'Android';
            $platformMaker = 'Google Inc';
            $mobileDevice = 'true';
        }
    } elseif (false !== strpos($ua, 'coc_coc_browser')) {
        $browserNameBrowscap = 'Coc Coc Browser';
        $browserType = 'Browser';
        $browserMaker = 'Coc Coc Company Limited';

        if (preg_match('/coc_coc_browser\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'NaverMatome')) {
        $browserNameBrowscap = 'NaverMatome';
        $browserType = 'Application';
        $browserMaker = 'Naver';

        if (preg_match('/NaverMatome\-Android\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'Flipboard')) {
        $browserNameBrowscap = 'Flipboard App';
        $browserType = 'Application';
        $browserMaker = 'Flipboard, Inc.';

        if (preg_match('/Flipboard\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'Arora')) {
        $browserNameBrowscap = 'Arora';
        $browserType = 'Browser';
        //$browserMaker = 'Flipboard, Inc.';

        if (preg_match('/Arora\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'Acoo Browser')) {
        $browserNameBrowscap = 'Acoo Browser';
        $browserType = 'Browser';
        //$browserMaker = 'Flipboard, Inc.';

        if (preg_match('/Acoo Browser\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'ABrowse')) {
        $browserNameBrowscap = 'ABrowse';
        $browserType = 'Browser';
        //$browserMaker = 'Flipboard, Inc.';

        if (preg_match('/ABrowse\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'AmigaVoyager')) {
        $browserNameBrowscap = 'AmigaVoyager';
        $browserType = 'Browser';
        //$browserMaker = 'Flipboard, Inc.';

        if (preg_match('/AmigaVoyager\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'Beonex')) {
        $browserNameBrowscap = 'Beonex';
        $browserType = 'Browser';
        //$browserMaker = 'Flipboard, Inc.';

        if (preg_match('/Beonex\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'Stainless')) {
        $browserNameBrowscap = 'Stainless';
        $browserType = 'Browser';
        //$browserMaker = 'Flipboard, Inc.';

        if (preg_match('/Stainless\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'Sundance')) {
        $browserNameBrowscap = 'Sundance';
        $browserType = 'Browser';
        //$browserMaker = 'Flipboard, Inc.';

        if (preg_match('/Sundance\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'Sunrise')) {
        $browserNameBrowscap = 'Sunrise';
        $browserType = 'Browser';
        //$browserMaker = 'Flipboard, Inc.';

        if (preg_match('/Sunrise\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'SunriseBrowser')) {
        $browserNameBrowscap = 'Sunrise';
        $browserType = 'Browser';
        //$browserMaker = 'Flipboard, Inc.';

        if (preg_match('/SunriseBrowser\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'Seznam.cz')) {
        $browserNameBrowscap = 'Seznam Browser';
        $browserType = 'Browser';
        $browserMaker = 'Seznam.cz, a.s.';

        if (preg_match('/Seznam\.cz\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'Aviator')) {
        $browserNameBrowscap = 'WhiteHat Aviator';
        $browserType = 'Browser';
        $browserMaker = 'WhiteHat Security';

        if (preg_match('/Aviator\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'Dragon')) {
        $browserNameBrowscap = 'Dragon';
        $browserType = 'Browser';
        $browserMaker = 'Comodo Group Inc';

        if (preg_match('/Dragon\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'Beamrise')) {
        $browserNameBrowscap = 'Beamrise';
        $browserType = 'Browser';
        $browserMaker = 'Beamrise Team';

        if (preg_match('/Beamrise\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'Diglo')) {
        $browserNameBrowscap = 'Diglo';
        $browserType = 'Browser';
        $browserMaker = 'Diglo Inc';

        if (preg_match('/Diglo\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'APUSBrowser')) {
        $browserNameBrowscap = 'APUSBrowser';
        $browserType = 'Browser';
        $browserMaker = 'APUS-Group';

        if (preg_match('/APUSBrowser\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'Chedot')) {
        $browserNameBrowscap = 'Chedot';
        $browserType = 'Browser';
        $browserMaker = 'Chedot.com';

        if (preg_match('/Chedot\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'Qword')) {
        $browserNameBrowscap = 'Qword Browser';
        $browserType = 'Browser';
        $browserMaker = 'Qword Corporation';

        if (preg_match('/Qword\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'Iridium')) {
        $browserNameBrowscap = 'Iridium Browser';
        $browserType = 'Browser';
        $browserMaker = 'Iridium Browser Team';

        if (preg_match('/Iridium\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'MxNitro')) {
        $browserNameBrowscap = 'Maxthon Nitro';
        $browserType = 'Browser';
        $browserMaker = 'Maxthon International Limited';

        if (preg_match('/MxNitro\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'MxBrowser')) {
        $browserNameBrowscap = 'Maxthon';
        $browserType = 'Browser';
        $browserMaker = 'Maxthon International Limited';

        if (preg_match('/MxBrowser\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'Maxthon')) {
        $browserNameBrowscap = 'Maxthon';
        $browserType = 'Browser';
        $browserMaker = 'Maxthon International Limited';

        if (preg_match('/Maxthon\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'Superbird') || false !== strpos($ua, 'SuperBird')) {
        $browserNameBrowscap = 'SuperBird';
        $browserType = 'Browser';
        $browserMaker = 'superbird-browser.com';

        if (preg_match('/superbird\/(\d+\.\d+)/i', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'TinyBrowser')) {
        $browserNameBrowscap = 'TinyBrowser';
        $browserType = 'Browser';
        $browserMaker = 'unknown';

        if (preg_match('/TinyBrowser\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'Chrome') && false !== strpos($ua, 'Version')) {
        $browserNameBrowscap = 'Android WebView';
        $browserType = 'Browser';
        $browserMaker = 'Google Inc';

        if (preg_match('/Version\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        if ($browserVersion <= 1) {
            $lite = false;
        }
    } elseif (false !== strpos($ua, 'Safari') && false !== strpos($ua, 'Version') && false !== strpos($ua, 'Tizen')) {
        $browserNameBrowscap = 'Samsung WebView';
        $browserType = 'Browser';
        $browserMaker = 'Samsung';

        if (preg_match('/Version\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'Chromium')) {
        $browserNameBrowscap = 'Chromium';
        $browserType = 'Browser';
        $browserMaker = 'Google Inc';

        if (preg_match('/Chromium\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'Flock')) {
        $browserNameBrowscap = 'Flock';
        $browserType = 'Browser';
        //$browserMaker = 'Mozilla Foundation';

        if (preg_match('/Flock\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'Fluid')) {
        $browserNameBrowscap = 'Fluid';
        $browserType = 'Browser';
        //$browserMaker = 'Mozilla Foundation';

        if (preg_match('/Fluid\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'ChromePlus')) {
        $browserNameBrowscap = 'ChromePlus';
        $browserType = 'Browser';
        //$browserMaker = 'Google Inc';

        if (preg_match('/ChromePlus\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'RockMelt')) {
        $browserNameBrowscap = 'RockMelt';
        $browserType = 'Browser';
        //$browserMaker = 'Google Inc';

        if (preg_match('/RockMelt\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'Shiira')) {
        $browserNameBrowscap = 'Shiira';
        $browserType = 'Browser';
        //$browserMaker = 'Google Inc';

        if (preg_match('/Shiira\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'Iron')) {
        $browserNameBrowscap = 'Iron';
        $browserType = 'Browser';
        //$browserMaker = 'Google Inc';

        if (preg_match('/Iron\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'Chrome')) {
        $browserNameBrowscap = 'Chrome';
        $browserType = 'Browser';
        $browserMaker = 'Google Inc';
        $browserVersion = $chromeVersion;

        if ($browserVersion < 30) {
            $lite = false;
        }
    } elseif (false !== strpos($ua, 'Opera Mini')) {
        $browserNameBrowscap = 'Opera Mini';
        $browserType = 'Browser';
        $browserMaker = 'Opera Software ASA';

        if (preg_match('/Opera Mini\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }
    } elseif (false !== strpos($ua, 'FlyFlow')) {
        $browserNameBrowscap = 'FlyFlow';
        $browserType = 'Browser';
        $browserMaker = 'Baidu';

        if (preg_match('/FlyFlow\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'Epiphany') || false !== strpos($ua, 'epiphany')) {
        $browserNameBrowscap = 'Epiphany';
        $browserType = 'Browser';
        //$browserMaker = 'Baidu';

        if (preg_match('/Epiphany\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'Safari') && false !== strpos($ua, 'Version') && false !== strpos($ua, 'Android')) {
        $browserNameBrowscap = 'Android';
        $browserType = 'Browser';
        $browserMaker = 'Google Inc';

        if (preg_match('/Version\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        if ($browserVersion != '4.0') {
            $lite = false;
        }
    } elseif (false !== strpos($ua, 'BlackBerry') && false !== strpos($ua, 'Version')) {
        $browserNameBrowscap = 'BlackBerry';
        $browserType = 'Browser';
        $browserMaker = 'Research In Motion Limited';

        if (preg_match('/Version\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }
        $lite = false;
    } elseif (false !== strpos($ua, 'Safari') && false !== strpos($ua, 'Version')) {
        $browserNameBrowscap = 'Safari';
        $browserType = 'Browser';
        $browserMaker = 'Apple Inc';

        if (preg_match('/Version\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }
    } elseif (false !== strpos($ua, 'PaleMoon')) {
        $browserNameBrowscap = 'PaleMoon';
        $browserType = 'Browser';
        $browserMaker = 'Moonchild Productions';

        if (preg_match('/PaleMoon\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'Phoenix')) {
        $browserNameBrowscap = 'Phoenix';
        $browserType = 'Browser';
        //$browserMaker = 'www.waterfoxproject.org';

        if (preg_match('/Phoenix\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== stripos($ua, 'Prism')) {
        $browserNameBrowscap = 'Prism';
        $browserType = 'Browser';
        //$browserMaker = 'www.waterfoxproject.org';

        if (preg_match('/Prism\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== stripos($ua, 'QtWeb Internet Browser')) {
        $browserNameBrowscap = 'QtWeb Internet Browser';
        $browserType = 'Browser';
        //$browserMaker = 'www.waterfoxproject.org';

        if (preg_match('/QtWeb Internet Browser\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'Waterfox')) {
        $browserNameBrowscap = 'Waterfox';
        $browserType = 'Browser';
        $browserMaker = 'www.waterfoxproject.org';

        if (preg_match('/Waterfox\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'QupZilla')) {
        $browserNameBrowscap = 'QupZilla';
        $browserType = 'Browser';
        $browserMaker = 'David Rosca and Community';

        if (preg_match('/QupZilla\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'Thunderbird')) {
        $browserNameBrowscap = 'Thunderbird';
        $browserType = 'Email Client';
        $browserMaker = 'Mozilla Foundation';

        if (preg_match('/Thunderbird\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'kontact')) {
        $browserNameBrowscap = 'Kontact';
        $browserType = 'Email Client';
        $browserMaker = 'KDE e.V.';

        if (preg_match('/kontact\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'Fennec')) {
        $browserNameBrowscap = 'Fennec';
        $browserType = 'Browser';
        $browserMaker = 'Mozilla Foundation';

        if (preg_match('/Fennec\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'myibrow')) {
        $browserNameBrowscap = 'My Internet Browser';
        $browserType = 'Browser';
        $browserMaker = 'unknown';

        if (preg_match('/myibrow\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'Daumoa')) {
        $browserNameBrowscap = 'Daumoa';
        $browserType = 'Bot/Crawler';
        $browserMaker = 'Daum Communications Corp';
        $crawler      = 'true';

        if (preg_match('/Daumoa (\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'Camino')) {
        $browserNameBrowscap = 'Camino';
        $browserType = 'Browser';
        $browserMaker = 'Mozilla Foundation';

        if (preg_match('/Camino\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'Cheshire')) {
        $browserNameBrowscap = 'Cheshire';
        $browserType = 'Browser';
        //$browserMaker = 'Mozilla Foundation';

        if (preg_match('/Cheshire\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'Classilla')) {
        $browserNameBrowscap = 'Classilla';
        $browserType = 'Browser';
        //$browserMaker = 'Mozilla Foundation';

        //if (preg_match('/Classilla\/(\d+\.\d+)/', $ua, $matches)) {
        //    $browserVersion = $matches[1];
        //}

        $lite = false;
    } elseif (false !== strpos($ua, 'CometBird')) {
        $browserNameBrowscap = 'CometBird';
        $browserType = 'Browser';
        //$browserMaker = 'Mozilla Foundation';

        if (preg_match('/CometBird\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'CometBird')) {
        $browserNameBrowscap = 'CometBird';
        $browserType = 'Browser';
        //$browserMaker = 'Mozilla Foundation';

        if (preg_match('/CometBird\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'EnigmaFox')) {
        $browserNameBrowscap = 'EnigmaFox';
        $browserType = 'Browser';
        //$browserMaker = 'Mozilla Foundation';

        if (preg_match('/EnigmaFox\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'conkeror') || false !== strpos($ua, 'Conkeror')) {
        $browserNameBrowscap = 'Conkeror';
        $browserType = 'Browser';
        $browserMaker = 'Mozilla Foundation';

        if (preg_match('/conkeror\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'Galeon')) {
        $browserNameBrowscap = 'Galeon';
        $browserType = 'Browser';
        //$browserMaker = 'Mozilla Foundation';

        if (preg_match('/Galeon\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'Hana')) {
        $browserNameBrowscap = 'Hana';
        $browserType = 'Browser';
        //$browserMaker = 'Mozilla Foundation';

        if (preg_match('/Hana\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'Iceape')) {
        $browserNameBrowscap = 'Iceape';
        $browserType = 'Browser';
        //$browserMaker = 'Mozilla Foundation';

        if (preg_match('/Iceape\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'IceCat')) {
        $browserNameBrowscap = 'IceCat';
        $browserType = 'Browser';
        //$browserMaker = 'Mozilla Foundation';

        if (preg_match('/IceCat\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'Iceweasel')) {
        $browserNameBrowscap = 'Iceweasel';
        $browserType = 'Browser';
        //$browserMaker = 'Mozilla Foundation';

        if (preg_match('/Iceweasel\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'K-Meleon')) {
        $browserNameBrowscap = 'K-Meleon';
        $browserType = 'Browser';
        //$browserMaker = 'Mozilla Foundation';

        if (preg_match('/K\-Meleon\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'K-Ninja')) {
        $browserNameBrowscap = 'K-Ninja';
        $browserType = 'Browser';
        //$browserMaker = 'Mozilla Foundation';

        if (preg_match('/K\-Ninja\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'Kapiko')) {
        $browserNameBrowscap = 'Kapiko';
        $browserType = 'Browser';
        //$browserMaker = 'Mozilla Foundation';

        if (preg_match('/Kapiko\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'Kazehakase')) {
        $browserNameBrowscap = 'Kazehakase';
        $browserType = 'Browser';
        //$browserMaker = 'Mozilla Foundation';

        if (preg_match('/Kazehakase\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'KMLite')) {
        $browserNameBrowscap = 'KMLite';
        $browserType = 'Browser';
        //$browserMaker = 'Mozilla Foundation';

        if (preg_match('/KMLite\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'lolifox')) {
        $browserNameBrowscap = 'lolifox';
        $browserType = 'Browser';
        //$browserMaker = 'Mozilla Foundation';

        if (preg_match('/lolifox\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'Konqueror')) {
        $browserNameBrowscap = 'Konqueror';
        $browserType = 'Browser';
        //$browserMaker = 'Mozilla Foundation';

        if (preg_match('/Konqueror\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'Leechcraft')) {
        $browserNameBrowscap = 'Leechcraft';
        $browserType = 'Browser';
        //$browserMaker = 'Mozilla Foundation';

        //if (preg_match('/Leechcraft\/(\d+\.\d+)/', $ua, $matches)) {
        //    $browserVersion = $matches[1];
        //}

        $lite = false;
    } elseif (false !== strpos($ua, 'Madfox')) {
        $browserNameBrowscap = 'Madfox';
        $browserType = 'Browser';
        //$browserMaker = 'Mozilla Foundation';

        if (preg_match('/Madfox\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'myibrow')) {
        $browserNameBrowscap = 'myibrow';
        $browserType = 'Browser';
        //$browserMaker = 'Mozilla Foundation';

        if (preg_match('/myibrow\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'Netscape6')) {
        $browserNameBrowscap = 'Netscape';
        $browserType = 'Browser';
        //$browserMaker = 'Mozilla Foundation';

        if (preg_match('/Netscape6\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'Netscape')) {
        $browserNameBrowscap = 'Netscape';
        $browserType = 'Browser';
        //$browserMaker = 'Mozilla Foundation';

        if (preg_match('/Netscape\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'Navigator')) {
        $browserNameBrowscap = 'Netscape Navigator';
        $browserType = 'Browser';
        //$browserMaker = 'Mozilla Foundation';

        if (preg_match('/Navigator\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'Orca')) {
        $browserNameBrowscap = 'Orca';
        $browserType = 'Browser';
        //$browserMaker = 'Mozilla Foundation';

        if (preg_match('/Orca\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'Sylera')) {
        $browserNameBrowscap = 'Sylera';
        $browserType = 'Browser';
        //$browserMaker = 'Mozilla Foundation';

        if (preg_match('/Sylera\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'SeaMonkey')) {
        $browserNameBrowscap = 'SeaMonkey';
        $browserType = 'Browser';
        //$browserMaker = 'Mozilla Foundation';

        if (preg_match('/SeaMonkey\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'Fennec')) {
        $browserNameBrowscap = 'Fennec';
        $browserType = 'Browser';
        $browserMaker = 'Mozilla Foundation';

        if (preg_match('/Fennec\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'GoBrowser')) {
        $browserNameBrowscap = 'GoBrowser';
        $browserType = 'Browser';
        //$browserMaker = 'Mozilla Foundation';

        if (preg_match('/GoBrowser\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'Minimo')) {
        $browserNameBrowscap = 'Minimo';
        $browserType = 'Browser';
        //$browserMaker = 'Mozilla Foundation';

        if (preg_match('/Minimo\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'BonEcho')) {
        $browserNameBrowscap = 'Firefox';
        $browserType = 'Browser';
        $browserMaker = 'Mozilla Foundation';

        if (preg_match('/BonEcho\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        if ($browserVersion < 30) {
            $lite = false;
        }
    } elseif (false !== strpos($ua, 'Shiretoko')) {
        $browserNameBrowscap = 'Firefox';
        $browserType = 'Browser';
        $browserMaker = 'Mozilla Foundation';

        if (preg_match('/Shiretoko\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        if ($browserVersion < 30) {
            $lite = false;
        }
    } elseif (false !== strpos($ua, 'Minefield')) {
        $browserNameBrowscap = 'Firefox';
        $browserType = 'Browser';
        $browserMaker = 'Mozilla Foundation';

        if (preg_match('/Minefield\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        if ($browserVersion < 30) {
            $lite = false;
        }
    } elseif (false !== strpos($ua, 'Namoroka')) {
        $browserNameBrowscap = 'Firefox';
        $browserType = 'Browser';
        $browserMaker = 'Mozilla Foundation';

        if (preg_match('/Namoroka\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        if ($browserVersion < 30) {
            $lite = false;
        }
    } elseif (false !== strpos($ua, 'GranParadiso')) {
        $browserNameBrowscap = 'Firefox';
        $browserType = 'Browser';
        $browserMaker = 'Mozilla Foundation';

        if (preg_match('/GranParadiso\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        if ($browserVersion < 30) {
            $lite = false;
        }
    } elseif (false !== strpos($ua, 'Firebird')) {
        $browserNameBrowscap = 'Firefox';
        $browserType = 'Browser';
        $browserMaker = 'Mozilla Foundation';

        if (preg_match('/Firebird\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        if ($browserVersion < 30) {
            $lite = false;
        }
    } elseif (false !== strpos($ua, 'Firefox')) {
        $browserNameBrowscap = 'Firefox';
        $browserType = 'Browser';
        $browserMaker = 'Mozilla Foundation';

        if (preg_match('/Firefox\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        if ($browserVersion < 30) {
            $lite = false;
        }
    } elseif (false !== strpos($ua, 'FxiOS')) {
        $browserNameBrowscap = 'Firefox for iOS';
        $browserType = 'Browser';
        $browserMaker = 'Mozilla Foundation';

        if (preg_match('/FxiOS\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'Browzar')) {
        $browserNameBrowscap = 'Browzar';
        $browserType = 'Browser';
        //$browserMaker = 'Mozilla Foundation';

        $lite = false;
    } elseif (false !== strpos($ua, 'Crazy Browser')) {
        $browserNameBrowscap = 'Crazy Browser';
        $browserType = 'Browser';
        //$browserMaker = 'Mozilla Foundation';

        if (preg_match('/Crazy Browser (\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'GreenBrowser')) {
        $browserNameBrowscap = 'GreenBrowser';
        $browserType = 'Browser';
        //$browserMaker = 'Mozilla Foundation';

        //if (preg_match('/Crazy Browser (\d+\.\d+)/', $ua, $matches)) {
        //    $browserVersion = $matches[1];
        //}

        $lite = false;
    } elseif (false !== strpos($ua, 'KKman')) {
        $browserNameBrowscap = 'KKman';
        $browserType = 'Browser';
        //$browserMaker = 'Mozilla Foundation';

        if (preg_match('/KKman(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'Lobo')) {
        $browserNameBrowscap = 'Lobo';
        $browserType = 'Browser';
        //$browserMaker = 'Mozilla Foundation';

        if (preg_match('/Lobo\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'Sleipnir')) {
        $browserNameBrowscap = 'Sleipnir';
        $browserType = 'Browser';
        //$browserMaker = 'Mozilla Foundation';

        if (preg_match('/Sleipnir\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'SlimBrowser')) {
        $browserNameBrowscap = 'SlimBrowser';
        $browserType = 'Browser';
        //$browserMaker = 'Mozilla Foundation';

        if (preg_match('/SlimBrowser\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'TencentTraveler')) {
        $browserNameBrowscap = 'TencentTraveler';
        $browserType = 'Browser';
        //$browserMaker = 'Mozilla Foundation';

        if (preg_match('/TencentTraveler (\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'TheWorld')) {
        $browserNameBrowscap = 'TheWorld';
        $browserType = 'Browser';
        //$browserMaker = 'Mozilla Foundation';

        if (preg_match('/TheWorld\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'MSIE')) {
        $browserNameBrowscap = 'IE';
        $browserType = 'Browser';
        $browserMaker = 'Microsoft Corporation';

        if (preg_match('/MSIE (\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = true;
    } elseif (false !== strpos($ua, 'SMTBot')) {
        $browserNameBrowscap = 'SMTBot';
        $browserType = 'Bot/Crawler';
        $browserMaker = 'SimilarTech Ltd.';
        $crawler      = 'true';

        if (preg_match('/SMTBot\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'gvfs')) {
        $browserNameBrowscap = 'gvfs';
        $browserType = 'Tool';
        $browserMaker = 'The GNOME Project';

        if (preg_match('/gvfs\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'luakit')) {
        $browserNameBrowscap = 'luakit';
        $browserType = 'Browser';
        $browserMaker = 'Mason Larobina';

        if (preg_match('/WebKitGTK\+\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'Cyberdog')) {
        $browserNameBrowscap = 'Cyberdog';
        $browserType = 'Browser';
        //$browserMaker = 'Mason Larobina';

        if (preg_match('/Cyberdog\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'ELinks')) {
        $browserNameBrowscap = 'ELinks';
        $browserType = 'Browser';
        //$browserMaker = 'Mason Larobina';

        //if (preg_match('/WebKitGTK\+\/(\d+\.\d+)/', $ua, $matches)) {
        //    $browserVersion = $matches[1];
        //}

        $lite = false;
    } elseif (false !== strpos($ua, 'Links')) {
        $browserNameBrowscap = 'Links';
        $browserType = 'Browser';
        //$browserMaker = 'Mason Larobina';

        //if (preg_match('/WebKitGTK\+\/(\d+\.\d+)/', $ua, $matches)) {
        //    $browserVersion = $matches[1];
        //}

        $lite = false;
    } elseif (false !== strpos($ua, 'Galaxy')) {
        $browserNameBrowscap = 'Galaxy';
        $browserType = 'Browser';
        //$browserMaker = 'Mason Larobina';

        if (preg_match('/Galaxy\/(\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'iNet Browser')) {
        $browserNameBrowscap = 'iNet Browser';
        $browserType = 'Browser';
        //$browserMaker = 'Mason Larobina';

        if (preg_match('/iNet Browser (\d+\.\d+)/', $ua, $matches)) {
            $browserVersion = $matches[1];
        }

        $lite = false;
    } elseif (false !== strpos($ua, 'Uzbl')) {
        $browserNameBrowscap = 'Uzbl';
        $browserType = 'Browser';
        //$browserMaker = 'Mason Larobina';

        if (preg_match('/Uzbl (\d+\.\d+)/', $ua, $matches)) {
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
            'Browser' => '" . str_replace("'", "\\'", $browserNameBrowscap) . "',
            'Browser_Type' => '$browserType',
            'Browser_Bits' => '$browserBits',
            'Browser_Maker' => '$browserMaker',
            'Browser_Modus' => '$browserModus',
            'Version' => '$browserVersion',
            'MajorVer' => '$maxVersion',
            'MinorVer' => '$minVersion',
            'Platform' => '$platformNameBrowscap',
            'Platform_Version' => '$platformVersionBrowscap',
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
            'Browser_Name'            => '" . str_replace("'", "\\'", $browserNameBrowscap) . "',
            'Browser_Type'            => '$browserType',
            'Browser_Bits'            => $browserBits,
            'Browser_Maker'           => '$browserMaker',
            'Browser_Modus'           => '$browserModus',
            'Browser_Version'         => '$browserVersion',
            'Platform_Name'           => '$platformNameDetector',
            'Platform_Version'        => '$platformVersionDetector',
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