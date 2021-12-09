<?php

namespace app\common\model;

use think\Model;
/**
 * 评论模型模型
 */
class Comment Extends Model
{
    // 表名
    protected $name = 'Comment';

    //获取评论列表
    //获取商品列表
    public function select_page($condtion,$field,$order='add_time desc',$page=1,$pagesize=10){

        return $this->field($field)->where($condtion)->order($order)->limit(($page-1)*$pagesize, $pagesize)->select();
    }


    /*
     * 获取单条数据
     *
     * */
    public function find_data($where = [] , $field='*',$order='id desc'){
        $where['status'] = 0;
        return $this->where($where)->field($field)->order($order)->find();
    }


    /*
     * 获取评论数量
     * @param $where 条件
     * */
    public function count_comment_number($where = []){
        $where['status'] = 0;
        return $this->where($where)->count();
    }

    /*
     * 获取多条评论
     */
    public function select_limit_data($where = [],$field = '*',$order = 'add_time desc',$data)
    {
        return $this->where($where)->field($field)->order($order)->limit($data)->select();
    }
}