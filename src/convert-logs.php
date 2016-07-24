<?php
/*******************************************************************************
 * INIT
 ******************************************************************************/
ini_set('memory_limit', '3000M');
ini_set('max_execution_time', '-1');
ini_set('max_input_time', '-1');
ini_set('display_errors', 1);
error_reporting(E_ALL);

define('DB_SERVER', 'localhost');
define('DB_NAME', 'test');
define('DB_USER', 'root');
define('DB_PASSWORD', '');

date_default_timezone_set('Europe/Berlin');

$path = 'D:\\projects\\temp\\log\\';
//$db   = new database();Thomas Mueller

/*******************************************************************************
 * loading files
 ******************************************************************************/
try {
    $dir = new FilesystemIterator($path);
} catch (Exception $e) {
    echo $e->getMessage();
    die();
}

$i = 0;
$j = 0;

$requests = [];

$targetSqlFile  = 'D:\\projects\\temp\\' . date('Y-m-d') . '-testagents.sql';
$targetBulkFile = 'D:\\projects\\temp\\' . date('Y-m-d') . '-testagents.txt';
echo "writing to file '" . $targetSqlFile . "'\n";

foreach ($dir as $file) {
    $ips      = [];
    $requests = [];

    ++$i;
    $filePath = strtolower($path . $file->getFilename());

    echo '#' . sprintf('%1$05d', (int) $i) . ' :' . $filePath . ' [ bisher ' . ($j > 0 ? $j : 'keine') . ' Agent' . ($j !== 1 ? 'en' : '') . ' ]';

    if (!$file->isFile() || !$file->isReadable()) {
        echo ' - ignoriert' . "\n";

        continue;
    }

    if (('.gz' === substr($filePath, -3)) || ('.tgz' === substr($filePath, -4)) || ('.filepart' === substr($filePath, -9)) || ('.sql' === substr($filePath, -4)) || ('.rename' === substr($filePath, -7)) || ('.txt' === substr($filePath, -4)) || ('.zip' === substr($filePath, -4)) || ('.rar' === substr($filePath, -4))) {
        echo ' - ignoriert' . "\n";

        continue;
    }

    $startTime = microtime(true);
    $lines     = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $lines     = array_unique($lines);
    $k         = 0;
    $agents    = [];
    $ips       = [];
    $requests  = [];

    file_put_contents($targetSqlFile, '-- ' . $file->getFilename() . "\n\n", FILE_APPEND | LOCK_EX);
    file_put_contents($targetSqlFile, "START TRANSACTION;\n", FILE_APPEND | LOCK_EX);

    foreach ($lines as $line) {
        $agentOfLine   = trim(extractAgent($line));
        $timeOfLine    = trim(extractTime($line));

        if (!array_key_exists($agentOfLine, $agents)) {
            $agents[$agentOfLine] = ['count' => 1, 'time' => $timeOfLine, 'file' => $targetSqlFile . ' / ' . $file->getFilename(), 'line' => $line];
        } else {
            ++$agents[$agentOfLine]['count'];
        }
    }

    file_put_contents($targetSqlFile, '-- ' . $file->getFilename() . ': UserAgents (' . count($agents) . " Pcs.)\n\n", FILE_APPEND | LOCK_EX);

    $sort1 = [];
    $sort2 = [];
    $sort3 = [];

    foreach ($agents as $agentOfLine => $data) {
        $sort1[$agentOfLine] = $data['count'];
        $sort2[$agentOfLine] = $data['time'];
        $sort3[$agentOfLine] = $agentOfLine;
    }

    array_multisort($sort1, SORT_DESC, $sort2, SORT_DESC, $sort3, SORT_ASC, $agents);

    foreach ($agents as $agentOfLine => $data) {
        //file_put_contents($targetSqlFile, '-- ' . $data['line'] . "\n", FILE_APPEND | LOCK_EX);

        $sql = "INSERT INTO `agents` (`agent`, `count`, `lastTimeFound`, `created`, `file`) VALUES ('" . addslashes($agentOfLine) . "', " . addslashes($data['count']) . ", '" . addslashes($data['time']) . "', '" . addslashes($data['time']) . "', '" . addslashes($data['file']) . "') ON DUPLICATE KEY UPDATE `count`=`count`+" . addslashes($data['count']) . ", `file`='" . addslashes($data['file']) . "',`lastTimeFound`='" . addslashes($data['time']) . "';\n";
        file_put_contents($targetSqlFile, $sql, FILE_APPEND | LOCK_EX);
        file_put_contents($targetBulkFile, $agentOfLine . "\n", FILE_APPEND | LOCK_EX);
        ++$k;
    }

    file_put_contents($targetSqlFile, "\n\n", FILE_APPEND | LOCK_EX);

    file_put_contents($targetSqlFile, "COMMIT;\n\n", FILE_APPEND | LOCK_EX);
    $agentsToStore = '';

    $dauer = microtime(true) - $startTime;
    echo ' - fertig [ ' . ($k > 0 ? $k . ' neue' : 'keine neuen') . ($k === 1 ? 'r' : '') . ' Agent' . ($k !== 1 ? 'en' : '') . ', ' . number_format($dauer, 4, ',', '.') . ' sec ]';

    echo "\n";//exit;
    unlink($filePath);

    $j += $k;

    if (600 <= (int) $dauer) {
        echo "\n\n";
        echo 'Abbruch, da Einlesen zu Lange dauert ... - bitte Neu starten' . "\n";

        break;
    }
}

$data = file($targetBulkFile, FILE_SKIP_EMPTY_LINES | FILE_IGNORE_NEW_LINES);
$data = array_unique($data);

file_put_contents($targetBulkFile, implode("\n", $data), LOCK_EX);

echo "\n\n";
echo 'lesen der Dateien beendet. ' . $j . ' neue Agenten hinzugefuegt' . "\n";
exit;

/*******************************************************************************
 * library
 ******************************************************************************/
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
    $time      = time();

    if (isset($timeParts[1])) {
        $parts = explode(']', $timeParts[1]);
        $time  = str_replace(']', '', trim($parts[0]));
        $time  = strtotime($time);
    } else {
        var_dump($text, $timeParts);
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

        var_dump($text, $parts[12]);
        exit;
    }

    return (int) trim(substr($text, $posStart, $posEnd - $posStart + 1));
}
function extractRequest($text)
{
    $parts = explode('"', $text);

    return $parts[1];
}
