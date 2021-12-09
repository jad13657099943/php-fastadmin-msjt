<?php

namespace app\common\model;

use think\Model;
use think\Db;

/**
 * 实名认证/商家入驻
 */
class Store Extends Model
{

    // 表名
    protected $name = 'user_agent_apply';
    protected $field = true;
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = '';
    // 追加属性
    protected $append = [
    ];

    /**
     * 获取多条数据
     * @param $where
     * @param string $field
     * @param string $order
     * @param int $page
     * @param int $pagesize
     * @return mixed
     */
    public function select_page($where,$field='*',$order='add_time desc',$page=0,$pagesize=0)
    {
        return $this->where($where)->field($field)->order($order)->limit(($page-1)*$pagesize, $pagesize)->select();
    }

    /**
     * 获取单条数据
     * @param $where
     * @param string $field
     * @param string $order
     * @return mixed
     */
    public function find_data($where,$field='*',$order='add_time desc')
    {
        return $this->where($where)->field($field)->order($order)->find();
    }

    /**
     * 添加数据
     * @param $data
     * @return mixed
     */
    public function add_data($data)
    {
        return $this->allowField(true)->insert($data);
    }

    public function update_data($where,$data)
    {
        return $this->where($where)->update($data);
    }
}
