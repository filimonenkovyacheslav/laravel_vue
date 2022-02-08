<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$outs = array();
var_dump(exec('ls', $outs));
var_dump($outs);