<?php
/**
 * Created by PhpStorm.
 * User: lihan
 * Date: 17/1/13
 * Time: 00:12
 */
namespace FSth\SYar\Yar\Encoder;

class PHP implements Encoder
{
    function encode($message)
    {
        return serialize($message);
    }

    function decode($message)
    {
        return unserialize($message);
    }
}