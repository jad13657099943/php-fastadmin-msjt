<?php

namespace app\api\controller;

use app\common\controller\Api;

/**
 * 收藏
 */
class Collection extends Api
{
    protected $noNeedLogin = ['*'];//setCollect setCollect
    protected $noNeedRight = ['*'];

//初始化
    public function _initialize()
    {
        parent::_initialize();
        $this->collect = model('Collect');
        $this->litestoregoods = model('Litestoregoods');
        $this->article = model('Cmsarchives');


    }

    /**
     * 添加收藏
     */
    public function addCollect()
    {
        $params = $this->request->request();
        empty($params['goods_id']) && $this->error('缺少商品id');
        $uid = $this->auth->id;

        $data = ['uid' => $uid, 'goods_id' => $params['goods_id']];

        //添加收藏
        $save_rel = $this->collect->save_data([], $data);
        !$save_rel && $this->error('收藏失败');
        $this->success('收藏成功');
    }

    /**
     *  删除收藏
     * @param collect_id 商品id
     * @param type 1)商品收藏 2)文章收藏
     * @param article 文章id
     */
    public function delCollect()
    {
        $params = $this->request->request();
        empty($params['goods_id']) && $this->error('缺少商品id');
        $uid = $this->auth->id;

        //取消收藏
        $where = ['uid' => $uid, 'goods_id' => ['IN', $params['goods_id']]];
        $save_rel = $this->collect->save_data($where, ['status' => 2]);

        !$save_rel && $this->error('收藏失败');
        $this->success('取消收藏成功');
    }




    /**
     * 收藏列表
     * @param int uid 商家id
     */
    public function collectList()
    {
        $uid = $this->auth->id;
        $where = ['uid' => $uid, 'status' => 1];
        $field = 'id,goods_id';
        $with = ['goods' => function ($query) {
            $goods_field = 'goods_id,goods_name,image,stock_num,goods_price,goods_status,is_marketing,marketing_id activity_id';
            $query->withField($goods_field);
        }];

        //获取收藏列表
        $list = $this->collect->select_data($where, $field, 'id desc', $with);
        $this->success('', ['list' => $list]);
    }
}