<?php

namespace FSth\SYar\Client;

use FSth\Framework\Server\Pack\Handler;
use FSth\Framework\Server\Pack\Packer;
use FSth\SYar\Exception\SYarException;
use FSth\SYar\Tool\Format;
use Fsth\Framework\Client\SYarClient;

class Client extends SYarClient
{

    public function __call($name, $arguments)
    {
        // TODO: Implement __call() method.
        try {
            $this->arguments = !empty($arguments) ? $arguments : [];
            $this->name = $name;

            $this->beforeCall();
            if (empty($this->client) || $this->client->isConnected() === false) {
                $this->tcpConnect();
            }
            $ret = $this->client->send($this->packer->encode(Format::client($this->service, $name, $arguments)));
            $this->checkTcpSendResult($ret);

            $receive = $this->waitTcpResult();
            $result = $this->packer->decode($receive);
            return $this->parser->parse($result['data']);
        } catch (\Exception $e) {
            if (($e->getCode() == 2 || $e->getCode() == 3) && strpos($e->getMessage(), 'Broken pipe') !== false) {
                $this->tcpClose();
            }
            throw new SYarException($e->getMessage(), $e->getCode());
        } finally {
            $this->afterCall();
        }
    }

    private function checkTcpSendResult($ret)
    {
        if (!empty($ret)) {
            return;
        }
        $errorCode = $this->client->errCode;

        $msg = ($errorCode == 0) ? "Connect fail. Check host dns." : \socket_strerror($errorCode);

        throw new SYarException($msg, self::CONNECT_ERROR);
    }
}