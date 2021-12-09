<?php

namespace app\common\model;

use think\Cache;
use think\Model;

/**
 * 栏目分类管理模型
 */
class Cmschannel extends Model
{
    protected $name = 'cms_channel';


    /**
     * 读取栏目分类列表
     */
    public function getCmschannelList($where,$field='*'){
        $list = $this->field($field)->where($where)->select();
        //图片封装

        return $list;
    }










}
