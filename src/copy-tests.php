<?php
/*******************************************************************************
 * INIT
 ******************************************************************************/
ini_set('memory_limit', '3000M');
ini_set('max_execution_time', '-1');
ini_set('max_input_time', '-1');
ini_set('display_errors', 1);
error_reporting(E_ALL);

date_default_timezone_set('Europe/Berlin');

/*******************************************************************************
 * loading files
 ******************************************************************************/
 
$sourceDirectory = 'X:\\projects\\BrowserDetector\\vendor\\browscap\\browscap\\tests\\fixtures\\issues\\';
$targetDirectory = 'X:\\projects\\BrowserDetector\\tests\\issues\\';

$iterator = new \RecursiveDirectoryIterator($sourceDirectory);
$checks   = array();
$counter  = 0;

foreach (new \RecursiveIteratorIterator($iterator) as $file) {
    /** @var $file \SplFileInfo */
    if (!$file->isFile() || $file->getExtension() != 'php') {
        continue;
    }
    
    echo 'processing ' . $file->getPathname() . ' ...' . "\n";
    
    $tests = require_once $file->getPathname();
    
    $output  = "<?php\n\nreturn [\n";
    
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
        
        $data[$key]          = $test;
        $checks[$test['ua']] = $key;
        
        $output .= "    'browscap-$key' => [
        'ua' => '" . str_replace("'", "\\'", $test['ua']) . "',
        'properties' => [
            'Browser' => '" .  str_replace("'", "\\'", $test['properties']['Browser']) . "',
            'Browser_Type' => '" .  str_replace("'", "\\'", $test['properties']['Browser_Type']) . "',
            'Browser_Bits' => " .  str_replace("'", "\\'", $test['properties']['Browser_Bits']) . ",
            'Browser_Maker' => '" .  str_replace("'", "\\'", $test['properties']['Browser_Maker']) . "',
            'Browser_Modus' => '" .  str_replace("'", "\\'", $test['properties']['Browser_Modus']) . "',
            'Version' => '" .  str_replace("'", "\\'", $test['properties']['Version']) . "',
            'Platform' => '" .  str_replace("'", "\\'", $test['properties']['Platform']) . "',
            'Platform_Version' => '" .  str_replace("'", "\\'", $test['properties']['Platform_Version']) . "',
            'Platform_Bits' => " .  $test['properties']['Platform_Bits'] . ",
            'Platform_Maker' => '" .  str_replace("'", "\\'", $test['properties']['Platform_Maker']) . "',
            'Win16' => " .  ($test['properties']['Win16'] ? 'true' : 'false') . ",
            'Win32' => " .  ($test['properties']['Win32'] ? 'true' : 'false') . ",
            'Win64' => " .  ($test['properties']['Win64'] ? 'true' : 'false') . ",
            'isMobileDevice' => " .  ($test['properties']['isMobileDevice'] ? 'true' : 'false') . ",
            'isTablet' => " .  ($test['properties']['isTablet'] ? 'true' : 'false') . ",
            'Crawler' => " .  ($test['properties']['Crawler'] ? 'true' : 'false') . ",
            'Device_Name' => '" .  str_replace("'", "\\'", $test['properties']['Device_Name']) . "',
            'Device_Maker' => '" .  str_replace("'", "\\'", $test['properties']['Device_Maker']) . "',
            'Device_Type' => '" .  str_replace("'", "\\'", $test['properties']['Device_Type']) . "',
            'Device_Pointing_Method' => '" .  str_replace("'", "\\'", $test['properties']['Device_Pointing_Method']) . "',
            'Device_Code_Name' => '" .  str_replace("'", "\\'", $test['properties']['Device_Code_Name']) . "',
            'Device_Brand_Name' => '" .  str_replace("'", "\\'", $test['properties']['Device_Brand_Name']) . "',
            'RenderingEngine_Name' => '" .  str_replace("'", "\\'", $test['properties']['RenderingEngine_Name']) . "',
            'RenderingEngine_Version' => '" .  str_replace("'", "\\'", $test['properties']['RenderingEngine_Version']) . "',
            'RenderingEngine_Maker' => '" .  str_replace("'", "\\'", $test['properties']['RenderingEngine_Maker']) . "',
        ],
    ],\n";
        
        $counter++;
    }
    
    $output .= "];\n";
    
    file_put_contents($targetDirectory . $file->getFilename(), $output);
}

echo "\nEs wurden $counter Tests exportiert";