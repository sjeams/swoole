
<?php
require_once(__DIR__ . '/libs/RedisLib.php');

//房间号
function room($uid_list){
	$room_id = implode('_',$uid_list);
	return $room_id;
}

//读取聊天记录缓存-----------------
function getChatMessages($uid_list){
	$message = "message:".room($uid_list);
	//历史聊天内容
	$contents = RedisLib::getInstance()->lRange($message, 0, -1);
	return $contents;
}
//写入聊天记录缓存
function addChatMessages($uid_list,$frame){
	$message = "message:".room($uid_list);
	//历史聊天内容
	RedisLib::getInstance()->lPush($message, $frame->data);
}

/**
 *   $room_id    当前房间id    
 */
function get_push_room($uid_list){
	$room_id = "room:".room($uid_list);
	// hset(name, key, value)
	$fds = RedisLib::getInstance()->smembers($room_id);
	return $fds;
}

function push_room($uid_list,$fd){
	$room_id = "room:".room($uid_list);
 	$fd =RedisLib::getInstance()->sAdd($room_id,$fd);
}

function remove_fd($uid_list,$fd){
	$room_id = "room:".room($uid_list);
    //用户下线了--删除元素
	RedisLib::getInstance()->srem($room_id,$fd);
}

 
 
 