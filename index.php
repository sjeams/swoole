<?php
$clientFds = array();
//创建websocket服务器对象，监听0.0.0.0:9501端口
$ws = new swoole_websocket_server("0.0.0.0", 9501);

$ws->set(
    array(
        'worker_num' => 1,
    )
);
//打开websocket连接事件
$ws->on('open', function(swoole_websocket_server $server, $request) use (&$clientFds) {
    echo "新用户 $request->fd 登录 . \n";
    $clientFds[] = $request->fd;
    //var_dump($clientFds);
});
//监听websocket消息事件 
$ws->on('message', function(swoole_websocket_server $server, $request) use (&$clientFds) {

    foreach ($clientFds as $fd) {
        # code...
        $server->push($fd, $request->data);
    }

});
//关闭连接事件
$ws->on('close', function(swoole_websocket_server $server, $fd) use (&$clientFds) {
    echo "用户 {$fd} 下线. \n";
    $key = array_search($fd, $clientFds);
    unset($clientFds[$key]);
});

$ws->start();