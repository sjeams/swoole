<?php
namespace app\API\server;
 
class RedisLib
{
    private static $_instance = null;
    private function __construct(){
        self::$_instance = new \Redis();
        // self::$_instance->connect('127.0.0.1','6379','5');
        self::$_instance->connect('124.221.174.216', 6379,'5');
        self::$_instance->auth('yincan1993');
    }
    private function __clone(){}
 
    public static function getInstance(){
        if(!self::$_instance){
            new self;
        }
 
        return self::$_instance;
    }
 
    public static function setKeyValueArray($redisKey, $obj)
    {
        if (empty($obj) || count($obj) == 0) {
            return false;
        }
        return self::getInstance()->hMSet($redisKey, $obj);
    }
 
    public static function setString($key, $value)
    {
        return self::getInstance()->set($key,$value);
    }
 
    public static function getString($key)
    {
        return self::getInstance()->get($key);
    }
 
    public static function getKeyValueArray($key)
    {
        $obj = array();
        if(empty($key)){
            return $obj;
        }
        if(empty($key)){$key="";}
        $fields = self::getInstance()->hkeys($key);
        if (!empty($fields) && count($fields) > 0)
        {
            $obj = self::getInstance()->hmget($key, $fields);
        }
        return $obj;
    }
 
    public static function getHmget($key,$fields)
    {
        $obj = self::getInstance()->hmget($key, $fields);
        return $obj;
    }
 
    public static function setRedisSet($setName, $obj)
    {
        if (empty($obj) || count($obj) == 0)
            return;
        foreach ($obj as $k => $v)
        {
            self::getInstance()->sAdd($setName, $v);
        }
    }
 
    public static function getRedisSetMember($keyName)
    {
        return self::getInstance()->sMembers($keyName);
    }
 
    public static function getKeyName($keyName, $addCurlyBrace = false)
    {
        if ($addCurlyBrace)
        {
            $_name = '{'.$keyName.'}';
        }
        else
        {
            $_name = $keyName;
        }
        return $_name;
    }
 
    public static function unionSet($set1, $set2)
    {
        return self::getInstance()->sUnion($set1, $set2);
    }
 
    public static function addMemberToSet($setName, $member)
    {
        return self::getInstance()->sAdd($setName, $member);
    }
 
    public static function getKeys($keyName)
    {
        return self::getInstance()->keys($keyName);
    }
 
    public static function deleteByKeys($keys)
    {
        return self::getInstance()->del($keys);
    }
 
    public static function setTimeout($key, $ttl)
    {
        return self::getInstance()->setex($key, $ttl, '');
    }
 
    public static function setEx($key,$ttl,$value)
    {
        return self::getInstance()->setex($key, $ttl, $value);
    }
 
    public static function getValueFromHashKey($key, $field)
    {
        return self::getInstance()->hget($key, $field);
    }
 
    public static function setRedisZSet($key, $score, $value)
    {
        return self::getInstance()->zAdd($key, $score, $value);
    }
 
    public static function getRedisZMembers($key, $start, $members=-1)
    {
        return self::getInstance()->zrange($key, $start, $members);
    }
 
    public static function incrString($key,$number)
    {
        return self::getInstance()->incrBy($key,$number);
    }
 
    public static function getRedisZMembersWithScores($key, $start, $members,$type=false)
    {
        return self::getInstance()->zrange($key, $start, $members,$type);
    }
 
    public static function removeItemFromZSort($key, $value)
    {
        return self::getInstance()->zRem($key, $value);
    }
 
    public static function popFromZSort($key)
    {
        return self::getInstance()->sPop($key);
    }
 
    public static function removeItemFromSet($key, $value)
    {
        return self::getInstance()->sRem($key, $value);
    }
 
    public static function moveItemInSet($srcKey, $destKey, $value)
    {
        return self::getInstance()->sMove($srcKey, $destKey, $value);
    }
 
    public static function lPushToList($key, $value)
    {
        return self::getInstance()->lPush($key, $value);
    }
 
    public static function rPopFromList($key)
    {
        return self::getInstance()->rPop($key);
    }
 
    public static function getListRange($key, $start=0, $limit=-1)
    {
        return self::getInstance()->lrange($key, $start, $limit);
    }
 
    public static function getElementFromList($key, $index)
    {
        return self::getInstance()->lindex($key, $index);
    }
 
    public static function insertAfter($key, $pivot, $value)
    {
        return self::getInstance()->lInsert($key, \Redis::AFTER, $pivot, $value);
    }
 
    public static function insertBefore($key, $pivot, $value)
    {
        return self::getInstance()->lInsert($key, \Redis::BEFORE, $pivot, $value);
    }
 
    public static function getListSize($key)
    {
        $len = self::getInstance()->llen($key);
        return $len;
    }
 
    public static function removeElementFromList($key, $value, $count=1)
    {
        return self::getInstance()->lRem($key, $value, $count);
    }
 
    public static function setElementFromList($key, $index, $value)
    {
        return self::getInstance()->lSet($key, $index, $value);
    }
 
    public static function isListEmpty($key)
    {
        $len = self::getListSize($key);
        return ($len==0);
    }
 
    public static function setValueToHashKey($key, $field, $value)
    {
        return self::getInstance()->hSet($key, $field, $value);
    }
 
    public static function getRedisZMembersRev($key, $start, $end=-1, $withScores = false)
    {
        return self::getInstance()->zrevrange($key, $start, $end, $withScores);
    }
 
    public static function getRedisZRevRank($key, $value)
    {
        return self::getInstance()->zrevrank($key, $value);
    }
 
    public static function getRedisZRank($key, $value)
    {
        return self::getInstance()->zrank($key, $value);
    }
 
    public static function removeRedisZRangeByZRank($key,$start,$end)
    {
        return self::getInstance()->zRemRangeByRank($key, $start,$end);
    }
 
    public static function getHashKeys($key)
    {
        return self::getInstance()->hkeys($key);
    }
 
    public static function removeHashByFieldKey($key, $field)
    {
        return self::getInstance()->hDel($key, $field);
    }
 
    public static function setHashIncrement($key, $field, $score)
    {
        return self::getInstance()->hIncrBy($key, $field, $score);
    }
 
    public static function setZSetIncrement($key, $score, $member)
    {
        return self::getInstance()->zIncrBy($key, $score, $member);
    }
 
    public static function getZSetSize($key)
    {
        return self::getInstance()->zCard($key);
    }
 
    public static function getSetSize($key)
    {
        return self::getInstance()->sCard($key);
    }
 
    public static function getZSetScore($key, $member)
    {
        return self::getInstance()->zScore($key, $member);
    }
 
    public static function getBit($key,$offset)
    {
        return self::getInstance()->getBit($key,$offset);
    }
 
    public static function isSetMember($key, $member)
    {
        return self::getInstance()->sIsMember($key, $member);
    }
 
    public static function expire($key, $time,  $ttl)
    {
        return self::getInstance()->expireAt($key, $time + $ttl);
    }
 
    public static function getAllHashValues($key)
    {
        return self::getInstance()->hGetAll($key);
    }
 
    public static function getExists($key)
    {
        return self::getInstance()->exists($key);
    }
 
    public static function getType($key)
    {
        return self::getInstance()->type($key);
    }
 
    public static function usedb($db)
    {
        return self::getInstance()->select($db);
    }
}