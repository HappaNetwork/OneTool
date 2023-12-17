<?php

namespace app\service;

use qq\Dg;
use think\exception\ValidateException;
use think\facade\Cache;
use app\exception\BusinessException;

class DgService
{
    private $cloud; //云端操作对象

    public function checkAfford($type, $month)
    {
        $obj = $this->getCloud();
        if (!in_array($type, array(0, 1, 2))) {
            throw new BusinessException("下单类型错误");
        }
        if (!in_array($month, array(1, 3, 6, 12, 999))) {
            throw new BusinessException("下单时长错误");
        }
        return $obj->checkAfford($type, $month);
    }

    public function getSelf($refresh = false)
    {
        if ($refresh) {
            Cache::delete('dg_selfs');
        }
        $obj = $this->getCloud();
        $d =  Cache::remember('dg_selfs', function () use (&$obj) {
            $data = $obj->getSelf();
            if (isset($data['code']) && $data['code'] != 200) {
                return false;
            } else {
                return $data['data'];
            }
        }, 600);
        if ($d) {
            return $d;
        } else {
            Cache::delete('dg_selfs');
            return array();
        }
    }

    public function getServers($refresh = false)
    {
        if ($refresh) {
            Cache::delete('dg_servers');
        }
        $obj = $this->getCloud();
        $d =  Cache::remember('dg_servers', function () use (&$obj) {
            $data = $obj->getServers();
            if (isset($data['code']) && $data['code'] != 200) {
                throw new BusinessException($data['msg']);
            } else {
                $temp = array();
                foreach ($data['data']['list'] as $key) {
                    $temp[$key['key']] = $key['name'];
                }
                return $temp;
            }
        }, 600);
        if ($d) {
            return $d;
        } else {
            Cache::delete('dg_servers');
            return array();
        }
    }

    public function getCountries($refresh = false)
    {
        if ($refresh) {
            Cache::delete('dg_phones');
        }
        $obj = $this->getCloud();
        $d =  Cache::remember('dg_phones', function () use (&$obj) {
            $data = $obj->getCountries();
            if (isset($data['code']) && $data['code'] != 200) {
                return false;
            } else {
                $temp = array();
                foreach ($data['data'] as $key) {
                    $temp[$key['code']] = $key;
                }
                return $temp;
            }
        }, 600);
        if ($d) {
            return $d;
        } else {
            Cache::delete('dg_phones');
            return array();
        }
    }

    public function getProjects($refresh = false)
    {
        if ($refresh) {
            Cache::delete('dg_projects');
        }
        $obj = $this->getCloud();
        $d =  Cache::remember('dg_projects', function () use (&$obj) {
            $data = $obj->getProject();
            if (isset($data['code']) && $data['code'] != 200) {
                return false;
            } else {
                $temp = array();
                foreach ($data['data'] as $key => $value) {
                    foreach ($value as $key1 => $value1) {
                        $temp[$key1] = $value1;
                    }
                }
                return self::arraySort($temp, 'sort', 'asc');
            }
        }, 600);
        if ($d) {
            return $d;
        } else {
            Cache::delete('dg_projects');
            return array();
        }
    }

    public function getActivity($refresh = false)
    {
        if ($refresh) {
            Cache::delete('dg_activities');
        }
        $obj = $this->getCloud();
        $d =  Cache::remember('dg_activities', function () use (&$obj) {
            $data = $obj->getActivity();
            if (isset($data['code']) && $data['code'] != 200) {
                return false;
            } else {
                return $data['data'];
            }
        }, 600);
        if ($d) {
            return $d;
        } else {
            Cache::delete('dg_activities');
            return array();
        }
    }

    public function changeSetting($uin, $type, $status = 0)
    {
        //$status = $status == 0 ? 0 : 1;
        $project = $this->getProjects();
        if (!$project || !isset($project[$type])) {
            throw new BusinessException("该项目不存在");
        }
//        $uin_info = $this->find(array('uin' => $uin));
//        if (!$uin_info) {
//            throw new BusinessException("未查询到账号信息");
//        }
        $obj = $this->getCloud();
        if ($obj->changeSetting($uin, $type, $status)) {
            $this->getUinInfo($uin, true);
            return array();
        }
        throw new BusinessException("更改项目状态失败");
    }

    public function changeStatus($uin, $status = 0)
    {
//        $uin_info = $this->find(array('uin' => $uin));
//        if (!$uin_info) {
//            return $this->error("未查询到账号信息");
//        }
        $obj = $this->getCloud();
        if ($obj->changeStatus($uin, $status)) {
            $this->getUinInfo($uin, true);
            return array();
        }
        throw new BusinessException("更改挂机状态失败");
    }

    public function run($uin)
    {
//        $uin_info = $this->find(array('uin' => $uin));
//        if (!$uin_info) {
//            return $this->error("未查询到账号信息");
//        }
        $obj = $this->getCloud();
        $resp = $obj->run($uin);
        if ($resp['code'] == 200) {
            $this->getUinInfo($uin, true);
            return array();
        }
        throw new BusinessException($resp['message']);
    }

