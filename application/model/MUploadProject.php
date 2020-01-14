<?php
/**
 * Created by item-upload
 * User: bibo
 * Date: 2020-01-11
 * Time: 10:59
 */

namespace app\model;


use app\common\lib\RedisUtils;
use app\common\lib\Utils;
use think\Db;
use think\Exception;
use think\facade\Config;
use think\Model;

class MUploadProject extends Model
{
    protected $table = TABLE_UPLOAD_PROJECT;
    const M_SIZE = 1048576;

    /**
     * @param $param
     * @return array|bool|\Exception
     */
    public function buildAppSecret($param)
    {
        try{
            Db::table($this->table)
                ->where('app_name', $param['pro'])
                ->update(['state' => 0]);

            $data = [
                'app_name' => trim($param['pro']),
                'key_id' => Utils::getAppKeyId($param['pro']),
                'created_at' => time(),
                'allow_type' => json_encode($param['type']),
                'max_size' => trim($param['size'])
            ];
            $data['key_secret'] = Utils::getAppKeySecret($data['app_name'], $data['key_id'], $data['created_at']);

            $result = Db::table($this->table)->insert($data);

            if ($result) {

                return ['key_id' => $data['key_id'], 'key_secret' => $data['key_secret']];

            } else {

                return false;

            }
        } catch (\Exception $e) {
            Utils::exportError($e);

            return $e;
        }
    }

    /**
     * @param $app_name
     * @param $key_id
     * @param $key_secret
     * @return bool|\Exception
     */
    public function checkSecret($app_name, $key_id, $key_secret)
    {
        try{
            $data = Db::table($this->table)
                ->where('app_name', $app_name)
                ->where('key_id', $key_id)
                ->where('state', KEY_SECRET_STATE_ON)
                ->find();

            if (empty($data)) {
                throw new Exception('secret不存在或已停用');
            }

            if ($data['key_secret'] !== $key_secret) {
                throw new Exception('身份校验失败');
            }

            return true;
        } catch (\Exception $e) {
            Utils::exportError($e);

            return $e;
        }
    }

    public function saveUploadToken($param)
    {
        try{
            $info = Db::table($this->table)
                ->where('key_id', $param['id'])
                ->where('state', KEY_SECRET_STATE_ON)
                ->find();

            if (empty($info)) {
                return ['code' => CODE_TOKEN_ERROR, 'msg' => '无项目信息或已过期'];
            }

            $allow_type = json_decode($info['allow_type'], true);

            if (!isset($allow_type[$param['type']])) {
                return ['code' => CODE_TOKEN_ERROR, 'msg' => '文件类型受限'];
            }
            $expire = config('upload.token_expire');
            $data = [
                "app" => $info['app_name'],
                "allow_type" => json_encode($allow_type[$param['type']]),
                "max_size" => $info['max_size'],
                'expire' => $expire + time(),
                'mode' => $param['mode']
            ];
            $token = Utils::token($data, $info['app_name']);
            if ($token instanceof Exception) {
                return false;
            }

            $key = Utils::getTokenSaveKey($data['app'], $token);
            $result = RedisUtils::getInstance()->set($key, serialize($token), $expire);
            if (!$result) {
                throw new \Exception('SYSTEM_BUSY');
            }

            return $token;
        } catch (\Exception $e) {
            Utils::exportError($e);

            return $e;
        }
    }

    public function veriDecode($param, $decode)
    {
        try{
            if ($decode->app !== $param['app']) {
                throw new Exception('无效key_secret');
            }

            if ($decode->expire < time()) {
                throw new Exception('token已过期');
            }

            $info = Db::table($this->table)
                ->where('key_id', $decode->key_id)
                ->where('state', KEY_SECRET_STATE_ON)
                ->find();

            if (empty($info)) {
                throw new Exception('key_secret已关闭');
            }

            return true;
        } catch (\Exception $e) {
            Utils::exportError($e);

            return $e;
        }
    }

    /**
     * 文件合法性校验
     * @param $decode
     * @param $file
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function checkFile($decode, $file)
    {
        if (Config::get('upload.token_type') === 3) {
            $info = Db::table($this->table)
                ->where('key_id', $decode->key_id)
                ->find();

            $allow_type = json_decode($info['allow_type'],true);
            $allow_type = isset($allow_type[$decode->type]) ? $allow_type[$decode->type] : [];

            $max_size = $info['max_size'];

        } else {
            $allow_type = json_decode($decode->allow_type,true);
            $max_size = $decode->max_size;
        }


        list($_, $type) = explode('/', $file->getMime());
        unset($_);
        if (!in_array($type, $allow_type)) {
            list($_, $type) = explode('.', $file->getInfo()['name']);
            unset($_);
            if (!in_array($type, $allow_type)) {
                throw new \Exception('文件类型受限');
            }
        }

        // 文件大小
        $re = $file->checkSize(self::M_SIZE * $max_size);
        if (!$re) {
            throw new \Exception('文件不能大小不能超过' . $decode->max_size . 'M');
        }

    }
}