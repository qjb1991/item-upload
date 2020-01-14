<?php
/**
 * Created by item-upload
 * User: bibo
 * Date: 2020-01-14
 * Time: 13:46
 */

namespace app\common\lib;


use Exception;
use OSS\OssClient;
use think\facade\Config;

class AliOssUtils
{
    private static $_instance = null;
    private static $handle = null;
    private static $app_key = null;
    private static $app_secret = null;
    private static $endpoint = null;
    private static $pub_bucket_name = null;
    private static $pri_bucket_name = null;
    private static $expire = null;

    private function __construct()
    {

        // 初始化
        self::$app_key = Config::get('upload.alioss.access_key_id');
        self::$app_secret = Config::get('upload.alioss.access_key_secret');
        self::$pub_bucket_name = Config::get('upload.alioss.pub_bucket_name');
        self::$pri_bucket_name = Config::get('upload.alioss.pri_bucket_name');
        self::$expire = Config::get('upload.alioss.expire');
        self::$endpoint = Config::get('upload.alioss.endpoint_net');

        // 实例化
        self::$handle = new OssClient(self::$app_key, self::$app_secret, self::$endpoint);

    }

    // 获取单例
    public static function getInstance()
    {

        if (!(self::$_instance instanceof self)) {

            self::$_instance = new self();

        }

        return self::$_instance;
    }

    // url签名
    public static function getSignUrl($bucket = '', $object = '', $expire = 0)
    {

        return self::$handle->signUrl($bucket, $object, $expire);
    }


    // 签名
    public static function getSign($param = array(), $is_vod = false)
    {

        $dir = $is_vod ? '/' : date('y-m-d') . '/';
        $now = time();
        $end = $now + self::$expire;
        $expiration = gmt_iso8601($end);

        //最大文件大小.用户可以自己设置
        $condition = array(
            0 => 'content-length-range',
            1 => 0,
            2 => 1048576000
        );
        $conditions[] = $condition;

        //表示用户上传的数据,必须是以$dir开始, 不然上传会失败,这一步不是必须项,只是为了安全起见,防止用户通过policy上传到别人的目录
        $start = array(
            0 => 'starts-with',
            1 => '$key',
            2 => $dir
        );
        $is_vod ? '' : $conditions[] = $start;

        $arr = array('expiration' => $expiration, 'conditions' => $conditions);

        $policy = json_encode($arr);
        $base64_policy = base64_encode($policy);
        $string_to_sign = $base64_policy;
        $signature = base64_encode(hash_hmac('sha1', $string_to_sign, $is_vod ? $param['access_key_secret'] : self::$app_secret, true));

        $response = array();
        $response['accessid'] = $is_vod ? $param['access_key_id'] : self::$app_key;
        $response['host'] = $is_vod ? $param['end_point'] : self::$endpoint;
        $response['policy'] = $base64_policy;
        $response['signature'] = $signature;
        $response['expire'] = $end;
        //这个参数是设置用户上传指定的前缀
        $response['dir'] = $dir;

        return $response;
    }

    public function uploadFile($file_name, $path, $type = 1)
    {
        try {
            switch ($type) {
                case 1 :
                    $type = self::$pub_bucket_name;
                    break;
                case 2 :
                    $type = self::$pri_bucket_name;
                    break;
                default :
                    return false;
            }

            return self::$handle->uploadFile($type, $file_name, $path);
        } catch (Exception $e) {

            return $e->getMessage();
        }

    }

    public function signUrl($object, $timeout = 300)
    {
        try{

            $signed_url = self::$handle->signUrl(self::$pri_bucket_name, $object, $timeout);

            return $signed_url;

        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}