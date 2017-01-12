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
    protected $initBinds = array(
        'onServerStart' => 'ManagerStart',
        'onServerStop' => 'ManagerStop',
        'onTask' => 'task',
        'onFinish' => 'finish'
    );

    public function onTask(\swoole_http_server $server, $taskId, $fromId, $data)
    {

    }

    public function onFinish(\swoole_http_server $server, $taskId, $data)
    {

    }
}