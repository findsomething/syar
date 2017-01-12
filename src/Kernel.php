<?php
/**
 * Created by PhpStorm.
 * User: lihan
 * Date: 17/1/12
 * Time: 23:15
 */
namespace FSth\SYar;

use FSth\Framework\Context\Kernel as BaseKernel;
use FSth\SYar\Exception\SYarException;
use FSth\SYar\Solid\LoggerSolid;

class Kernel extends BaseKernel
{
    const SERVICE = "service_%s";

    public function boot()
    {
        $this->init();
    }

    protected function init()
    {
        $this['logger'] = function ($kernel) {
            return LoggerSolid::logger('run', $kernel->config['log_level']);
        };

        $this['namespace'] = __NAMESPACE__ . "\\";
        $this->registerService();
    }

    protected function registerService()
    {
        if (!empty($this->config('registers'))) {
            $registers = include($this->config('registers'));

            $serviceName = $this['namespace'] . "Service\\";
            foreach ($registers as $register) {
                $class = $serviceName . $register;
                if (class_exists($class)) {
                    $this[sprintf(self::SERVICE, $register)] = function ($kernel) use ($class) {
                        return new $class($kernel);
                    };
                }
            }
        }
    }

    public function service($name)
    {
        $service = sprintf(self::SERVICE, $name);
        if ($this->offsetExists($service)) {
            return $this[$service];
        }
        throw new SYarException("找不到名为{$name}的Service");
    }
}