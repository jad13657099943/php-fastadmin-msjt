<?php

namespace app\common\model;

use think\Model;
/**
 * 评论模型模型
 */
class Cmscomment Extends Model
{
    // 表名
    protected $name = 'Cms_comment';

    //获取评论列表
    public function select_page($where = [],$field = '*',$order='createtime desc',$page=1,$pagesize=10){
        $list = $this->where($where)->field($field)->order($order)->limit(($page -1)*$pagesize)->select();
        return $list;

    }


    /*
     * 获取单条数据
     *
     * */
    public function find_data($where = [] , $field='*',$order='id desc'){
        $where['status'] = 'normal';
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

    /*
     * 新增数据
     */
    public function add_data($data)
    {
        return $this->insert($data);
    }

    /*
     * 增加广告浏览量/评论数量/点赞数
     */
    public function setInc_data($where = [],$data)
    {
        $this->where($where)->setInc($data);
    }

    /*
     * 减少广告浏览量/评论数量/点赞数
     */
    public function setDec_data($where = [],$data)
    {
        $this->where($where)->setDec($data);
    }



}