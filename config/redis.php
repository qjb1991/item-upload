<?php
/**
 * Created by item-upload
 * User: bibo
 * Date: 2020-01-13
 * Time: 14:13
 */
use think\facade\Env;

return [
    'hostname' => Env::get('redis.hostname', '127.0.0.1'),
    'port' => Env::get('redis.port', 6379),
    'dbindex' => Env::get('redis.dbindex', 0),
];