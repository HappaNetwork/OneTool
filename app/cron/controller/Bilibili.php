<?php

namespace app\cron\controller;

use app\index\model\TaskLogs;
use app\index\model\Info;
use app\index\model\Accounts;
use app\index\model\Jobs;
use app\index\model\Tasks;
use app\index\model\Users;
use bilibili\BiliHelper;
use Exception;
use think\facade\Request;

class Bilibili extends Common
{
    public function index()
    {
        $cronkey = Request::get('cronkey');
        if (empty($cronkey) || $cronkey != config('sys.cronkey')) {
            $res = ['code' => -1000, 'message' => 'CronKey Access Denied!'];
            exit(json_encode($res, JSON_UNESCAPED_UNICODE));
        }
        $urls = [];
        $vip_expired_userIds = [];
        $jobs = Jobs::getUnexecutedList('bilibili', [['do', '<>', 'globalroom']]); // 获取未执行任务列表
        if (count($jobs) == 0) {
            return resultJson(-1002, '没有要执行的任务');
        }
        foreach ($jobs as $job) {
            if (in_array($job['user_id'], $vip_expired_userIds)) continue;
            $user = Users::where('uid' , '=' , $job['uid'])->find();
            if($user == null){
                Jobs::delJob('bilibili',$job['user_id']);
                continue;
            }
            $task = Tasks::where('type', '=', 'bilibili')->where('execute_name', '=', $job['do'])->find();
            $account = Accounts::where('type', '=', 'bilibili')->where('user_id', '=', $job['user_id'])->find();
            if ($account == null) {
                Accounts::delById($job['user_id']);
                Jobs::delJob('bilibili',$job['user_id']);
                continue;
            }
            if ($task['vip'] == 1 && strtotime($user['vip_end'] ?? '') < time()) {  // 判断会员功能、用户会员是否过期
                $this->vipExpired('bilibili', $user['uid'], $job['user_id']); // 会员过期处理
                // 将VIP过期的任务用户id放入一个数组，用于后续判断
                $vip_expired_userIds[] = $job['user_id'];
                continue;
            } else {
                $urls[] = $this->getExecuteUrl($job['user_id'], $job['do']);
            }
            Info::where('sysid','=','100')->inc('times',1)->update();
            Info::where('sysid','=','100')->update(['last' => date('Y-m-d H:i:s')]);
            Jobs::updateJobInfo($job['do'], $job['user_id'], [ // 更新任务执行信息
                'lastExecute' => date("Y-m-d H:i:s"),
                'nextExecute' => isset($account['timing']) ? strtotime($account['timing'].'+1 day') : time() + $task['execute_rate'],
            ]);
        }
        if ($urls) $this->curl_mulit($urls);
        return resultJson(1000, '执行任务成功');
    }

    public function execute($do)
    {
        $data = Request::get();
        if (isset($data['runkey']) && $data['runkey'] == RUN_KEY) {
            $bilibili = new BiliHelper($data['mid'], $data['mid_md5'], $data['token'], $data['csrf'], $data['access_key'], $data); // 实例化 BiliHelper

            $execute = $bilibili->{$do}();
            if ($bilibili->cookiezt) {
                $account = Accounts::where('type', '=', 'bilibili')->where('user_id', '=', $data['mid'])->find();
                $user = Users::where('uid', '=', $account['uid'])->find();
                $this->accountInvalid('bilibili', $user, $data['mid']); // 账号失效处理
            } else {
                TaskLogs::operateExecuteLog('bilibili', $data['mid'], $do, $execute['message']); // 写入运行日志
            }
        } else {
            return resultJson(-1001, 'RunKey Access Denied!');
        }
    }


    private function getExecuteUrl($user_id, $do)
    {
        $job = Jobs::where(['user_id' => $user_id, 'do' => $do])->find();
        try {
            $job_data = unserialize($job['data']);
        } catch (Exception $e) {
            TaskLogs::operateExecuteLog('bilibili', $user_id, $do, '获取功能配置失败，请重新添加账号'); // 写入运行日志
            Jobs::where(['user_id' => $user_id, 'do' => $do])-> update(['state' => 0]);
            return false;
        }
        $job_config = $job['data'] ? '&'.http_build_query($job_data) : '';
        $account = Accounts::where('type', '=', 'bilibili')->where('user_id', '=', $user_id)->find();
        $account_info = unserialize($account['data']);
        $url = get_Domain() . "cron/bilibili/{$do}?mid={$account_info['mid']}&mid_md5={$account_info['mid_md5']}&token={$account_info['token']}&csrf={$account_info['csrf']}&access_key={$account_info['access_key']}" . $job_config . "&runkey=" . RUN_KEY;
        return $url;
    }

}