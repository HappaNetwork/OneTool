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

Route::group('console', function () {
    Route::rule('netease/[:act]/[:user_id]', 'console/netease');
    Route::rule('bilibili/[:act]/[:mid]', 'console/bilibili');
    Route::rule('sport/[:act]/[:uid]', 'console/sport');
    Route::rule('iqiyi/[:act]/[:uid]', 'console/iqiyi');
    Route::rule('qq/[:act]/[:uin]', 'console/qq');
    Route::rule('tieba/[:act]/[:uid]', 'console/tieba');
    Route::rule('mihoyo/[:act]/[:uid]', 'console/mihoyo');
    Route::rule('heybox/[:act]/[:uid]', 'console/heybox');
    Route::rule('user/[:act]', 'console/user');
    Route::rule('shop/[:act]', 'console/shop');
    Route::rule('qrcode/[:act]/[:uid]', 'console/qrcode');
});

Route::get('tool/[:act]', 'console/tool');
Route::group('tool', function () {
   Route::post('analyse', 'tool/analyse');
});

Route::group('ajax', function () {
    Route::rule('netease/[:act]', 'ajax/netease');
    Route::rule('bilibili/[:act]', 'ajax/bilibili');
    Route::rule('sport/[:act]', 'ajax/sport');;
    Route::rule('iqiyi/[:act]', 'ajax/iqiyi');
    Route::rule('qq/[:act]', 'ajax/qq');
    Route::rule('tieba/[:act]', 'ajax/tieba');
    Route::rule('mihoyo/[:act]', 'ajax/mihoyo');
    Route::rule('heybox/[:act]', 'ajax/heybox');
    Route::rule('qrcode/[:act]', 'ajax/qrcode');

    Route::rule('user/[:act]', 'ajax/user');
    Route::rule('shop/[:act]', 'ajax/shop');
    Route::rule('agent/[:act]', 'ajax/agent');
});
