<?php
$method = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];
$time = "0".(int)($GLOBALS['stoptime']-$_SERVER["REQUEST_TIME_FLOAT"])* 1000;
if($code == 200) {
   $log = "{$method}\t\t{$uri}\t\t\t{$code}\t\t{$time}ms"; 
   file_put_contents('logs.txt', $log."\n", FILE_APPEND | LOCK_EX);
}