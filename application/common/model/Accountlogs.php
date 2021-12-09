<?php

namespace app\common\model;

use think\Cache;
use think\Model;

/**
 * 地区数据模型
 */
class Accountlogs extends Model
{
    // 表名
    protected $name = 'Account_logs';

    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    // 定义字段类型
    protected $type = [
    ];

    //获取单条数据
    public function find_data($where = [],$field = '*')
    {
        return $this->where($where)->field($field)->find();
    }

    //获取多条数据
    public function select_data($where = [],$field = '*')
    {
        return $this->where($where)->field($field)->select();
    }

    //获取多条分页数据
    public function get_page_data($where = [],$field = '*',$order ='id desc',$page=0,$pagesize=10)
    {
        return $this->field($field)->where($where)->order($order)->limit(($page-1)*$pagesize, $pagesize)->select();
    }

    //新增数据
    public function add_data($data)
    {
        return $this->insert($data);
    }

    //修改数据

    public function update_data($where,$data)
    {
        return $this->where($where)->update($data);
    }

    //删除数据
    public function delete_data($where)
    {
        return $this->where($where)->delete();
    }
}