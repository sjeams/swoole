<?php
/*
 * @Author: sjeam
 * @Date: 2022-06-07 10:09:21
 * @Description: 
 */
/**
 * Created by PhpStorm.
 * User: 印第安老斑鸠
 * Date: 2019/2/14
 * Time: 10:30
 */
$server = new  swoole_websocket_server("0.0.0.0",9501);
$server->set(array(
    'worker_num'=>2,
));
$redis = new redis();
$redis->connect('124.221.174.216', 6379);
$server->on('open', function (Swoole\WebSocket\Server $server, $request) {});
$server->on('message', function (Swoole\WebSocket\Server $server, $frame) use ($redis){
    //将任务安装时间丢进redis的有序集合之中（实际上要同步存进数据库）
    $result = $redis->zAdd('queue',microtime(true),'用户user'.time());
    if ($result){
        swoole_timer_after($frame->data,function () use($frame,$server){
            //做异常捕获，失败的话就通知失败
            try{
                $str = "你好，你在".(($frame->data)/1000).'秒前的预定的套餐，请到店内柜台前拿取';
                $server->push($frame->fd,$str);
            }catch (Exception $exception){
                $server->push($frame->fd,'订单失败');
            }
        });
    }
    $server->push($frame->fd,"支付成功。请稍后,接收通知!");
});
$server->on('close', function ($ser, $fd) {
    echo "client {$fd} closed\n";
});
$server->start();