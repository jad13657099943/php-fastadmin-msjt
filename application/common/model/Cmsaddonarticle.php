<?php

namespace app\common\model;

use think\Cache;
use think\Model;

/**
 * 广告管理模型
 */
class Cmsaddonarticle extends Model
{
    protected $name = 'Cms_addonarticle';


    /**
     * 读取文章列表
     */
    public function select_data($where =[],$field='*'){
        return $this->field($field)->where($where)->select();
    }


    /**
     * 读取单条文章
     */
    public function find_data($where =[],$field='*'){
        return $this->field($field)->where($where)->find();
    }

    /**
     * 读取文章列表
     */
    public function get_page_data($condtion,$field,$page=0,$pagesize=0,$order='id desc'){
        return $this->field($field)->where($condtion)->order($order)->limit(($page-1)*$pagesize, $pagesize)->select();
    }

    /*
     * 增加广告浏览量/评论数量/点赞数
     */
    public function setInc_data($where = [],$data)
    {
        dump($where);dump($data);
        $this->where($where)->setInc($data);
    }

    /*
     * 减少广告浏览量/评论数量/点赞数
     */
    public function setDec_data($where = [],$data)
    {
        dump($where);dump($data);
        $this->where($where)->setDec($data);
    }

}
