<?php
/**
 * Created by item-upload
 * User: bibo
 * Date: 2020-01-11
 * Time: 14:59
 */
use think\facade\Env;

return [
    'token_type' => 3,      // 1：key_id,key_secret直接上传  2：用户按规则生成access_key换取令牌上传  3：用户按规则生成令牌直接上传
    'token_expire' => 600,

    # 注册上传mode
    'mode' => [
        'LocalMode',    // 上传本地
        'AliOssMode'    // 上传阿里oss,
    ],

    'upload_save_path' => dirname(__DIR__).DIRECTORY_SEPARATOR.'upload',
    'upload_access_path' => 'http://file.upload.com/',

    'alioss' => [
        'access_key_id' => Env::get('alioss.access_key_id', ''),
        'access_key_secret' => Env::get('alioss.access_key_secret', ''),
        'endpoint_net' => Env::get('alioss.endpoint_net', ''),

        'pub_bucket_host' => Env::get('alioss.pub_bucket_host', ''),
        'pub_bucket_name' => Env::get('alioss.pub_bucket_name', ''),
        'pub_endpoint_pir' => Env::get('alioss.pub_endpoint_pir', ''),

        'pri_bucket_host' => Env::get('alioss.pri_bucket_host', ''),
        'pri_bucket_name' => Env::get('alioss.pri_bucket_name', ''),
        'pri_endpoint_pir' => Env::get('alioss.pri_endpoint_pir', ''),

        'expire' => 3600
    ]
];