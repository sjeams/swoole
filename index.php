<?php
class swooleServer
{
    public $server;
    public function __construct()
    {
        $this->server = new swoole_websocket_server("0.0.0.0", 9501);
    }

    public function go()
    {

        // 客户端链接时候触发
        $this->server->on('open', function (swoole_websocket_server $server, $request) {
            echo "server: handshake success with fd{$request->fd}\n";
        });
        // 客户端发送消息的时候触发
        $this->server->on('message', function (swoole_websocket_server $server, $frame) {
            echo "receive from {$frame->fd}:{$frame->data},opcode:{$frame->opcode},fin:{$frame->finish}\n";
//            $server->push($frame->fd, "{$frame->data}");
            // 获取到里面所有的链接id，然后逐个广播
            foreach ($this->server->connections as $fd) {
                // 需要先判断是否是正确的websocket连接，否则有可能会push失败
                if ($this->server->isEstablished($fd)) {
                    // push到server 然后客户端接收
                    $this->server->push($fd, json_encode(['on'=>$frame->fd,'msg'=>$frame->data]));
                }
            }
        });
        // 客户端关闭链接的时候触发
        $this->server->on('close', function ($ser, $fd) {
            echo "client {$fd} closed\n";
        });

        // 运行server
        $this->server->start();
    }
}

$ser = new swooleServer();
$ser->go();




















// //连接本地的 Redis 服务
// $redis = new Redis();
// $redis->connect('124.221.174.216', 6379);
// $redis->auth('yincan1993');
// // $key='name';
// // $value="test";
// // $redis->set($key,$value);
// // //$expireTime = mktime(23, 59, 59, date("m"), date("d"), date("Y"));
// // //设置键的过期时间 1小时候
// $expreTime =strtotime('+1 hours ');
// // $redis->expireAt($key, $expreTime);

// $clientFds = array();
// //创建websocket服务器对象，监听0.0.0.0:9501端口
// $ws = new swoole_websocket_server("0.0.0.0", 9501);

// $ws->set(
//     array(
//         'worker_num' => 1,
//     )
// );


// //打开websocket连接事件
// $ws->on('open', function(swoole_websocket_server $server, $request) use (&$clientFds) {
//     echo "新用户 $request->fd 登录 . \n";
//     $clientFds[] = $request->fd;
//     //var_dump($clientFds);
// });
// //监听websocket消息事件 
// $ws->on('message', function(swoole_websocket_server $server, $request) use (&$clientFds) {

//     foreach ($clientFds as $fd) {
//         # code...
//         $server->push($fd, $request->data);
//     }

// });
// //关闭连接事件
// $ws->on('close', function(swoole_websocket_server $server, $fd) use (&$clientFds) {
//     echo "用户 {$fd} 下线. \n";
//     $key = array_search($fd, $clientFds);
//     unset($clientFds[$key]);
// });

// $ws->start();