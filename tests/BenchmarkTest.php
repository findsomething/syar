<?php

/**
 * Created by PhpStorm.
 * User: lihan
 * Date: 17/1/13
 * Time: 10:24
 */
class BenchmarkTest
{
    private $url;
    private $n = 1000;
    private $c = 50;

    private $yar = "http://syar.edusoho.net/rpc.php";
    private $syar = "http://127.0.0.1:9503/";

    public function testSYar()
    {
        $this->url = $this->syar;
        echo "syar:". $this->execute();
    }

    public function testYar()
    {
        $this->url = $this->yar;
        echo "yar:". $this->execute();
    }

    public function testConcurrentSYar()
    {
        $workers = [];
        $this->url = $this->syar;
        for ($i = 0; $i < $this->c; $i++) {
            $process = new swoole_process(array($this, "process"), false, false);
            $pid = $process->start();
            $workers[] = $pid;
            echo "syar Worker {$pid} start\n";
        }
        for ($i = 0; $i < $this->c; $i++) {
            $ret = swoole_process::wait();
            echo "syar Worker exit, PID=" . $ret['pid'] . "\n";
        }
    }

    public function testConcurrentYar()
    {

        $workers = [];
        $this->url = $this->yar;
        for ($i = 0; $i < $this->c; $i++) {
            $process = new swoole_process(array($this, "process"), false, false);
            $pid = $process->start();
            $workers[] = $pid;
            echo "yar Worker {$pid} start\n";
        }
        for ($i = 0; $i < $this->c; $i++) {
            $ret = swoole_process::wait();
            echo "yar Worker exit, PID=" . $ret['pid'] . "\n";
        }
    }

    public function process(\swoole_process $worker)
    {
        echo $worker->pid. ":" .$this->execute();
    }

    public function execute()
    {
        $success = 0;
        $failed = 0;
        $client = new \Yar_client($this->url . "?service=TestService");
        $startTime = microtime(true);
        for ($i = 0; $i < $this->n; $i++) {
            $value = ($i % 2 == 0) ? 1 : 2;
            $params = array('hello' => $value);
            try {
                $result = $client->giveBack($params);
                if (!empty($result['hello']) && $result['hello'] === $params['hello']) {
                    $success++;
                } else {
                    $failed++;
                }
            } catch (\Exception $e) {
                var_dump($e->getMessage());
                $failed++;
            }
        }
        $endTime = microtime(true);
        return "cost:" . ($endTime - $startTime) . " success:{$success} failed:{$failed}\n";
    }
}

$test = new BenchmarkTest();
//$test->testSYar();
//$test->testYar();
$test->testConcurrentSYar();
//$test->testConcurrentYar();