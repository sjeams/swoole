<?php
/*
 * @Author: sjeam
 * @Date: 2022-06-07 15:40:52
 * @Description: 
 */
$redis = new Redis();
$redis->connect('124.221.174.216', 6379);
$redis->auth('yincan1993');
//3秒钟查询一次任务队列
swoole_timer_tick(3000,function ()use($redis){
    $result = $redis->zRange('queue',0,-1);
    if($result){
        foreach ($result as $res){
            $redis->zRem("queue", $res);
            echo "队列中的".$res."的订单已经处理完毕\n";
        }
    }else{
        echo "目前没有队列订单任务";
    }
});