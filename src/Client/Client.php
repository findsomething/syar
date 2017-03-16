<?php

namespace FSth\SYar\Client;

use FSth\Framework\Client\Client as BaseClient;
use FSth\Framework\Server\Pack\Handler;
use FSth\Framework\Server\Pack\Packer;
use FSth\SYar\Exception\SYarException;
use FSth\SYar\Tool\Format;
use FSth\SYar\Tool\Parser;

class Client extends BaseClient
{
    const RECEIVE_TIMEOUT = 3;

    protected $host;
    protected $port;

    protected $client;

    protected $code;
    protected $error;

    protected $service;

    protected $packer;
    protected $parser;

    protected $setting = [
        'open_length_check' => 1,
        'package_length_type' => 'N',
        'package_length_offset' => 0,
        'package_body_offset' => 4,
        'package_max_length' => 1024 * 1024 * 2,
        'open_tcp_nodelay' => 1,
        'socket_buffer_size' => 1024 * 1024 * 4,
    ];

    public function __construct($host, $port, $service, $options = [])
    {
        $this->client = new \swoole_client(SWOOLE_SOCK_TCP | SWOOLE_KEEP);
        $this->setting = $this->setting + $options;
        $this->service = $service;
        $this->client->set($this->setting);

        $this->packer = new Packer();
        $this->packer->setPackerHandler(new Handler());
        $this->parser = new Parser();

        $this->host = $host;
        $this->port = $port;
        $connected = $this->client->connect($this->host, $this->port, self::RECEIVE_TIMEOUT);
        if (!$connected) {
            $this->code = $this->client->errCode;
            if ($this->code == 0) {
                $this->error = "Connect fail.Please check the host dns.";
                $this->code = -1;
            } else {
                $this->error = \socket_strerror($this->code);
            }
            throw new SYarException($this->error, $this->code);
        }
    }

    public function __call($name, $arguments)
    {
        // TODO: Implement __call() method.
        $this->client->send($this->packer->encode(Format::client($this->service, $name, $arguments)));

        $receive = $this->client->recv();
        $result = $this->packer->decode($receive);
        return $this->parser->parse($result['data']);
    }
}