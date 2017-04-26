<?php
/**
 * Created by PhpStorm.
 * User: lihan
 * Date: 17/1/12
 * Time: 23:50
 */
namespace FSth\SYar\Server;

use FSth\Framework\Server\Pack\Handler as TcpHandler;
use FSth\Framework\Server\Protocol as BaseProtocol;
use FSth\SYar\Exception\SYarException;
use FSth\SYar\Tool\Format;
use FSth\SYar\Yar\Packer;
use FSth\Framework\Server\Pack\Packer as TcpPacker;

class Protocol extends BaseProtocol
{
    private $packer;
    private $yar;
    private $tcpPacker;

    public function __construct($kernel)
    {
        parent::__construct($kernel);
        $this->packer = new Packer();

        $this->tcpPacker = new TcpPacker();
        $this->tcpPacker->setPackerHandler(new TcpHandler());
    }

    public function onTask(\swoole_http_server $server, $taskId, $fromId, $data)
    {

    }

    public function onFinish(\swoole_http_server $server, $taskId, $data)
    {

    }

    public function onRequest(\swoole_http_request $req, \swoole_http_response $res)
    {
        try {
            $this->beforeRequest($req, $res);
            $parse = $this->parseReq($req);
            $service = $this->kernel->service($parse['service']);
            $result = call_user_func_array([$service, $parse['method']], $parse['params']);
            $this->yar->setReturnValue($result);
            $res->header('Content-Type', 'application/octet-stream');
            $this->afterRequest($req, $res);
            $res->end($this->packer->pack($this->yar));
        } catch (\Exception $e) {
            if (!empty($this->yar)) {
                $res->header('Content-Type', 'application/octet-stream');
                $this->yar->setError($e->getMessage());
                $res->end($this->packer->pack($this->yar));
            } else {
                $res->status(500);
                $res->end($e->getMessage());
            }
        }
    }

    public function onReceive(\swoole_server $server, $fd, $fromId, $data)
    {
        $this->beforeReceive($server, $fd, $fromId, $data);
        $decode = $this->tcpPacker->decode($data);
        if ($decode['msg'] != 'OK') {
            $result = Format::serverException($decode['msg'], $decode['code']);
        } else {
            try {
                $yarSource = $decode['data'];
                $service = $this->kernel->service($yarSource['s']);
                $callResult = call_user_func_array([$service, $yarSource['m']], $yarSource['p']);
                $result = Format::server($callResult);
            } catch (\Exception $e) {
                $result = Format::serverException($e->getMessage(), $e->getCode());
            }
        }
        $encode = $this->tcpPacker->encode($result);
        $this->afterReceive($server, $fd, $fromId, $data);
        $server->send($fd, $encode);
    }

    protected function beforeRequest(\swoole_http_request $req, \swoole_http_response $res)
    {

    }

    protected function afterRequest(\swoole_http_request $req, \swoole_http_response $res)
    {

    }

    protected function beforeReceive(\swoole_server $server, $fd, $fromId, $data)
    {

    }

    protected function afterReceive(\swoole_server $server, $fd, $fromId, $data)
    {

    }

    private function parseReq(\swoole_http_request $req)
    {
        $this->yar = null;
        if ($req->server['request_method'] != 'POST') {
            throw new SYarException("目前只支持post的请求");
        }
        $this->yar = $this->packer->unpack($req->rawContent());
        if ($this->yar->getError()) {
            throw new SYarException("解析错误" . $this->yar->getError());
        }
        if (empty($req->get) || empty($req->get['service'])) {
            throw new SYarException("未正确的请求方式");
        }
        $service = $this->kernel->service($req->get['service']);
        if (!$this->yar->getRequestMethod() || !method_exists($service, $this->yar->getRequestMethod())) {
            throw new SYarException("{$req->get['service']}未含有{$this->yar->getRequestMethod()}方法");
        }
        return array(
            'service' => $req->get['service'],
            'method' => $this->yar->getRequestMethod(),
            'params' => $this->yar->getRequestParams(),
        );
    }
}
