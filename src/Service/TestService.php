<?php
/**
 * Created by PhpStorm.
 * User: lihan
 * Date: 17/1/13
 * Time: 00:40
 */
namespace FSth\SYar\Service;

use FSth\Framework\Context\Object;

class TestService extends Object
{
    public function giveBack($params)
    {
        return $params;
    }
}