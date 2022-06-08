<?php
 
 $redis = new Redis();
 $redis->connect('124.221.174.216', 6379);
 $redis->auth('yincan1993');
 $chatMessagesKey = "swoole:message:123";
 $tid=123;
 RedisLib::getInstance()->hSet($chatMessagesKey, $tid, 'hellow');
 $contents = $redis->lRange($chatMessagesKey, 0, -1);
 var_dump($contents);
 die;


require(__DIR__ . '/libs/RedisLib.php');
// RedisLib::getInstance()->hSet($roomOnlinesKey, $request->fd, $fid);
$contents = RedisLib::getInstance()->lRange($chatMessagesKey, 0, -1);
//  var_dump(__DIR__ . '/libs/RedisLib.php');

 var_dump($contents);
 die;
//聊天内容
$chatMessagesKey = "swoole:message:%s";
//房间用户
$roomUserKey = "swoole:room:%s";
//所有在线用户
$roomOnlinesKey = "swoole:onlines";
 
//实例化一个swoole的websocket服务监听本机的9501端口
$server = new swoole_websocket_server("0.0.0.0", 9501);
 
$server->set([
    // 虚拟目录的只想位置，只针对静态的资源  html css js 图片 视频
    'document_root' =>__DIR__.'', // v4.4.0以下版本, 此处必须为绝对路径
    'enable_static_handler' => true,
	'worker_num' => 2,
	// 'daemonize'  => 1,  // 作为守护进程运行，需同时设置log_file
	'log_file'   => __DIR__ . '/log/swoole.log',  // 指定标准输出和错误日志文件
]);
 
$server->on('Request', function ($request, $response) {
    $response->header('Content-Type', 'text/html; charset=utf-8');
    $response->end('<h1>Hello Swoole. #' . rand(1000, 9999) . '</h1>');
});
 
//只需要绑定要监听的ip和端口。如果ip指定为127.0.0.1，则表示客户端只能位于本机才能连接，其他计算机无法连接。
//端口这里指定为9501，可以通过netstat查看下该端口是否被占用。如果该端口被占用，可更改为其他端口，如9502，9503等。
$server->on('open', function (swoole_websocket_server $server, $request) use ($chatMessagesKey, $roomUserKey, $roomOnlinesKey) {
    $fid = $request->get['fid'];
    $tid = $request->get['tid'];
    $type = $request->get['type'];
 
    if($fid && $type) {
 
        //存储在线用户
        RedisLib::getInstance()->hSet($roomOnlinesKey, $request->fd, $fid);
 
        //咨询问题
        if($type == 'ask') {
            $roomUserKey = sprintf($roomUserKey, $fid);
            $chatMessagesKey = sprintf($chatMessagesKey, $fid);
            //上线进入某个房间
            RedisLib::getInstance()->hSet($roomUserKey, $fid, $request->fd);
            //历史聊天内容
            $data = [];
            $contents = RedisLib::getInstance()->lRange($chatMessagesKey, 0, -1);
 
            if($contents) {
                foreach ($contents as $content) {
                    $data[] = json_decode($content, true);
                }
            }
            $msg = [
                'type' => 'open',
                'fid' => $fid,
                'tid' => $tid,
                'content' => $data
            ];
 
            $server->push($request->fd, json_encode($msg));
        }
        //回复问题
        elseif ($type == 'reply') {
            //上线进入某个房间
            $roomUserKey = sprintf($roomUserKey, $tid);
            $chatMessagesKey = sprintf($chatMessagesKey, $tid);
            RedisLib::getInstance()->hSet($roomUserKey, $tid, $request->fd);
 
            //历史聊天内容
            $contents = RedisLib::getInstance()->lRange($chatMessagesKey, 0, -1);
            $data = [];
            if($contents) {
                foreach ($contents as $content) {
                    $data[] = json_decode($content, true);
                }
            }
            $msg = [
                'type' => 'open',
                'fid' => $fid,
                'tid' => $tid,
                'content' => $data
            ];
 
            $server->push($request->fd, json_encode($msg));
        }
 
        echo "你好连接成功{$request->fd}\n";
 
    } else {
        echo "非法请求，连接成功{$request->fd}\n";
    }
 
});
 
$server->on('message', function (swoole_websocket_server $server, $frame) use ($chatMessagesKey, $roomUserKey) {
 
    echo $frame->data, "\r\n";
 
    $msg = json_decode($frame->data, true);
    if(!empty($msg) && isset($msg['fid'])) {
        //咨询问题
        if($msg['type'] == 'ask') {
            $chatMessagesKey = sprintf($chatMessagesKey, $msg['fid']);
        }
        //回复问题
        elseif ($msg['type'] == 'reply') {
            $chatMessagesKey = sprintf($chatMessagesKey, $msg['tid']);
        }
 
        //保存聊天记录
        RedisLib::getInstance()->rPush($chatMessagesKey, $frame->data);
 
        foreach ($server->connections as $key => $fd) {
            if($fd) {
                $server->push($fd, $frame->data);
            }
        }
    }
 
    if($msg['type'] == 'active') {
        echo '我是心跳包, 我还活着', $frame->fd, "\r\n";
    }
 
});
 
$server->on('close', function ($ser, $fd) use($roomOnlinesKey) {
 
    //用户下线了
    if(RedisLib::getInstance()->hExists($roomOnlinesKey, $fd)) {
        RedisLib::getInstance()->hdel($roomOnlinesKey, $fd);
    }
 
    /*$is_websocket = $ser->getClientInfo($fd)['websocket_status'];
    if($is_websocket) {
        echo "client {$fd} closed  websocket status is {$is_websocket}\n";
    } else {
        echo "client {$fd} closed  is not valid websocket connection\n";
    }*/
});
 
$server->start();