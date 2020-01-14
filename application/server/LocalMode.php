<?php
/**
 * Created by item-upload
 * User: bibo
 * Date: 2020-01-13
 * Time: 15:02
 */

namespace app\server;


use app\model\MFile;

class LocalMode extends BaseMode
{
    const M_SIZE = 1048576;

    public function index($decode, $file)
    {
        // TODO: Implement index() method.

        $file = $this->save($file, $decode);  // 保存本地

        return $file;
    }

    public function save($file, $decode)
    {
        if (!$file->isValid()) {
            throw new \Exception('非法上传');
        }

        $model = new MFile();
        $file = $model->saveFile($file, $decode);

        return $file;
    }
}