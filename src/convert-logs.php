<?php
/*******************************************************************************
 * INIT
 ******************************************************************************/
use FileLoader\Loader;

ini_set('memory_limit', '3000M');
ini_set('max_execution_time', '-1');
ini_set('max_input_time', '-1');
ini_set('display_errors', 1);
error_reporting(E_ALL);

date_default_timezone_set('Europe/Berlin');

require __DIR__ . '/../vendor/autoload.php';

$sourcesDirectory =  __DIR__ . '/../sources/';
$targetDirectory  =  __DIR__ . '/../results/';

$i = 0;
$j = 0;

$requests = [];

$targetBulkFile = $targetDirectory . date('Y-m-d') . '-testagents.txt';
$targetInfoFile = $targetDirectory . date('Y-m-d') . '-testagents.info.txt';

echo "writing to file '" . $targetBulkFile . "'\n";

$loader = new Loader();

/*******************************************************************************
 * loading files
 ******************************************************************************/

$files = scandir($sourcesDirectory, SCANDIR_SORT_ASCENDING);

foreach ($files as $filename) {
    /** @var $file \SplFileInfo */
    $file = new \SplFileInfo($sourcesDirectory . DIRECTORY_SEPARATOR . $filename);

    $ips      = [];
    $requests = [];

    ++$i;
    echo '# ', sprintf('%1$05d', (int) $i), ' :', strtolower($file->getPathname()), ' [ bisher ', ($j > 0 ? $j : 'keine'), ' Agent', ($j !== 1 ? 'en' : ''), ' ]';

    if (!$file->isFile() || !$file->isReadable()) {
        echo ' - ignoriert', PHP_EOL;

        continue;
    }

    if (in_array($file->getExtension(), ['filepart', 'sql', 'rename', 'txt', 'zip', 'rar', 'php', 'gitkeep'])) {
        echo ' - ignoriert', PHP_EOL;

        continue;
    }

    if (null === ($filepath = getPath($file))) {
        echo ' - ignoriert', PHP_EOL;

        continue;
    }

    $j += handleFile($loader, $filepath, $targetInfoFile, $targetBulkFile, $file);
}

if (file_exists($targetBulkFile)) {
    $data = file($targetBulkFile, FILE_SKIP_EMPTY_LINES | FILE_IGNORE_NEW_LINES);
    $data = array_unique($data);

    file_put_contents($targetBulkFile, implode("\n", $data), LOCK_EX);
}

echo PHP_EOL, PHP_EOL;
echo 'lesen der Dateien beendet. ', $j, ' neue Agenten hinzugefuegt', PHP_EOL;
exit;

/*******************************************************************************
 * library
 ******************************************************************************/
/**
 * @param Loader      $loader
 * @param string      $filepath
 * @param string      $targetInfoFile
 * @param string      $targetBulkFile
 * @param SplFileInfo $file
 *
 * @throws \FileLoader\Exception
 * @return int
 */
