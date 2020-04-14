<?php
$GLOBALS['starttime'] = microtime(true);
header('Content-Type: text/plain; charset=utf-8');
http_response_code(200);
$code = 200;
$file = dirname(__FILE__). '/logs.txt';
if(is_file($file)) {
    echo trim(file_get_contents($file));
} else {
    echo "No logs yet.";
}
$GLOBALS['stoptime'] = microtime(true);
include_once('process-log.php');