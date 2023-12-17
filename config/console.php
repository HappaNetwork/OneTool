<?php
// +----------------------------------------------------------------------
// | 控制台配置
// +----------------------------------------------------------------------
return [
    // 指令定义
    'commands' => [
        'netease' => 'app\command\Netease', // 网易云音乐
        'bilibili' => 'app\command\Bilibili', // 哔哩哔哩
        'iqiyi' => 'app\command\Iqiyi', // 爱奇艺
        'qq' => 'app\command\QQ', // QQ
        'sport' => 'app\command\Sport', // 运动
        'tieba' => 'app\command\Tieba', // 百度贴吧
    ],
];
