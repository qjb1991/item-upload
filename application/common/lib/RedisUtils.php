<?php
/**
 * Created by item-upload
 * User: bibo
 * Date: 2020-01-13
 * Time: 14:06
 */

namespace app\common\lib;


use think\facade\Config;

class RedisUtils
{
    private static $handler = null;
    private static $_instance = null;
    private static $options = array();

    private function __construct($options = [])
    {
        if (!extension_loaded('redis')) {
            throw new \BadFunctionCallException('not support: redis');      //判断是否有扩展
        }

        self::$handler = new \Redis;
        self::$handler->connect( Config::get('redis.hostname'), Config::get('redis.port') );
        self::$handler->select(Config::get('redis.dbindex'));
    }

    public function __call($name, $arguments)
    {

        // TODO: Implement __call() method.
        if( count( $arguments ) == 1 ){
            return self::$handler->$name( $arguments[0] );

        } else if( count( $arguments ) == 2 ){

            return self::$handler->$name( $arguments[0] , $arguments[1] );

        }else if( count( $arguments ) == 3 ){
            return self::$handler->$name( $arguments[0] , $arguments[1] , $arguments[2] );

        }else if( count( $arguments ) == 4 ){
            return self::$handler->$name( $arguments[0] , $arguments[1] , $arguments[2] ,$arguments[3] );

        }else if( count( $arguments ) == 5 ){
            return self::$handler->$name( $arguments[0] , $arguments[1] , $arguments[2] ,$arguments[3] , $arguments[4] );
        }else{

            throw new \think\Exception( '未定义redis方法' );
        }

    }

    /**
     * @return RedisPackage|null 对象
     */
    public static function getInstance()
    {
        if (!(self::$_instance instanceof self)) {

            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * 禁止外部克隆
     */
    public function __clone()
    {
        trigger_error('Clone is not allow!', E_USER_ERROR);
    }

    /**
     * 写入缓存
     * @param string $key 键名
     * @param string $value 键值
     * @param int $exprie 过期时间 0:永不过期
     * @return bool
     */
    public static function set($key, $value, $exprie = 0)
    {
        if ($exprie == 0) {
            $set = self::$handler->set($key, $value);
        } else {
            $set = self::$handler->setex($key, $exprie, $value);
        }
        return $set;
    }
    public function setnx( $key = ''  , $value = '' , $expire = 0 ){

        $result = self::$handler->setnx( $key , $value );
        if( $result && $expire ) self::$handler->expire( $key , $expire );
        return $result;
    }
    public function setInc( $key = '' ){
        return self::$handler->incr($key);
    }

    public function setDec( $key = '' ){
        return self::$handler->decr($key);
    }


    /**
     * 读取缓存
     * @param string $key 键值
     * @return mixed
     */
    public  function get($key)
    {
        $fun = is_array($key) ? 'Mget' : 'get';
        return self::$handler->{$fun}($key);
    }

    /**
     * 获取值长度
     * @param string $key
     * @return int
     */
    public  function lLen($key)
    {
        return self::$handler->lLen($key);
    }

    /**
     * 将一个或多个值插入到列表头部
     * @param $key
     * @param $value
     * @return int
     */
    public  function LPush($key, $value)
    {
        return self::$handler->lPush($key, $value);
    }

    /**
     * 移出并获取列表的第一个元素
     * @param string $key
     * @return string
     */
    public  function lPop($key)
    {
        return self::$handler->lPop($key);
    }

    /**
     * 移出并获取列表的第一个元素
     * @param string $key
     * @return string
     */
    public  function rPop($key)
    {
        return self::$handler->rPop($key);
    }


    /**
     * 移出并获取列表的第一个元素
     * @param string $key
     * @return string
     */
    public  function  brPop($key, $timeout) {
        return self::$handler->brPop($key, $timeout);
    }

    /**
     * 删除指定元素
     * @param string $key
     * @return string
     */
    public function lRem( $key, $value, $count = 1 ) {
        return self::$handler->lRem( $key, $value, $count );
    }

    /**
     * 无序组合
     * @param $key
     * @param $value
     * @return int
     */
    public  function sAdd($key, $value)
    {
        return self::$handler->sadd($key, $value);
    }

    public  function sIsMember($key, $value)
    {
        return self::$handler->sIsMember($key, $value);
    }

    public  function sRem($key, $value)
    {
        return self::$handler->sRem($key, $value);
    }

    /**
     * 有序组合
     * @param $key
     * @param $value
     * @return int
     */
    public  function zAdd($key, $score , $value)
    {
        return self::$handler->zadd($key, $score , $value);
    }

    /**
     * 有序组合
     * @param $key
     * @param $value
     * @return int
     */
    public  function zCount($key, $min = 0 , $max = 0 )
    {
        return self::$handler->zCount($key, $min , $max);
    }

    /**
     * 有序组合
     * @param $key
     * @param $value
     * @return int
     */
    public  function zCard($key )
    {
        return self::$handler->zCard($key);
    }

    /**
     * 有序组合
     * @param $key
     * @param $value
     * @return int
     */
    public  function zRange( $key, $start, $end , $withscores = null )
    {
        return self::$handler->zRange( $key, $start, $end , $withscores );
    }


    /**
     * 有序组合
     * @param $key
     * @param $value
     * @return int
     */
    public  function zRevRangeByScore( $key, $start, $end , $options = [] )
    {
        return self::$handler->zRevRangeByScore( $key, $end , $start, $options );
    }

    /**
     * 有序组合
     * @param $key
     * @param $value
     * @return int
     */
    public  function zRangeByScore( $key, $start, $end , $options = [] )
    {
        return self::$handler->zRangeByScore( $key, $start, $end , $options );
    }

    /**
     * 有序组合
     * @param $key
     * @param $value
     * @return int
     */
    public  function zRank( $key, $member )
    {
        return self::$handler->zRank( $key,$member );
    }

    /**
     * 哈希
     * @param $key
     * @param $value
     * @return int
     */
    public  function hSet($key, $hkey , $value)
    {
        return self::$handler->hSet($key, $hkey , $value);
    }

    public  function hGet($key, $hkey)
    {
        return self::$handler->hGet($key, $hkey);
    }

    public  function hDel($key, $hkey)
    {
        return self::$handler->hDel($key, $hkey);
    }

    /**
     * 设置过期时间
     */
    public  function expire($key, $exprie)
    {
        return self::$handler->expire($key, $exprie);
    }

    /**
     * 删除键
     */
    public  function del($key)
    {
        return self::$handler->del($key);
    }

    public   function subscribe( $arr , $call ){
        return self::$handler->subscribe( $arr , $call );
    }

    public   function exists($key){
        return self::$handler->exists( $key );
    }

    public function ttl( $key ){
        return self::$handler->ttl( $key );
    }

    public function keys( $pattern ) {
        return self::$handler->keys( $pattern );
    }

    public function watch( $key = '' ) {

        self::$handler->watch( $key );

        return true;
    }

    public function multi() {
        return self::$handler->multi();
    }

    public function exec() {
        return self::$handler->exec();
    }

    public function scan( $pattern  = null , $count = 0 ) {

        $return_arr = [];
        $iterator = null;

        while( $keys = self::$handler->scan( $iterator , $pattern , $count ) ) {

            foreach($keys as $key) {
                array_push( $return_arr , $key );
            }

        }

        return $return_arr;
    }
}