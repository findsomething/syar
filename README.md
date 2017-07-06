## syar 

### 说明
1. 用swoole+yar实现了一个简单的rpc调用 目前只实现了单次调用(数据解析部分借鉴了https://github.com/stcer/syar thx)
2. tcp 初步实现了一个类似的rpc调用

### 使用方法

* 启动服务端:
bin/server run|start|stop|restart config/server.php

* 启动带tcp与http的服务端 :
bin/server run|start|stop|restart config/multi-server.php



### 测试
http
```
$client = new \Yar_client("http://127.0.0.1:9503/?service=TestService");
$params = array('hello' => 'world');
$result = $client->giveBack($params);
```

tcp
```
$client = new \FSth\SYar\Client\Client('127.0.0.1', '9504', 'TestService');
$result = $client->giveBack(['hello' => 'world']);
```

### changelog
* 2017-03-17 v0.1.4
```
add rpc with tcp protocol
```

* 2017-05-15 v0.2.0
```
add cmd Ø
```

### 2017-07-06 v0.3.0
```
add zipKin support
```