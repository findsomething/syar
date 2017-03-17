<?php

namespace FSth\SYar\Server;

use FSth\Framework\Server\MultiServer as BaseMultiServer;

class MultiServer extends BaseMultiServer
{
    protected $binds = [
        'onWorkerStart' => 'WorkerStart',
        'onRequest' => 'request',
        'onTask' => 'task',
        'onFinish' => 'finish',
        'onReceive' => 'receive'
    ];
}