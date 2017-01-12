<?php
/**
 * from https://github.com/stcer/syar.git
 * thx
 */
namespace FSth\SYar\Yar\Encoder;

class Json implements Encoder
{
    function encode($message)
    {
        return json_encode($message);
    }

    function decode($message)
    {
        return json_decode($message, true);
    }
}
