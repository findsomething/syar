<?php
/**
 * from https://github.com/stcer/syar.git
 * thx
 */
namespace FSth\SYar\Yar\Encoder;

class Msgpack implements Encoder
{
    function encode($message)
    {
        return \msgpack_pack($message);
    }

    function decode($message)
    {
        return \msgpack_unpack($message);
    }
}
