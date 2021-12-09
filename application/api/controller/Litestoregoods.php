<?php

namespace app\api\controller;

use app\common\controller\Api;


/**
 * 菜品接口
 */
class Litestoregoods extends Api
{

    protected $noNeedLogin = ['index'];
    protected $noNeedRight = ['*'];
    public function _initialize()
    {
        parent::_initialize();
    }

    /**
     * 菜品首页
     *
     */
    public function index()
    {
        $merchant_info=[];
        $merchant_info['merchant_name']=config('site.merchant_name');
        $merchant_info['merchant_start_time']=config('site.merchant_start_time');
        $merchant_info['merchant_end_time']=config('site.merchant_end_time');
        $merchant_info['merchant_logo']=config('site.merchant_logo');
        $merchant_info['merchant_lng_lat']=config('site.merchant_lng_lat');
        $merchant_info['merchant_address']=config('site.merchant_address');
        $cate_list=model('Litestorecategory')->getLitestoreCategoryList([],'id,name');
        $page=$this->request->post("page");
        $pagesize=$this->request->post("pagesize");
        $cate_arr_list=collection($cate_list)->toArray();
        $default_cate_id= empty($this->request->post("cate_id"))?$cate_arr_list[0]['id']:$this->request->post("cate_id");
        $list=model('Litestoregoods')->getLitestoreGoodsList(['category_id'=>$default_cate_id],'goods_id,images,goods_name,goods_prom_price,sales_actual',$page,$pagesize);

        $this->success('请求成功',['merchant_info'=>$merchant_info,'cate_list'=>$cate_list,'list'=>$list]);


    }

}
