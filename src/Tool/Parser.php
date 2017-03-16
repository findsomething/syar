<?php

namespace FSth\SYar\Tool;

use FSth\Framework\Tool\ArrayTool;
use FSth\SYar\Exception\SYarException;

class Parser
{
    public function parse($result)
    {
        if (!is_array($result) || !ArrayTool::requireds($result, ['i', 's', 'r', 'o', 'e'])) {
            throw new SYarException("返回内容格式错误");
        }
        if (!empty($result['e'])) {
            throw new SYarException($result['e']['message'], $result['e']['code']);
        }
        return $result['r'];
    }
}