function handleFile(\FileLoader\Loader $loader, $filepath, $targetInfoFile, $targetBulkFile, \SplFileInfo $file)
{
    $startTime = microtime(true);

    $loader->setLocalFile($filepath);

    /** @var \GuzzleHttp\Psr7\Response $response */
    $response = $loader->load();

    /** @var \GuzzleHttp\Psr7\Stream $stream */
    $stream = $response->getBody();

    $stream->read(1);

    $k      = 0;
    $agents = [];

    $stream->rewind();

    while (!$stream->eof()) {
        $line = $stream->read(8192);

        $lineMatches = [];

        if (!preg_match(getRegex(), $line, $lineMatches)) {
            file_put_contents($targetInfoFile, 'no useragent found in line "' . $line . '"' . "\n", FILE_APPEND | LOCK_EX);

            continue;
        }

        if (isset($lineMatches['userAgentString'])) {
            $agentOfLine = trim($lineMatches['userAgentString']);
        } else {
            $agentOfLine = trim(extractAgent($line));
        }

        if (!array_key_exists($agentOfLine, $agents)) {
            $agents[$agentOfLine] = 1;
        } else {
            ++$agents[$agentOfLine];
        }
    }

    $sortCount = [];
    $sortTime  = [];
    $sortAgent = [];

    foreach ($agents as $agentOfLine => $count) {
        $sortCount[$agentOfLine] = $count;
        $sortAgent[$agentOfLine] = $agentOfLine;
    }

    array_multisort($sortCount, SORT_DESC, $sortTime, SORT_DESC, $sortAgent, SORT_ASC, $agents);

    foreach (array_keys($agents) as $agentOfLine) {
        file_put_contents($targetBulkFile, $agentOfLine . "\n", FILE_APPEND | LOCK_EX);
        ++$k;
    }

    $dauer = microtime(true) - $startTime;
    echo ' - fertig [ ', ($k > 0 ? $k . ' neue' : 'keine neuen'), ($k === 1 ? 'r' : ''), ' Agent', ($k !== 1 ? 'en' : ''), ', ', number_format($dauer, 4, ',', '.'), ' sec ]', PHP_EOL;

    unlink($file->getPathname());

    return $k;
}

function extractAgent($text)
{
    $parts = explode('"', $text);
    array_pop($parts);

    $userAgent = array_pop($parts);

    return $userAgent;
}

function extractTime($text)
{
    $timeParts = explode('[', $text);

    if (isset($timeParts[1])) {
        $parts = explode(']', $timeParts[1]);
        $time  = str_replace(']', '', trim($parts[0]));
        $time  = strtotime($time);
    } else {
        var_dump('extractTime failed: no time found', $text, $timeParts);
        exit;
    }

    return date('Y-m-d H:i:s', $time);
}

function extractIP($text)
{
    $ipParts = explode(' ', $text);

    return trim($ipParts[0]);
}

function extractReturn($text)
{
    $pos = strpos($text, 'HTTP/');

    if (false !== $pos) {
        $posStart = strpos($text, ' ', $pos) + 1;
        $posEnd   = strpos($text, ' ', $posStart) - 1;
    } else {
        $parts = explode(' ', $text);

        if (!empty($parts[12])) {
            return (int) trim($parts[12]);
        }

        var_dump('extractReturn failed: no HTTP found', $text, $parts[12]);
        exit;
    }

    return (int) trim(substr($text, $posStart, $posEnd - $posStart + 1));
}

function extractRequest($text)
{
    $parts = explode('"', $text);

    return $parts[1];
}

/**
 * @param \SplFileInfo $file
 *
 * @return string
 */
function getPath(\SplFileInfo $file)
{
    if (false === realpath($file->getPathname())) {
        return null;
    }

    switch ($file->getExtension()) {
        case 'gz':
            $path = 'compress.zlib://' . realpath($file->getPathname());
            break;
        case 'bz2':
            $path = 'compress.bzip2://' . realpath($file->getPathname());
            break;
        case 'tgz':
            $path = 'phar://' . realpath($file->getPathname());
            break;
        default:
            $path = realpath($file->getPathname());
            break;
    }

    return $path;
}

function getRegex()
{
    return '/^'
    . '(?P<remotehost>\S+)'                            # remote host (IP)
    . '\s+'
    . '(?P<logname>\S+)'                            # remote logname
    . '\s+'
    . '(?P<user>\S+)'                            # remote user
    . '.*'
    . '\[(?P<time>[^]]+)\]'                      # date/time
    . '[^"]+'
    . '\"(?P<http>.*)\"'                         # Verb(GET|POST|HEAD) Path HTTP Version
    . '\s+'
    . '(?P<status>.*)'                             # Status
    . '\s+'
    . '(?P<length>.*)'                             # Length (include Header)
    . '[^"]+'
    . '\"(?P<referrer>.*)\"'                         # Referrer
    . '[^"]+'
    . '\"(?P<userAgentString>.*)\".*'   # User Agent
    . '$/x';
}
