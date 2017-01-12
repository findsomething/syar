<?php
/**
 * from https://github.com/stcer/syar.git
 * thx
 */
namespace FSth\SYar\Yar\Encoder;

interface Encoder
{
    function encode($message);
    function decode($message);
}