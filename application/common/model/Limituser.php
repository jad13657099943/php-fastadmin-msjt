<?php

namespace app\common\model;

use think\Model;

class Limituser extends Model
{
    // 表名
    protected $name = 'limituser';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;

    // 追加属性
    protected $append = [
        'create_time_text'
    ];

    /*
   * 获取单条数据
   * @param  $where 查询条件
   * @param  $field 需要查询字段
   * @param  $order 排序字段
   */
    public function find_data($where = [], $field = '*')
    {
        return $this->where($where)->field($field)->find();
    }


    /**
     * 验证是否存在
     * */
    public function check_count($where){
        return $this->where($where)->count();
    }
}