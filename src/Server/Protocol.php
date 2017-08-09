<?php
/**
 * Created by PhpStorm.
 * User: lihan
 * Date: 17/1/12
 * Time: 23:50
 */
namespace FSth\SYar\Server;

use FSth\Framework\Context\Context;
use FSth\Framework\Extension\ZipKin\RequestKin;
use FSth\Framework\Server\Pack\Handler as TcpHandler;
use FSth\Framework\Server\Protocol as BaseProtocol;
use FSth\Framework\Tool\StandardTool;
use FSth\SYar\Exception\SYarException;
use FSth\SYar\Tool\Format;
use FSth\SYar\Yar\Packer;
use FSth\Framework\Server\Pack\Packer as TcpPacker;
use whitemerry\phpkin\Tracer;

class Protocol extends BaseProtocol
{
    protected $packer;
    protected $yar;
    protected $tcpPacker;

    protected $parse;

    protected $receiveParse;

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
            $this->parse = null;
            $this->parse = $parse = $this->parseReq($req);
            $this->beforeRequest($req, $res);
            $service = $this->kernel->service($parse['service']);
            $result = call_user_func_array([$service, $parse['method']], $this->parse['params']);
            $this->yar->setReturnValue($result);
            $res->header('Content-Type', 'application/octet-stream');
            $res->end($this->packer->pack($this->yar));
        } catch (\Exception $e) {
            if ($e->getCode() != 1001) {
                $this->logger->error('syar onRequest error', [
                    'error' => $e->getMessage(),
                    'code' => $e->getCode(),
                    'parse' => $this->parse,
                ]);
            }
            if (!empty($this->yar)) {
                $res->header('Content-Type', 'application/octet-stream');
                $this->yar->setError($e->getMessage());
                $res->end($this->packer->pack($this->yar));
            } else {
                $res->status(500);
                $res->end($e->getMessage());
            }
        } finally {
            $this->afterRequest($req, $res);
        }
    }

    public function onReceive(\swoole_server $server, $fd, $fromId, $data)
    {
        $this->receiveParse = null;
        $this->receiveParse = $decode = $this->tcpPacker->decode($data);
        $this->beforeReceive($server, $fd, $fromId, $data);
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
                $this->logger->error('syar onReceive error', [
                    'error' => $e->getMessage(),
                    'code' => $e->getCode(),
                    'parse' => $this->receiveParse
                ]);
            }
        }
        $encode = $this->tcpPacker->encode($result);
        $this->afterReceive($server, $fd, $fromId, $data);
        $server->send($fd, $encode);
    }

    protected function beforeRequest(\swoole_http_request $req, \swoole_http_response $res)
    {
        $this->context = new Context();
        $traceConfig = $this->kernel->config('trace');
        $traceHeader = $this->getTraceHeaderByRequest();

        if (empty($traceConfig['execute']) || !$traceConfig['execute']) {
            return false;
        }
        $serverConfig = $this->kernel->config('server');
        $serverName = !empty($serverConfig['name']) ? $serverConfig['name'] : "testYar";
        $traceHeader['request_uri'] = StandardTool::toSpanName($this->parse['service'], $this->parse['method']);

        $this->initTrace($serverName, $serverConfig['host'], $serverConfig['port'], $traceConfig['setting'],
            $traceHeader);
    }

    protected function beforeReceive(\swoole_server $server, $fd, $fromId, $data)
    {
        $this->context = new Context();
        $traceConfig = $this->kernel->config('trace');
        $data = !empty($this->receiveParse['data']) ? $this->receiveParse['data'] : [];
        if (empty($traceConfig['execute']) || !$traceConfig['execute'] || empty($data)) {
            return false;
        }
        $traceHeader = $this->getTraceHeaderByReceive();
        $serverConfig = $this->kernel->config('server');
        $serverName = !empty($serverConfig['name']) ? $serverConfig['name'] : "testYar";
        $traceHeader['request_uri'] = StandardTool::toSpanName($data['s'], $data['m']);

        $this->initTrace($serverName, $serverConfig['host'], $serverConfig['port'], $traceConfig['setting'],
            $traceHeader);
    }

    protected function afterReceive(\swoole_server $server, $fd, $fromId, $data)
    {
        $this->handleAfter();
    }

    protected function getTraceHeaderByRequest()
    {
        $traceHeader = [];
        $num = count($this->parse['params']);
        if ($num >= 1) {
            if (!empty($this->parse['params'][$num - 1]['traceHeader'])) {
                $traceHeader = $this->parse['params'][$num - 1]['traceHeader'];
                unset($this->parse['params'][$num - 1]);
            }
        }
        return $traceHeader;
    }

    protected function getTraceHeaderByReceive()
    {
        $traceHeader = [];
        if ($this->receiveParse['msg'] == 'OK') {
            $num = count($this->receiveParse['data']['p']);
            if ($num >= 1) {
                if (!empty($this->receiveParse['data']['p'][$num - 1]['traceHeader'])) {
                    $traceHeader = $this->receiveParse['data']['p'][$num - 1]['traceHeader'];
                    unset($this->receiveParse['data']['p'][$num - 1]['traceHeader']);
                }
            }
        }
        return $traceHeader;
    }

    private function parseReq(\swoole_http_request $req)
    {
        $this->yar = null;
        if ($req->server['request_method'] != 'POST') {
            throw new SYarException("目前只支持post的请求", 1001);
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
