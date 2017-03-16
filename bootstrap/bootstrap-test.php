<?php
/**
 * Created by PhpStorm.
 * User: lihan
 * Date: 17/1/12
 * Time: 23:14
 */
include dirname(__DIR__) . '/vendor/autoload.php';

$conf = include dirname(__DIR__)."/config/parameters.php";

$kernel = new FSth\SYar\Kernel($conf);
$kernel->boot();

return $kernel;
