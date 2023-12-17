<?php

namespace app\cron\controller;

use app\index\model\TaskLogs;
use app\index\model\Info;
use app\index\model\Accounts;
use app\index\model\Jobs;
use app\index\model\Tasks;
use app\index\model\Users;
use qq\QHelper as qqAPI;
use Exception;
use think\facade\Request;

class Qq extends Common
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
        $jobs = Jobs::getUnexecutedList('qq'); // 获取未执行任务列表
        if (count($jobs) == 0) {
            return resultJson(-1002, '没有要执行的任务');
        }
        foreach ($jobs as $job) {
            if (in_array($job['user_id'], $vip_expired_userIds)) continue;
            $user = Users::where('uid' , '=' , $job['uid'])->find();
            if($user == null){
                Jobs::delJob('qq',$job['user_id']);
                continue;
            }
            $task = Tasks::where('type', '=', 'qq')->where('execute_name', '=', $job['do'])->find();
            $account = Accounts::where('type', '=', 'qq')->where('user_id', '=', $job['user_id'])->find();
            if ($account == null) {
                Accounts::delById($job['user_id']);
                Jobs::delJob('qq',$job['user_id']);
                continue;
            }
            if ($task['vip'] == 1 && strtotime($user['vip_end'] ?? '') < time()) {  // 判断会员功能、用户会员是否过期
                $this->vipExpired('qq', $user['uid'], $job['user_id']); // 会员过期处理
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
                'nextExecute' =>  time() + $task['execute_rate'],
            ]);
        }
        if ($urls) $this->curl_mulit($urls);
        return resultJson(1000, '执行任务成功');
    }

    public function execute($do)
    {
        $data = Request::get();
        if ($data['runkey'] == RUN_KEY) {
            $qq = new qqAPI($data, $data);
            $execute = $qq->{$do}();
            if ($qq->cookiezt) {
                $account = Accounts::where('type', '=', 'qq')->where('user_id', '=', $data['uin'])->find();
                $user = Users::where('uid', '=', $account['uid'])->find();
                $update = $this->tryUpdateQQ(unserialize($account['data']));
                if ($update == false) {
                    $this->accountInvalid('qq', $user, $data['uin']); // 账号失效处理
                }
            } else {
                foreach ($qq->msg as $message) {
                    TaskLogs::operateExecuteLog('qq', $data['uin'], $do, $message); // 写入运行日志
                }
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
            TaskLogs::operateExecuteLog('netease', $user_id, $do, '获取功能配置失败，请重新添加账号'); // 写入运行日志
            Jobs::where(['user_id' => $user_id, 'do' => $do])-> update(['state' => 0]);
            return false;
        }
        $job_config = $job['data'] ? '&'.http_build_query($job_data) : '';
        $account = Accounts::where('type', '=', 'qq')->where('user_id', '=', $user_id)->find();
        $account_info = unserialize($account['data']);

        $account_data = http_build_query($account_info);

        $url = get_Domain() . "cron/qq/{$do}?uin={$account_info['uin']}" . $job_config . "&" . $account_data . "&runkey=" . RUN_KEY;
        return $url;
    }

    private function tryUpdateQQ($account_info)
    {
        $url = 'https://api.qqshabi.cn/request/qqlogin';
        $data = [
            'url' => $_SERVER['HTTP_HOST'],
            'key' => config('sys.login_system_key'),
            'do' => 'update',
            'uin' => $account_info['uin'],
            'pwd' => $account_info['password'],
            'area' => $account_info['area'],
        ];
        $ret = get_curl($url, $data);
        $arr = json_decode($ret, true);
        if (isset($arr) && $arr['saveOK'] == 0) {
            $data = [
                'uin' => $account_info['uin'],
                'skey' => $arr['cookies']['skey'],
                'qzone_pskey' => $arr['cookies']['qzone_pskey'],
                'kg_pskey' => $arr['cookies']['kg_pskey'] ,
                'v_pskey' => $arr['cookies']['v_pskey'],
                'ti_pskey' => $arr['cookies']['ti_pskey'],
                'nickname' => get_qqname($account_info['uin']),
                'password' => $account_info['password'],
                'area' => $account_info['area'],
            ];
            Accounts::where('type', '=', 'qq')->where('user_id', '=', $account_info['uin'])->update([
                'data' => serialize($data),
                'state' => 1
            ]);
            $user = Accounts::where('type', '=', 'qq')->where('user_id', '=', $account_info['uin'])->find();
            $data = [
                'type' => 'qq',
                'user_id' => $user['user_id'],
                'do' => 'QQ自动更新',
                'response' => '自动更新QQ成功',
            ];
            TaskLogs::operateLog($data);
            return true;
        }
        return false;
    }

}