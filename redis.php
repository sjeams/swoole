
<?php
/*
 * @Author: sjeam
 * @Date: 2022-06-07 10:09:21
 * @Description: 
 */
 
//连接本地的 Redis 服务
$redis = new Redis();
$redis->connect('122.51.2.193', 6379);
$redis->auth('yincan1993');
$key='name';
$value="test";
// $redis->set($key,$value);
// var_dump(11);die;
$redis->hSet('h', 'key1', 'hello'); 
$redis->hSet('h', 'key1', '123');
$redis->hSet('h', 'key2', 'hello');  
$data = $redis->hGetAll('h', 0, -1);
// var_dump($data);die;

//$expireTime = mktime(23, 59, 59, date("m"), date("d"), date("Y"));
//设置键的过期时间 1小时候
$expreTime =strtotime('+1 hours ');
$redis->expireAt($key, $expreTime);
//查看服务是否运行
echo   $redis->get('name');
