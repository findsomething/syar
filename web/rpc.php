<?php
/**
 * Created by PhpStorm.
 * User: lihan
 * Date: 17/1/13
 * Time: 09:44
 */
$kernel = include dirname(__DIR__)."/bootstrap/bootstrap-server.php";
try {
    $service = $kernel->service(empty($_GET['service']) ? 'NotFoundService' : $_GET['service']);
} catch (\Exception $e) {
    $service = $kernel->service('NotFoundService');
}

$server = new \Yar_Server($service);
return $server->handle();