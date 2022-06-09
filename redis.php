
<?php
/*
 * @Author: sjeam
 * @Date: 2022-06-07 10:09:21
 * @Description: 
 */
 
//连接本地的 Redis 服务
$redis = new Redis();
$redis->connect('124.221.174.216', 6379);
$redis->auth('yincan1993');
$key='name';
$value="test";
// $redis->set($key,$value);

$redis->hSet('h', 'key1', 'hello'); 
$data = $redis->lRange('h', 0, -1);
var_dump($data);die;

//$expireTime = mktime(23, 59, 59, date("m"), date("d"), date("Y"));
//设置键的过期时间 1小时候
$expreTime =strtotime('+1 hours ');
$redis->expireAt($key, $expreTime);
//查看服务是否运行
echo   $redis->get('name');
