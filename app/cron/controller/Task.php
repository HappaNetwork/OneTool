<?php

namespace app\cron\controller;

error_reporting(0);

use app\index\model\Accounts;
use app\index\model\Info;
use app\index\model\Jobs;
use app\index\model\TaskLogs;
use think\Exception;
use think\facade\Request;

class Task extends Common
{
    public function index()
    {
        $cronkey = Request::get('cronkey');
        if (empty($cronkey) || $cronkey != config('sys.cronkey')) {
            $res = ['code' => -1000, 'message' => 'CronKey Access Denied!'];
            exit(json_encode($res, JSON_UNESCAPED_UNICODE));
        }
        $sysid = 201;
        $interval = (int)config('sys.interval');

        $nump = Jobs::where('state', '=', 1)->whereTime('nextExecute', '<=', time())->count('id');
        $xz = ceil($nump / $interval);
        $shu = Info::where('sysid', '=', $sysid)->find();
        $times = $shu['times'];
        if ($times >= $xz) {
            $shu->times = 1;
            $shu->save();
            $times = 0;
        } else {
            $shu->times = $times + 1;
            $shu->save();
        }
        $times = $times * $interval;
        $row = (new \app\index\model\Jobs)->with(['user', 'account', 'task'])->where('type', '=', 'heybox')->where('state', '=', 1)->where('state', '=', 1)->whereTime('nextExecute', '<=', time())->order('nextExecute', 'asc')->limit($times, $interval)->select()->toArray();
        $vip_expired_userIds = [];
        $urls = [];
        foreach ($row as $res) {
            if (in_array($res['user_id'], $vip_expired_userIds)) continue;
            if (strtotime($res['user']['vip_end'] ?? '') < time()) {  // 判断会员功能、用户会员是否过期
                $this->vipExpired($res['type'], $res['user']['uid'], $res['user_id']); // 会员过期处理
                // 将VIP过期的任务用户id放入一个数组，用于后续判断
                $vip_expired_userIds[] = $res['user_id'];
                continue;
            }
            $urls[] = $this->taskApi($res, $res['account']);
            Jobs::updateJobInfo($res['do'], $res['user_id'], [ // 更新任务执行信息
                'lastExecute' => date("Y-m-d H:i:s"),
                'nextExecute' => isset($res['account']['timing']) ? strtotime($res['account']['timing'].'+1 day') : time() + $res['task']['execute_rate'],
            ]);
        }
        set_time_limit(0);
        ignore_user_abort(true);
        if (function_exists('fastcgi_finish_request')) {
            echo json_encode(['code' => 1001, 'message' => 'success in run' . date('Y-m-d H:i:s')]);
            fastcgi_finish_request();
            if ($urls) $this->curl_mulit($urls);
        }
        if ($urls) $this->curl_mulit($urls);
        return resultJson(1000, 'success in run ' . date('Y-m-d H:i:s'));
    }

    public function taskApi($job, $account)
    {
        $account_info = unserialize($account['data']);
        $account_data = '';
        if ($account_info) $account_data = http_build_query($account_info);
        try {
            $job_data = unserialize($job['data']);
        } catch (Exception $e) {
            TaskLogs::operateExecuteLog($type, $user_id, $do, '获取功能配置失败，请重新添加账号'); // 写入运行日志
            Jobs::where(['user_id' => $user_id, 'do' => $do])->update(['state' => 0]);
            return false;
        }
        $job_config = $job['data'] ? '&'.http_build_query($job_data) : '';
        switch ($job['type']) {
            case 'netease':
                $execute_url = get_Domain() . "cron/netease/{$job['do']}?user_id={$job['user_id']}&csrf={$account_info['csrf']}&musicu={$account_info['musicu']}" . $job_config . "&runkey=" . RUN_KEY;
                break;
            case 'bilibili':
                $execute_url = get_Domain() . "cron/bilibili/{$job['do']}?mid={$job['user_id']}&mid_md5={$account_info['mid_md5']}&token={$account_info['token']}&csrf={$account_info['csrf']}&access_key={$account_info['access_key']}" . $job_config . "&runkey=" . RUN_KEY;
                break;
            case 'sport':
                $execute_url = get_Domain() . "cron/sport/{$job['do']}?user_id={$job['user_id']}&login_token={$account_info['login_token']}&app_token={$account_info['app_token']}" . $job_config . "&" . $account_data . "&runkey=" . RUN_KEY;
                break;
            case 'tieba':
                $execute_url = get_Domain() . "cron/tieba/{$job['do']}?user_id={$job['user_id']}" . $job_config . "&" . $account_data . "&runkey=" . RUN_KEY;
                break;
            case 'mihoyo':
                $execute_url = get_Domain() . "cron/mihoyo/{$job['do']}?ltuid={$job['user_id']}&ltoken={$account_info['ltoken']}&cookie_token={$account_info['cookie_token']}&account_id={$account_info['account_id']}&login_uid={$account_info['login_uid']}&login_ticet={$account_info['login_ticket']}&stuid={$account_info['stuid']}&stoken={$account_info['stoken']}" . $job_config . "&runkey=" . RUN_KEY;
                break;
            case 'iqiyi':
                $execute_url = get_Domain() . "cron/iqiyi/{$job['do']}?user_id={$job['user_id']}&P00001={$account_info['P00001']}&P00003={$account_info['P00003']}" . $job_config . "&runkey=" . RUN_KEY;
                break;
            case 'qq':
                $execute_url = get_Domain() . "cron/qq/{$job['do']}?uin={$job['user_id']}" . $job_config . "&runkey=" . RUN_KEY;
                break;
            case 'heybox':
                $execute_url = get_Domain() . "cron/heybox/{$job['do']}?user_id={$job['user_id']}&pkey={$account_info['pkey']}" . "&runkey=" . RUN_KEY;
                break;
        }
        return $execute_url;
    }
}