<?php
/**
 * Created by PhpStorm.
 * User: lihan
 * Date: 17/1/13
 * Time: 00:40
 */
namespace FSth\SYar\Service;

use FSth\Framework\Client\SYarClient;
use FSth\Framework\Client\YarClient;
use FSth\Framework\Context\Object;
use FSth\SYar\Client\Client;

class TestService extends Object
{
    public function giveBack($params)
    {
        $client = new Client("127.0.0.1", "9511", "HealthService");
//        $client = new SYarClient("127.0.0.1", "9511", "HealthService");
//        $client = new YarClient("http://127.0.0.1:9509?service=HealthService");
        $client->setServerName("liveRpcService");
        $result = $client->health();
        return $result;
    }
}