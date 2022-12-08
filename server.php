<?php
// require_once(__DIR__ . '/server_room.php');
defined('SWOOLE_SERVER') OR define('SWOOLE_SERVER','0.0.0.0');
// 面向过程编程
// 使用文件缓存  获取用户在线数------------------
function getOnlineUserNum(){
	$data = file_get_contents(	__DIR__ .'/chats/user_num.txt');
	file_put_contents(	__DIR__ . '/chats/user_log.txt',$data);
	$data =max($data,0);
	return $data;
}
// 使用文件缓存 增加用户在线数
function setIncOnlineUserNum($type = null){
	if($type == 'init'){
		$num = 0;
	}else{
		$num = getOnlineUserNum() + 1;
	}
	file_put_contents(	__DIR__ . '/chats/user_num.txt',$num);
	return $num;
}
// 使用文件缓存 减少用户在线数
function setDecOnlineUserNum($type = null){
	if($type == 'init'){
		$num = 0;
	}else{
		$num = getOnlineUserNum() - 1;
	}
	file_put_contents(	__DIR__ . '/chats/user_num.txt',$num);
	return $num;
}


/**
 * WebSocket 服务器------------------
 */
setIncOnlineUserNum('init');
//创建websocket服务器对象，监听0.0.0.0:9502端口
$ws = new swoole_websocket_server(SWOOLE_SERVER, 9501);
$ws->set(
    array(
        'worker_num' => 2,
        'daemonize'  => 1,  // 作为守护进程运行，需同时设置log_file
		'enable_static_handler' => true,
		'heartbeat_check_interval' => 1800,//5秒侦测一次心跳，
		'heartbeat_idle_time' => 1810,
        'log_file'   => __DIR__ . '/log/swoole.log',  // 指定标准输出和错误日志文件
    )
);


//监听WebSocket连接打开事件
$ws->on('open', function ($ws, $request) {
	echo 'WS-'.$request->fd . ' connected. '.PHP_EOL;
	file_put_contents(	__DIR__ . '/chats/user_log.txt',$request);
	$num = setIncOnlineUserNum();
	$room_id =$request->get['room']; //房间号
	foreach ($ws->connections as $fd) {
        // 判断websocket连接是否正确，否则会push失败
        if($request->fd == $fd){
			//读取缓存
			$content = getChatMessages($room_id);
			$data = [
				'num' => $num,
				'msg' => $request->fd.' 进入了聊天室',
				'fd' => $request->fd,
				'room' =>$room_id,
				'content' => $content,
				'type' => 'USER_IN'
			];
			$ws->push($request->fd, json_encode($data));
		}else{
			$data = [
				'num' => $num,
				'msg' => $request->fd.' 进入了聊天室',
				'room' => $request->room,
				'content' => '',
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
	$room_id =$frame->get['room']; //房间号
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
				//写入缓存
				addChatMessages($room_id,$frame->data);
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









require_once(__DIR__ . '/libs/RedisLib.php');
// //房间号
// function room($room){
// 	$room_id = implode('_',$room);
// 	return $room_id;
// }

//读取聊天记录缓存-----------------
function getChatMessages($room){
	$message = "message:".$room;
	//历史聊天内容
	$contents = RedisLib::getInstance()->lRange($message, 0, -1);
	return $contents;
}
//写入聊天记录缓存
function addChatMessages($room,$frame){
	$message = "message:".$room;
	//历史聊天内容
	RedisLib::getInstance()->lPush($message, $frame->data);
}

/**
 *   $room_id    当前房间id    
 */
function get_push_room($room){
	$room_id = "room:".$room;
	// hset(name, key, value)
	$fds = RedisLib::getInstance()->smembers($room_id);
	return $fds;
}

function push_room($room,$fd){
	$room_id = "room:".$room;
 	$fd =RedisLib::getInstance()->sAdd($room_id,$fd);
}

function remove_fd($room,$fd){
	$room_id = "room:".$room;
    //用户下线了--删除元素
	RedisLib::getInstance()->srem($room_id,$fd);
}