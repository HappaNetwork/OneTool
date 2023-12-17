<?php
declare (strict_types=1);

namespace app\index\model;

use think\Collection;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\facade\Session;
use think\Model;

class Jobs extends Model
{
    protected $pk = 'id';

    public static function add($type, $user_id)
    {
        $self = new static();
        $tasks = Tasks::getTaskList($type);
        foreach ($tasks as $key => $value) {
            if ($value['vip'] == 1 && empty(Session::get('user.vip_start'))) {
                $self->insert([
                    'uid' => Session::get('user.uid'),
                    'type' => $type,
                    'user_id' => $user_id,
                    'do' => $value['execute_name'],
                    'state' => 0,
                    'nextExecute' => time(),
                ]);
            } else {
                $self->insert([
                    'uid' => Session::get('user.uid'),
                    'type' => $type,
                    'user_id' => $user_id,
                    'do' => $value['execute_name'],
                    'state' => 1,
                    'nextExecute' => time(),
                ]);
            }
        }
    }

    /**
     * refreshJob 管理员后台编辑任务后更新Jobs任务信息
     * @param $type
     * @param $user_id
     * @return void
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public static function refreshJob($type, $user_id)
    {
        $self = new static();
        $tasks = Tasks::getTaskList($type);
        foreach ($tasks as $key => $value) {
            if (!$self::getJobInfo($type, $user_id, $value['execute_name'])) {
                $self->create([
                    'uid' => Session::get('user.uid'),
                    'type' => $type,
                    'user_id' => $user_id,
                    'do' => $value['execute_name'],
                    'nextExecute' => time(),
                ]);
            }
        }
    }

    /**
     * getJobInfo
     * @param $type
     * @param $user_id
     * @param $do
     * @return Jobs|array|false|mixed|Model
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public static function getJobInfo($type = null, $user_id = null, $do = null)
    {
        $self = new static();
        if ($result = $self->where('type', $type)->where('user_id', $user_id)->where('do', $do)->find()) {
            return $result;
        }
        return false;
    }

    /**
     * addNeteaseJob
     * @param $data
     * @return false|int|string
     * @author BadCen
     */
    public static function addNeteaseJob($data)
    {
        $self = new static();
        $tasks = Tasks::getTaskList('netease');
        foreach ($tasks as $key => $value) {
            $result = $self->insert([
                'uid' => Session::get('user.uid'),
                'type' => 'netease',
                'user_id' => $data['user_id'],
                'do' => $value['execute_name'],
                'nextExecute' => time(),
            ]);
        }
        if ($result) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * addBilibiliJob
     * @param $data
     * @return bool
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     * @author BadCen
     */
    public static function addBilibiliJob($data)
    {
        $self = new static();
        $tasks = Tasks::getTaskList('bilibili');
        foreach ($tasks as $key => $value) {
            $result = $self->insert([
                'uid' => Session::get('user.uid'),
                'type' => 'bilibili',
                'user_id' => $data['mid'],
                'do' => $value['execute_name'],
                'nextExecute' => time(),
            ]);
        }
        if ($result) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * addSportJob
     * @param $data
     * @return bool
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     * @author BadCen
     */
    public static function addSportJob($data)
    {
        $self = new static();
        $tasks = Tasks::getTaskList('sport');
        foreach ($tasks as $key => $value) {
            $result = $self->insert([
                'uid' => Session::get('user.uid'),
                'type' => 'sport',
                'user_id' => $data['user_id'],
                'do' => $value['execute_name'],
                'nextExecute' => time(),
            ]);
        }
        if ($result) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * updateJob 更新账号后更新任务信息
     * @param $type
     * @return bool
     * @author BadCen
     */
    public static function updateJob($type = null, $user_id = null)
    {
        $self = new static();
        $tasks = Tasks::getTaskList($type);
        foreach ($tasks as $key => $value) {
            if ($value['vip'] == 1 && Session::get('user.vip_start')) {
                $self->where('type', '=', $type)
                    ->where('user_id', '=', $user_id)
                    ->where('state', '=', -1)
                    ->where('do', '=', $value['execute_name'])
                    ->update(['state' => 1, 'nextExecute' => time()]);
            } else {
                $self->where('type', '=', $type)
                    ->where('user_id', '=', $user_id)
                    ->where('state', '=', -1)
                    ->where('do', '=', $value['execute_name'])
                    ->update(['state' => 1, 'nextExecute' => time()]);
            }
        }
        return false;
    }

    /**
     * findByUserId
     * @param $user_id
     * @return Jobs|array|false|Model|null
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     * @author BadCen
     */
    public static function findByUserId($type, $user_id)
    {
        $self = new static();
        if ($result = $self->where('user_id', $user_id)->where('type', $type)->where('uid', Session::get('user.uid'))->select()) {
            return $result;
        }
        return false;
    }

    /**
     * switchState
     * @param $user_id
     * @param $do
     * @return Jobs|false
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     * @author BadCen
     */
    public static function switchState($user_id, $do)
    {
        $self = new static();
        $sql = $self->where('user_id', $user_id)->where('do', $do);
        if ($ret = $sql->find()) {
            if ($ret->state == -1) {
                $result = $sql->update(['state' => 1]);
            } else {
                $result = $sql->update([
                    'state' => $ret['state'] ^ 1
                ]);
            }
            return $result;
        }
        return false;
    }

    /**
     * getUnexecutedList
     * @param null $type
     * @return Jobs[]|array|false|Collection
     * @throws DataNotFoundException&
     * @throws DbException
     * @throws ModelNotFoundException
     * @author BadCen
     */
    public static function getUnexecutedList($type = null, $filter = [])
    {
        $self = new static();
        $result = $self->where($filter)->where([['type', '=', $type], ['state', '=', 1], ['nextExecute', '<=', time()]])
            ->limit((int)config('sys.interval') ?? 0)
            ->select();
        if ($result) {
            return $result;
        }
        return false;
    }

    /**
     * delJob
     * @param $type
     * @param $data
     * @return bool
     * @author BadCen
     */
    public static function delJob($type, $data)
    {
        $self = new static();
        if ($result = $self->where('type', $type)->where('user_id', $data)->delete()) {
            return $result;
        }
        return false;
    }

    /**
     * jobCount 任务数量
     * @return false|int
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     * @author BadCen
     */
    public static function jobCount()
    {
        $self = new static();
        return $self->count('id');
    }

    /**
     * updateJobInfo 更新任务运行信息
     * @param $user_id
     * @param $type
     * @param array $data
     * @return Jobs|false
     * @author BadCen
     */
    public static function updateJobInfo($do, $user_id, $data = [])
    {
        $self = new static();
        if ($result = $self->where(['do' => $do, 'user_id' => $user_id])->update($data)) {
            return $result;
        }
        return false;
    }

}