<?php

namespace app\common\model;

use think\Cache;
use think\Model;

/**
 * 限时折扣模型
 */
class Useragentapply extends Model
{
    protected $name = 'user_agent_apply';
    protected $createTime = 'create_time';
    protected $updateTime = false;
    /*
     * 统计数量
     */
    public function count_data($where)
    {
        return $this->where($where)->count();
    }

    //获取一维数组
    public function find_data($where = [], $field = '*', $order = "id desc")
    {
        return $this->where($where)->field($field)->order($order)->find();
    }

    //更新/添加数据
    public function save_data($where,$field)
    {
        return $this->save($field,$where);
    }

    public function select_data($where,$field ,$order = 'id desc', $page = 0, $pagesize = 10){
        return $this->where($where)->field($field)->order($order)->limit(($page - 1) * $pagesize, $pagesize)->select();
    }

    /**
     * 获取单列单个字段
     * @param array $where
     * @param $field
     * @return mixed
     */
    public function find_one_data($where = [],$field)
    {
        return $this->where($where)->value($field);
    }
}