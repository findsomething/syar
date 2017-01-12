<?php
/**
 * Created by PhpStorm.
 * User: lihan
 * Date: 17/1/12
 * Time: 23:16
 */
namespace FSth\SYar\Solid;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class LoggerSolid
{
    public static function logger($name, $mode = Logger::DEBUG)
    {
        $logPath = dirname(__DIR__)."/../var/logs/";
        $logger = new Logger($name);
        $logger->pushHandler(new StreamHandler($logPath.$name.".log", $mode));
        return $logger;
    }
}