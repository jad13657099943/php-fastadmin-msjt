<?php

namespace app\common\model;

use think\Cache;
use think\Model;

/**
 * 收藏模型
 */
class Collect extends Model
{
    // 表名
    protected $name = 'Collect';

    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'add_time';
    protected $updateTime = false;


    /**
     * 添加数据
     * @param $data
     * @return mixed
     */
    public function add_data($data)
    {
        return $this->create($data);
    }

    /**
     * 插入|更新
     * @param $where
     * @param $data
     * @return false|int
     */
    public function save_data($where, $data)
    {
        return $this->allowField(true)->save($data, $where);
    }

    /*
     * 获取多条数据
     * @param $where 条件
     * @param $field 查找字段
     */
    public function select_data($where, $field = "*", $order = "id desc", $with)
    {
        $list =  collection($this->where($where)->with($with)->field($field)->order($order)->select())->toArray();
        if ($list) {
            foreach ($list as $k => $v) {
                $list[$k]['goods']['image'] = $v['goods']['image'] ? config('item_url') . $v['goods']['image'] : '';
            }
        }
        return $list;
    }


    /*
     * 统计数量
     */
    public function getCount($where)
    {
        return $this->where($where)->count();
    }


    /*
     * 获取分页数据
     *
     */
    public function get_page_data($where = [], $field = "*", $page = 1, $pagesize = 10, $order = "add_time desc")
    {
        return $this->where($where)->field($field)->order($order)->limit(($page - 1) * $pagesize, $pagesize)->select();

    }


    /*
     * 查找单条数据
     * @param $where
     * @param string $filed
     * @return mixed
     */
    public function find_data($where, $filed = '*')
    {
        return $this->where($where)->field($filed)->find();
    }

    /*
     * 修改信息
     * @param $where
     * @param $data
     */
    public function update_data($where, $data)
    {
        return $this->where($where)->save($data);
    }

    /**
     * 关联商品表
     */
    public function goods()
    {
        return $this->belongsTo('Litestoregoods', 'goods_id', 'goods_id', null, 'left');
    }
}