<?php

namespace app\common\model;

use think\Cache;
use think\Model;
use app\common\model\User;

/**
 * 限时折扣模型
 */
class UserRebate extends Model
{
    protected $name = 'user_rebate';
    protected $updateTime = false;

    /*
     * 统计数量
     */
    public function count_data($where)
    {
        return $this->where($where)->count();
    }

    /*
     * 增加下级信息（判断该用户是否有上级）
     *
     */
    public function add_rebate_data($uid, $invite_id)
    {
        if ($uid && $invite_id) {
            $add_r = [
                'uid' => $uid,
                'first_id' => $invite_id,
                'add_time' => time(),
            ];
            //判断该用户是否已经被邀请
            if ($this->where(['uid' => $uid])->count() == 0) {
                //给上级邀请人加1
                model('User')->where(['id' => $invite_id])->setInc('invite_num');
                model('User')->edit_data(['id' => $uid], ['pid' => $invite_id]);
                $add = $this->where(['uid' => $uid])->insert($add_r);
            }

        }
        return $add;

    }

    //获取一维数组
    public function find_data($where = [], $field = '*')
    {
        return $this->where($where)->field($field)->find();
    }


    public function user()
    {
        return $this->hasOne('User', 'id', 'uid')->where(['vip_type' => ['neq', 1]])->setEagerlyType(0);
    }

    public function info()
    {
        return $this->hasOne('User', 'id', 'uid')->bind('nickname,mobile,vip_type,distributor,avatar,distributor_id,invite_num');
    }

    //获取一维数组
    public function select_data($where = [], $field = '*')
    {
        return $this->where($where)->field($field)->select();
    }


}