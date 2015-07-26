<?php
if (!isset($ini))
{
    require_once $argv[1].'Ini.php';
    $ini = new Ini();
}

$ts = new TestServer();
$ts->start();
?>