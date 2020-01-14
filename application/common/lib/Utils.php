<?php
/**
 * Created by item-upload
 * User: bibo
 * Date: 2020-01-11
 * Time: 11:04
 */

namespace app\common\lib;


use Firebase\JWT\JWT;
use think\Exception;
use think\facade\Config;
use think\facade\Log;

class Utils
{
    const KEY_SECRET_SALT = '*(&^%$';

    public static function exportError(\Exception $e = null, $type = 'error')
    {
        if (empty($e) || !($e instanceof \Exception)) {
            Log::error('unknown error');
        }

        Log::error('error code [ ' . $e->getCode() . '] file: ' . $e->getFile() . ' at line: ' . $e->getLine() . '  error msg : ' . $e->getMessage());
    }

    public static function getAppKeyId($pro)
    {
        return substr($pro, 0, 2) . self::random(10);
    }

    /**
     * 随机 生成 字符串
     * @param $numeric [只随机数字]
     * @param int $length [随机字符串长度]
     * @return string
     */
    public static function random($length = 1, $numeric = 0)
    {
        if ($numeric) {
            $hash = sprintf('%0' . $length . 'd', mt_rand(0, pow(10, $length) - 1));
        } else {
            $hash = '';
            $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz';
            $max = strlen($chars) - 1;
            for ($i = 0; $i < $length; $i++) {
                $hash .= $chars[mt_rand(0, $max)];
            }
        }
        return $hash;
    }

    /**
     * @param $app_name
     * @param $key_id
     * @param $time
     * @return string
     * @throws Exception
     */
    public static function getAppKeySecret($app_name, $key_id, $time)
    {
        if (empty($app_name) || empty($key_id) || empty($time)) {
            throw new Exception('empty param');
        }

        return sha1(sha1($app_name . $key_id . $time . self::KEY_SECRET_SALT) . self::KEY_SECRET_SALT);
    }

    public static function verifyToken($token = null, $app_name = null, $alg = array(DEF_ALG))
    {
        try{

            if (empty($token) || empty($app_name)) {
                throw new Exception('empty data');
            }

            $decode = JWT::decode($token, $app_name, $alg);

            return $decode;
        } catch (\Exception $e) {
            Log::error('token_error----->' . $e->getMessage());
            return $e;
        }

    }

    /**
     * @param $data
     * @param $app_name
     * @param string $alg
     * @return \Exception|string
     */
    public static function token($data, $app_name, $alg = DEF_ALG)
    {
        try{
            if (empty($data)) {
                throw new Exception('empty data');
            }

            if (empty($app_name)) {
                throw new Exception('empty app name');
            }

            $token = JWT::encode($data, $app_name, $alg);
            if (!$token) {
                Log::error(var_export(['data' => $data, 'key' => $app_name, 'alg' => $alg], true));
                throw new Exception('encode token error');
            }

            return $token;
        } catch (\Exception $e) {
            Utils::exportError($e);

            return $e;
        }
    }

    /**
     * @param $app
     * @param $token
     * @return string
     */
    public static function getTokenSaveKey($app, $token)
    {
        $arr = explode('.', $token);

        return $app . '_' . md5($arr[count($arr) - 1]);
    }

    public static function isAliveToken($app, $token)
    {
        $decode = self::verifyToken($token, $app);

        if ($decode instanceof \Exception) {
            return false;
        }

        if (empty($decode->app) || empty($decode->allow_type) || empty($decode->max_size) || empty($decode->expire)) {
            return false;
        }

        if ($decode->expire < time()) {
            return false;
        }

        $key = self::getTokenSaveKey($app, $token);
        $_token = RedisUtils::getInstance()->get($key);

        $_token = unserialize($_token);
        if (!$_token || $_token !== $token) {
            return false;
        }

        return $decode;
    }
}