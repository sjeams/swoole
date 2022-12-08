
<?php
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

 
 
 