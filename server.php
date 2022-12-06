<?php
defined('SWOOLE_SERVER') OR define('SWOOLE_SERVER','0.0.0.0');
 
// 面向过程编程
// 使用文件缓存  获取用户在线数
function getOnlineUserNum(){
	$data = file_get_contents('./chats/user_num.txt');
	return $data;
}
// 使用文件缓存 增加用户在线数
function setIncOnlineUserNum($type = null){
	if($type == 'init'){
		$num = 0;
	}else{
		$num = getOnlineUserNum() + 1;
	}
	file_put_contents('./chats/user_num.txt',$num);
	return $num;
}
// 使用文件缓存 减少用户在线数
function setDecOnlineUserNum($type = null){
	if($type == 'init'){
		$num = 0;
	}else{
		$num = getOnlineUserNum() - 1;
	}
	file_put_contents('/chats/user_num.txt',$num);
	return $num;
}
 
 
 
/**
 * WebSocket 服务器
 */
setIncOnlineUserNum('init');
 
//创建websocket服务器对象，监听0.0.0.0:9502端口
$ws = new swoole_websocket_server(SWOOLE_SERVER, 9501);
$ws->set(
    array(
        'worker_num' => 2,
        'daemonize'  => 1,  // 作为守护进程运行，需同时设置log_file
        'log_file'   => __DIR__ . './log/swoole.log',  // 指定标准输出和错误日志文件
    )
);
//监听WebSocket连接打开事件
$ws->on('open', function ($ws, $request) {
	echo 'WS-'.$request->fd . ' connected. '.PHP_EOL;
	
	$num = setIncOnlineUserNum();
	
	foreach ($ws->connections as $fd) {
        // 判断websocket连接是否正确，否则会push失败
        if($request->fd == $fd){
			$data = [
				'num' => $num,
				'msg' => $request->fd.' 进入了聊天室',
				'fd' => $request->fd,
				'type' => 'USER_IN'
			];
			$ws->push($request->fd, json_encode($data));
		}else{
			$data = [
				'num' => $num,
				'msg' => $request->fd.' 进入了聊天室',
				'type' => 'USER_IN'
			];
			if ($ws->isEstablished($fd)) {
				$ws->push($fd, json_encode($data));
			}
		}
    }
});
 
//监听WebSocket消息事件
$ws->on('message', function ($ws, $frame) {
    echo "Receive Message: {$frame->data}\n";
	
	foreach ($ws->connections as $item_fd) {
		if($item_fd != $frame->fd){
			$data = [
				//'num' => $num,
				'msg' => $frame->data,
				'type' => 'USER_MSG',
				'from_fd' => $frame->fd
			];
			
			// 判断websocket连接是否正确，否则会push失败
			if ($ws->isEstablished($item_fd)) {
				$ws->push($item_fd, json_encode($data));
			}
		}else{
			$data = [
				//'num' => $num,
				'msg' => $frame->data,
				'type' => 'USER_MSG',
				'from_fd' => $frame->fd
			];
			$ws->push($frame->fd, json_encode($data));
		}
    }
	
});
 
//监听WebSocket连接关闭事件
$ws->on('close', function ($ws, $fd) {
    echo "WS-{$fd} is closed.".PHP_EOL;
	
	$num = setDecOnlineUserNum();
	$data = [
		'num' => $num,
		'msg' => $fd.' 离开了聊天室',
		'type' => 'USER_OUT'
	];
	
	foreach ($ws->connections as $item_fd) {
		// 推送给除自己之外的所有人
		if($item_fd != $fd){
			// 判断websocket连接是否正确，否则会push失败
			if ($ws->isEstablished($item_fd)) {
				$ws->push($item_fd, json_encode($data));
			}
		}
    }
	
	echo "当前在线人员有：".$num . PHP_EOL;
	
});
 
$ws->start();