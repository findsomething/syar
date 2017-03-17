<?php

return [
    'bootstrap' => dirname(__DIR__). "/bootstrap/bootstrap-server.php",
    'host' => '0.0.0.0',
    'port' => '9503',
    'tcpPort' => '9504',
    'daemonize' => false,
    'pid_file' => dirname(__DIR__)."/var/locks/server.pid",
    'server' => "FSth\\SYar\\Server\\Server",
    'protocol' => "FSth\\SYar\\Server\\Protocol",
    'setting' => [
        'max_conn' => 200,
        'worker_num' => 8,
        'dispatch_mode' => 2,  //1：轮询 2：固定 3：抢占 4：IP分配 5：uid分配
        'task_worker_num' => 20, // task 数量
        'task_max_request' => 300, // 执行100次退出 以防内存泄露
        'task_ipc_mode' => 1, //unix socket通信
        'heartbeat_idle_time' => 600, // 心跳超时关闭 s
        'heartbeat_check_interval' => 120, // 心跳间隔
        'buffer_output_size' => 134217728, // 数据发送缓存区
        'pipe_buffer_size' => 134217728, // 管道通信的内存缓存区长度
        'socket_buffer_size' => 134217728, // 包括socket底层操作系统缓存区、应用层接收数据内存缓存区、应用层发送数据内存缓冲区
    ],
    'tcpSetting' => [
        'open_length_check' => 1,
        'package_length_type' => 'N',
        'package_length_offset' => 0,
        'package_body_offset' => 4,
    ],
];
