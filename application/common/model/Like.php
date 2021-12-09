<?php

namespace app\common\model;

use think\Cache;
use think\Model;

/**
 * 广告管理模型
 */
class Like extends Model
{
    protected $name = 'Like';


    /**
     * 获取多条数据
     */
    public function select_data($where =[],$field='*'){
        return $this->field($field)->where($where)->select();
    }


    /**
     * 单条数据
     */
    public function find_data($where =[],$field='*'){
        return $this->field($field)->where($where)->find();
    }

    /**
     * 分页数据
     */
    public function get_page_data($condtion,$field,$page=0,$pagesize=0,$order='id desc'){
        return $this->field($field)->where($condtion)->order($order)->limit(($page-1)*$pagesize, $pagesize)->select();
    }

    /*
     * 增加数据
     */
    public function add_data($data)
    {
        return $this->insert($data);
    }

}
