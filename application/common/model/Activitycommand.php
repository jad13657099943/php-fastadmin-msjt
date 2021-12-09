<?php

namespace app\common\model;

use think\Model;

/**
 * 购物车模型
 */
class Activitycommand Extends Model
{

    // 表名
    protected $name = 'activity_command';



    /*
     * 获取单条数据
     * @param  $where 查询条件
     * @param  $field 需要查询字段
     * @param  $order 排序字段
     */
    public function find_data($where = [], $field = '*', $order = 'add_time desc')
    {
        return $this->where($where)->field($field)->order($order)->find();
    }



    /*
     * 添加数据
     * @param  $param 查询条件
     */
    public function add_data($param)
    {
        return $this->allowField(true)->save($param);
    }

    /**
     * 修改信息
     * @param array $where
     * @param array $data
     * @return bool
     */
    public function update_data($where = [],$data = [])
    {
        if (empty($where)) {
            return false;
        }
        return $this->where($where)->update($data);
    }


    /**
     * 删除
     * @param $where
     * @return bool|mixed
     */
    public function delete_data($where)
    {
        if (empty($where)) {

            return false;
        }
        return $this->where($where)->delete();
    }

}