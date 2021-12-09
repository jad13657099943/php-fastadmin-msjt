<?php

namespace app\common\model;

use think\Model;

/**
 * 佣金模型
 */
class Commission Extends Model
{
//    protected $autoWriteTimestamp = false;

    protected $createTime = 'add_time';
    protected $updateTime = false;
    // 表名
    protected $name = 'Commission';

    //获取佣金明细列表
    public function select_page($where, $field, $order = 'add_time desc', $page = 1, $pagesize = 10)
    {
        return $this->where($where)->field($field)->order($order)->limit(($page - 1) * $pagesize)->select();

    }

    /**
     * @param $uid
     * @param $balance  变更的金额
     * @param int $type
     * @throws \think\exception\DbException
     */
    public static function balance($uid, $balance, $type = 0)
    {
        $user = User::get($uid);
        if ($user) {
            $user->balance += $balance;
            $type == 1 && $user->total_balance += $balance;
            $user->save();
            self::insert([
                'price' => $balance,
                'uid' => $uid,
                'type' => $type,
                'add_time' => time(),
            ]);
        }
    }
}