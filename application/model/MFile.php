<?php
/**
 * Created by item-upload
 * User: bibo
 * Date: 2020-01-13
 * Time: 17:39
 */

namespace app\model;


use app\common\lib\AliOssUtils;
use app\common\lib\OssUtils;
use app\common\lib\Utils;
use think\Db;
use think\facade\Config;
use think\Model;

class MFile extends Model
{
    protected $table = TABLE_FILE;

    /**
     * 本地保存
     * @param $file
     * @param $decode
     * @return array|int|string
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function saveFile($file, $decode)
    {
        $src = $file->getInfo(); // 获取 上传 信息
        $save_path = Config::get('upload.upload_save_path');

        $info = $file->getInfo();
        $move_info = $file->move($save_path);
        if ($move_info) {
            $access_path = Config::get('upload.upload_access_path') . str_replace('\\', '/', $move_info->getSaveName());
            list($_, $type) = explode('/', $src['type']);
            unset($_);
            $save_path = $save_path . DIRECTORY_SEPARATOR . $move_info->getSaveName();
            $md5 = md5_file($save_path);
            $data = Db::table($this->table)
                ->where([
                    'md5' => $md5,
                ])
                ->find();

            if (!empty($data)) {
                Db::table($this->table)
                    ->where('id', $data['id'])
                    ->update(['update_at' => time()]);
                $tmp = DIRECTORY_SEPARATOR.'tmp'.DIRECTORY_SEPARATOR.'upload';
                $_new = $tmp.DIRECTORY_SEPARATOR.$type.'.'.time().'tmp' ;
                if(!IS_WIN) {
                    if(!file_exists($tmp)){
                        mkdir($tmp,0755,true);
                    }
                    rename($save_path,$_new);// 移动
                }else{
                    unlink($save_path);
                }
                $result = [
                    'access_path' => $data['access_url'],
                    'md5' => $data['md5'],
                    'size' => $data['size']
                ];
                return $result;
            }

            $data = [
                'type' => $type,
                'row_name' => $src['name'],
                'access_url' => $access_path,
                'save_url' => $save_path,
                'app_name' => $decode->app,
                'create_at' => time(),
                'md5' => $md5,
                'size' => number_format($info['size']/1024/1024, 2) . 'M',
                'update_at' => time()
            ];

            $result = Db::table($this->table)->insert($data);

            if ($result) {
                $result = [
                    'access_path' => $access_path,
                    'md5' => $md5,
                    'size' => $data['size']
                ];
            }

            return $result;
        }
    }

    public function saveAliOssFile($file, $decode, $oss_type)
    {
        list($_, $type) = explode('.', $file->getInfo()['name']);
        unset($_);

        $src = $file->getInfo(); // 获取 上传 信息
        $md5 = md5_file($src['tmp_name']);

        $data = Db::table($this->table)
            ->where([
                'md5' => $md5,
            ])
            ->find();

        if (!empty($data)) {
            Db::table($this->table)
                ->where('id', $data['id'])
                ->update(['update_at' => time()]);

            $result = [
                'save_path' => $data['save_url'],
                'access_path' => $data['access_url'],
                'md5' => $md5,
                'size' => $data['size'],
            ];
            if ($oss_type == 2) {
//                    $str = $this->pathHandle($access_path, $decode->app);
                $decode_path = AliOssUtils::getInstance()->signUrl($data['save_url']);

                $result['access_path'] =  $decode_path;
            }
            return $result;

        } else {
            $file_name = $decode->app . '/pic/' . Utils::random(5) . time() . '.' . $type;
            $info = AliOssUtils::getInstance()->uploadFile($file_name, $src['tmp_name'], $oss_type);

            unlink($src['tmp_name']);
            if ($info) {
                $access_path = $info['info']['url'];
                list($_, $type) = explode('/', $src['type']);

                $data = [
                    'type' => $type,
                    'row_name' => $src['name'],
                    'access_url' => $access_path,
                    'save_url' => $file_name,
                    'app_name' => $decode->app,
                    'md5' => $md5,
                    'size' => number_format($src['size']/1024/1024, 2) . 'M',
                    'create_at' => time(),
                    'update_at' => time()
                ];

                $result = [
                    'save_path' => $file_name,
                    'access_path' => $access_path,
                    'md5' => $md5,
                    'size' => $data['size'],
                ];

                if ($oss_type == 2) {
//                    $str = $this->pathHandle($access_path, $decode->app);
                    $decode_path = AliOssUtils::getInstance()->signUrl($file_name);

                    $result['access_path'] =  $decode_path;
                }
                Db::table($this->table)->insert($data);

                return $result;
            }
        }

        return false;
    }

    public function pathHandle($path, $app)
    {
        $str = substr($path, strpos($path,$app . '/pic/'));

        return $str;
    }
}