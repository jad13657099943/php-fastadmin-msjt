<?php

namespace app\common\model;

use think\Model;

/**
 * 会员积分日志模型
 */
class ScoreLog Extends Model
{

    // 表名
    protected $name = 'user_score_log';
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = '';
    // 追加属性
    protected $append = [
    ];

    /*
     * 用户id取数据
     */
    public function getScoreId($uid, $field = '*', $order = 'add_time desc', $page = 0, $pagesize = 0)
    {
        return $this->where(['user_id' => $uid])->field($field)->order($order)->limit(($page - 1) * $pagesize, $pagesize)->select();
    }

    /**
     * 获取多条数据
     * @param $where
     * @param string $field
     * @param string $order
     * @return mixed
     */
    public function select_data($where, $field = '*', $order = 'id desc')
    {
        return $this->where($where)->field($field)->order($order)->select();
    }
}
