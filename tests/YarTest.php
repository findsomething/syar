<?php

/**
 * Created by PhpStorm.
 * User: lihan
 * Date: 17/1/13
 * Time: 01:04
 */
class YarTest extends PHPUnit_Framework_TestCase
{
    private $url = "http://127.0.0.1:9503/";

    public function testSYar()
    {
        $client = new \FSth\Framework\Client\YarClient($this->url . "?service=TestService");
        $client->setServerName("testClient");
        $params = array('hello' => 'world');

        $client->giveBack($params);
        var_dump($params);
    }

    public function testYar()
    {
//        $this->url = "http://syar.edusoho.net/rpc.php";
//        $client = new \Yar_client($this->url . "?service=TestService");
//        $params = array('hello' => 'world');
//        $client->giveBack($params);
//        var_dump($params);
    }
}