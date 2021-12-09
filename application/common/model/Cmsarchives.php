<?php

namespace app\common\model;

use think\Cache;
use think\Model;
use traits\model\SoftDelete;

/**
 * 广告管理模型
 */
class Cmsarchives extends Model
{
    protected $name = 'Cms_archives';
    use SoftDelete;
    protected $deleteTime = 'deletetime';



    /**
     * 读取广告模型列表
     */
    public function select_data($where =[],$field='*'){

        return $this->field($field)->where($where)->select();

    }


    /**
     * 读取单条广告模型
     */
    public function find_data($where =[],$field='*',$order='id desc'){
        return $this->field($field)->where($where)->order($order)->find();
    }

    /**
     * 读取广告模型列表
     */
    public function get_page_data($condtion,$field,$page=0,$pagesize=0,$order='id desc'){
        return $this->field($field)->where($condtion)->order($order)->limit(($page-1)*$pagesize, $pagesize)->select();
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
    //拼接二维数组的图片
    public function set_url_img($list)
    {
        if ($list) {
            foreach ($list as $k => $v) {
                $list[$k]['image'] = config('items_url') . $v['image'];
            }
        }
        return $list;
    }

    //修改信息
    public function edit_data($where,$data)
    {
        return $this->where($where)->update($data);
    }

    /**
     * 查询单列单个字段
     * @param array $where
     * @param $field
     * @return mixed
     */
    public function find_one_data($where = [],$field)
    {
        return $this->where($where)->value($field);
    }

}
