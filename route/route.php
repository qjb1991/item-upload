<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

use think\facade\Route;

Route::get('think', function () {
    return 'hello,ThinkPHP5!';
});

Route::get('hello/:name', 'index/hello');

Route::group('api', function () {
    Route::post('secret', 'uploader/secret');       // 创建项目key_id, key_secret

    Route::post('access', 'uploader/access');       // （调试用）身份校验

    Route::post('accessToken', 'uploader/accessToken');     // （调试用）获取令牌接口的access_token
    Route::post('token', 'uploader/token');     // 获取访问令牌
    Route::post('index', 'uploader/index');     // 图片上传


    Route::post('simpleAccessToken', 'uploader/simpleAccessToken');     // （调试用）图片上传token

})->prefix('api/');

return [

];
