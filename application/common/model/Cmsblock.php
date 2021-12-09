<?php

namespace app\common\model;

use Psr\Log\Test\DummyTest;
use think\Cache;
use think\Model;

/**
 * 广告管理模型
 */
class Cmsblock extends Model
{
    protected $name = 'cms_block';


    /**
     * 读取广告模型列表
     */
    public function select_data($where = [], $field = '*' ,$order = 'weigh asc')
    {
        return $this->field($field)->where($where)->order($order)->select();
    }


    /**
     * 读取单条广告模型
     */
    public function find_data($where = [], $field = '*')
    {
        return $this->field($field)->where($where)->find();
    }

    //拼接二维数组的图片
    public function set_url_img($list)
    {
        if ($list) {
            foreach ($list as $k => $v) {
                $list[$k]['image'] = config('item_url') . $v['image'];
            }
        }
        return $list;
    }

    /**
     * 读取单条广告模型
     */
    public function find_field_data($where = [], $field = '*')
    {
        $info =  $this->field($field)->where($where)->find();
        return $this->setPlitJointImages($info , $info['id']);
    }

    //拼接二维数组的图片
    /**
     *
    */
    public function setPlitJointImages($info, $cate_id)
    {
        $imgs = explode(',', $info['images']);
        $activity_ids = explode(',', $info['url']);

        if ($imgs) {
            foreach ($imgs as $k => $v) { //74 76 77
                $img[$k]['image'] = $v ? config('item_url') . $v : "";
                $result = $this->check_activityid($activity_ids[$k], $cate_id);
                $img[$k]['activity_id'] = $result['activity_id'];
                $img[$k]['goods_id'] = $result['goods_id'];
            }
        } else
            return [];
        return $img;
    }

    public function checkAdListJump($activity_id,$jump_status)
    {
//        dump($activity_id);dump($jump_status);die;
        $where['id'] = $activity_id;
        switch ($jump_status){
            case 1://限时抢购
                $model = model('Limitdiscountgoods');
                $field = 'limit_discount_id activity_id,goods_id';
                break;

            case 2: //团购商品
                $model = model('Groupbuygoods');
                $field = 'id activity_id,goods_id';
                break;

            case 3://vip特区
                break;

            default: //普通商品
                $model = model('Litestoregoods');
                unset($where);
                $where['goods_id'] = $activity_id;
                $field = 'goods_id activity_id ,goods_id';
                break;
        }
        if ($jump_status == 3 || $jump_status == 4 || $jump_status == 5 || $jump_status == 6 || $jump_status == 7 || $jump_status == 8 ||$jump_status == 9) {
            return ['activity_id' => 0, 'goods_id' => 0];
        }
        $info =  $model->find_data($where,$field);
        if(!$info){
            return ['activity_id' => '' , 'goods_id' => ''];
        }

        return ['activity_id' => $info['activity_id'] , 'goods_id' => $info['goods_id']];
    }


    /**
     * 判断跳转id
     * @param $activity_id 活动id
     * @param $cate_id 分类id
     * */
    public function check_activityid($activity_id ,$cate_id){
        $where['id'] = $activity_id;
        switch ($cate_id){
            case 74: //限时抢购
                $field = 'limit_discount_id activity_id,goods_id';
                $model = model('Limitdiscountgoods');
                break;

            case 76: //团购商品
                $model = model('Groupbuygoods');
                $field = 'id activity_id,goods_id';
                break;

            case 77: //我要砍价
                $model = model('Cutdowngoods');
                unset($where);
                $where['goods_id'] = $activity_id;
                $field = 'id activity_id,goods_id';
                break;

            default: //普通商品
                $model = model('Litestoregoods');
                unset($where);
                $where['goods_id'] = $activity_id;
                $field = 'goods_id activity_id ,goods_id';
                break;
        }

        $info = $model->find_data($where,$field);
        if(!$info){
            return ['activity_id' => '' , 'goods_id' => ''];
        }

        return ['activity_id' => $info['activity_id'] , 'goods_id' => $info['goods_id']];
    }


}
