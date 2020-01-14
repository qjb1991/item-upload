<?php

namespace app\api\controller;

use app\common\controller\BaseApi;
use app\common\lib\Utils;
use app\model\MUploadProject;
use think\Container;
use think\Exception;
use think\facade\Config;
use think\Request;

class Uploader extends BaseApi
{
    /**
     * @param Request $request
     * @return \think\response
     */
    public function secret(Request $request)
    {
        $param = $request->param();

        $model = new MUploadProject();
        $data = $model->buildAppSecret($param);

        return $this->response($data);
    }

    public function token(Request $request)
    {
        $access = $this->access();

        if (!$access) {
            return $this->response(['code' => CODE_TOKEN_ERROR, 'msg' => '身份校验失败']);
        }

        $param = $this->request->param();

        $model = new MUploadProject();
        $data = $model->saveUploadToken($param);

        return $this->response($data);
    }

    /**
     * @return bool|\Exception
     */
    public function access()
    {
        $param = $this->request->param();

        $model = new MUploadProject();

        if (config('upload.token_type') === 1) {

            $data = $model->checkSecret($param['app'], $param['id'], $param['secret']);

        } elseif (config('upload.token_type') === 2) {

            $access = Utils::verifyToken($param['access_token'], $param['app']);

            if ($access instanceof \Exception) {
                return false;
            }

            $data = $model->checkSecret($param['app'], $access->key_id, $access->key_secret);
        } else {
            $data = false;
        }
        return $data;
    }

    /**
     * @return \think\response
     */
    public function accessToken()
    {
        $param = $this->request->param();

        $data = [
            'key_id' => $param['id'],
            'key_secret' => $param['secret']
        ];
        $data = Utils::token($data, $param['app']);

        return $this->response($data);
    }

    public function simpleAccessToken()
    {
        $param = $this->request->param();

        $data = [
            'key_id' => $param['key_id'],
            'key_secret' => $param['key_secret'],
            'app' => $param['app'],
            'expire' => time() + Config::get('upload.token_expire'),
            'mode' => $param['mode'],
            'type' => $param['type']
        ];
        $data = Utils::token($data, $param['app']);

        return $this->response($data);
    }

    public function index()
    {
        $param = $this->request->post();

        if (Config::get('upload.token_type') === 3) {
            // key_id && key_secret 直接上传文件
            $decode = Utils::verifyToken($param['token'], $param['app']);
            if ($decode instanceof \Exception) {
                return $this->response(['code' => CODE_UPLOAD_FILE, 'msg' => '上传失败' . $decode->getMessage()]);
            }

            $model = new MUploadProject();
            $re = $model->veriDecode($param, $decode);
            if ($re instanceof Exception) {
                return $this->response(['code' => CODE_UPLOAD_FILE, 'msg' => '上传失败' . $re->getMessage()]);
            }

        } else {
            // 通过换取令牌上传
            $decode = Utils::isAliveToken($param['app'], $param['token']);
            if (!$decode) {
                return $this->response(['code' => CODE_UPLOAD_FILE, 'msg' => '令牌无效或已过期']);
            }

        }

        $file = $this->request->file('file');
        if (empty($file)) {
            return $this->response(['code' => CODE_FILE_ERROR, 'msg' => '请选择上传文件']);
        }

        if (is_array($file)) {
            return $this->response(['code' => CODE_FILE_ERROR, 'msg' => '只支持单图片上传']);
        }

        try{
            $mode = Config::get('upload.mode');
            $mode = $mode[$decode->mode - 1];
            $class = "app\\server\\" . $mode;

            if (class_exists($class)) {
                $model = Container::getInstance()->invokeClass($class);
            } else {
                return $this->response(['code' => CODE_FILE_ERROR, 'msg' => '存储方式不存在']);
            }

            if (isset($param['type']) && !empty($param['type'])) {
                $decode->space_type = (int)$param['type'];
            }

            $project_model = new MUploadProject();
            // 校验文件合法性
            $project_model->checkFile($decode, $file);

            $result = $model->index($decode, $file);

            return $this->response($result);
        } catch (\Exception $e) {
            Utils::exportError($e);

            return $this->response(['code' => CODE_UPLOAD_FILE, 'msg' => empty($e->getMessage()) ? '上传失败' : $e->getMessage()]);
        }
    }

}
