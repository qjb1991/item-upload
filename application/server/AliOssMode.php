<?php
/**
 * Created by item-upload
 * User: bibo
 * Date: 2020-01-14
 * Time: 10:03
 */

namespace app\server;


use app\model\MFile;

class AliOssMode extends BaseMode
{
    const SAVE_PUB = 1;
    const SAVE_PRI = 2;
    const M_SIZE = 1048576;

    public function index($decode, $file)
    {
        // TODO: Implement index() method.

        $oss_type = isset($decode->space_type) ? $decode->space_type : self::SAVE_PUB;

        $file = $this->saveAliOss($file, $decode, $oss_type);

        return $file;
    }

    public function saveAliOss($file, $decode, $oss_type)
    {
        if (!$file->isValid()) {
            throw new \Exception('非法上传');
        }

        $model = new MFile();
        $file = $model->saveAliOssFile($file, $decode, $oss_type);

        return $file;
    }
}