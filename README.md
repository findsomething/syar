## syar 

### 说明
```
1.用swoole+yar实现了一个简单的rpc调用 目前只实现了单次调用
2.数据解析部分借鉴了https://github.com/stcer/syar thx
```

### 使用方法
```
启动服务端
bin/server start|stop config/server.php
```

```
测试
$client = new \Yar_client("http://127.0.0.1:9503/?service=TestService");
$params = array('hello' => 'world');
$result = $client->giveBack($params);
var_dump($result);
```