    public function getUinInfo($uin = '', $refresh = false)
    {
        $key = 'dg_infos_' . $uin;
        if ($refresh) {
            Cache::delete($key);
        }
        $obj = $this->getCloud();
        $d =  Cache::remember($key, function () use (&$obj, $uin) {
            $data = $obj->getUinInfo($uin);
            if (isset($data['code']) && $data['code'] != 200) {
                return false;
            } else {
                unset($data['data']['uin']['app_id']);
                return $data['data'];
            }
        }, 600);
        if ($d) {
            return $d;
        } else {
            Cache::delete($key);
            return array();
        }
    }

    public function buy($uin, $pwd, $server, $type, $month)
    {
        if (!in_array($type, array(0, 1, 2))) {
            throw new BusinessException("下单类型错误");
        }
        if (!in_array($month, array(1, 3, 6, 12, 999))) {
            throw new BusinessException("下单时长错误");
        }
        if (strlen($pwd) != 32) {
            $pwd = md5($pwd);
        }
        $obj = $this->getCloud();
        if ($obj->buy($uin, $type, $month, $pwd, $server)) {
            $this->getUinInfo($uin, true);
            return true;
        }
        throw new BusinessException("下单失败，未知错误");
    }

    public function login($uin, $login_type = 'pwd', $step = 'normal', $ticket = '', $rand = '', $code = '')
    {
//        $uin_info = $this->find(array('uin' => $uin));
//        if (!$uin_info) {
//            return $this->error("未查询到账号信息");
//        }
        $obj = $this->getCloud();
        $data = $obj->login($uin, $login_type, $step, $ticket, $rand, $code);
        if (isset($data['code']) && $data['code'] == 200) {
            $this->getUinInfo($uin, true);
        }
        return $data;
    }

    public function sms($uin, $step = '1', $ticket = '', $rand = '', $phone = '')
    {
        $obj = $this->getCloud();
        return $obj->sms($uin, $step, $ticket, $rand, $phone);
    }

    public function changePhone($uin, $phone, $phone_area = '86')
    {
        $countries = $this->getCountries();
        if (!$countries || !isset($countries[$phone_area])) {
            throw new BusinessException("手机区号不存在");
        }
//        $uin_info = $this->find(array('uin' => $uin));
//        if (!$uin_info) {
//            return $this->error("未查询到账号信息");
//        }
        $obj = $this->getCloud();
        if ($obj->changePhone($uin, $phone, $phone_area)) {
            return array();
        }
        throw new BusinessException("更改挂机手机失败");
    }

    public function changePassword($uin, $password)
    {
        if (strlen($password) != 32) {
            $password = md5($password);
        }
//        $uin_info = $this->find(array('uin' => $uin));
//        if (!$uin_info) {
//            return $this->error("未查询到账号信息");
//        }
        $obj = $this->getCloud();
        if ($obj->changePassword($uin, $password)) {
            return array();
        }
        throw new BusinessException("更改挂机密码失败");
    }

    public function changeServer($uin, $server)
    {
        $servers = $this->getServers();
        if (!$servers || !isset($servers[$server])) {
            throw new BusinessException("该地区不存在");
        }
//        $uin_info = $this->find(array('uin' => $uin));
//        if (!$uin_info) {
//            return $this->error("未查询到账号信息");
//        }
        $obj = $this->getCloud();
        if ($obj->changeServer($uin, $server)) {
            $this->getUinInfo($uin, true);
            return array();
        }
        throw new BusinessException("更改挂机地区失败");
    }

    public function changeTiming($uin, $timing)
    {
        $timing = intval($timing);
        if ($timing < 0 || $timing >= 24 * 60) {
            throw new BusinessException("挂机时间错误");
        }
//        $uin_info = $this->find(array('uin' => $uin));
//        if (!$uin_info) {
//            return $this->error("未查询到账号信息");
//        }
        $obj = $this->getCloud();
        if ($obj->changeTiming($uin, $timing)) {
            $this->getUinInfo($uin, true);
            return array();
        }
        throw new BusinessException("更改挂机时间失败");
    }

    public static function arraySort($arr, $keys, $type = 'asc')
    {
        $keysvalue = array();
        $new_array = array();
        foreach ($arr as $k => $v) {
            $keysvalue[$k] = $v[$keys];
        }
        if ($type == 'asc') {
            asort($keysvalue);
        } else {
            arsort($keysvalue);
        }
        reset($keysvalue);
        foreach ($keysvalue as $k => $v) {
            $new_array[$k] = $arr[$k];
        }
        return $new_array;
    }

    private function getCloud()
    {
        if (config('sys.is_qqdg') != 1) {
            exit('功能未开启');
        }
        if (!$this->cloud) {
            $this->cloud = Dg::getInstance()->setAuthKey(config('sys.login_system_key'));
        }
        return $this->cloud;
    }
}