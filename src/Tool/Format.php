<?php

namespace FSth\SYar\Tool;

class Format
{
    public static function client($service, $method, $args)
    {
        return [
            'i' => '',
            's' => $service,
            'm' => $method,
            'p' => $args
        ];
    }

    public static function server($result, $status = 0)
    {
        return [
            'i' => '',
            's' => $status,
            'r' => $result,
            'o' => '',
            'e' => null
        ];
    }

    public static function serverException($message, $code, $status = 0)
    {
        return [
            'i' => '',
            's' => $status,
            'r' => '',
            'o' => '',
            'e' => [
                'message' => $message,
                'code' => $code,
            ]
        ];
    }
}