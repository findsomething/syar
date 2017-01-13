<?php
/**
 * Created by PhpStorm.
 * User: lihan
 * Date: 17/1/12
 * Time: 23:50
 */
namespace FSth\SYar\Server;

use FSth\Framework\Server\Protocol as BaseProtocol;
use FSth\SYar\Exception\SYarException;
use FSth\SYar\Yar\Packer;

class Protocol extends BaseProtocol
{
    private $packer;
    private $yar;

    public function __construct($kernel)
    {
        parent::__construct($kernel);
        $this->packer = new Packer();
    }

    public function onRequest(\swoole_http_request $req, \swoole_http_response $res)
    {
        try {
            $parse = $this->parseReq($req);
            $service = $this->kernel->service($parse['service']);
            $result = call_user_func_array(array($service, $parse['method']), $parse['params']);
            $this->yar->setReturnValue($result);
            $res->header('Content-Type', 'application/octet-stream');
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