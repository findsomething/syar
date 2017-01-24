<?php
/**
 * Created by PhpStorm.
 * User: lihan
 * Date: 17/1/12
 * Time: 23:19
 */
namespace FSth\SYar\Server;

use FSth\Framework\Server\HttpServer;

class Server extends HttpServer
{
    protected $binds = array(
        'onWorkerStart' => 'WorkerStart',
        'onRequest' => 'request',
        'onTask' => 'task',
        'onFinish' => 'finish'
    );
}