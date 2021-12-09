<?php

namespace app\api\controller;

use app\common\controller\Api;

class Store extends Api
{
    protected $noNeedLogin = ['*'];
    protected $noNeedRight = '*';

    public function _initialize()
    {
        parent::_initialize();
        $this->store_model = model('Store');
        $this->user = model('User');
        $this->userAgentApply = model('UserAgentApply');
    }


    /**
     * 申请代理
     * @param longitude 经度
     * @param latitude 纬度
     * @param idcard_front 身份证正面
     * @param idcard_back 身份证反面
     */
    public function StoreApply()
    {
        $params = $this->request->except(['s', 'token']);
        $uid = $this->auth->id;

        $this->auth->vip_type == 0 && $this->error('您还不是vip会员');
        !$params['username'] && $this->error('username不存在'); //用户名
        !$params['mobile'] && $this->error('mobile不存在'); //电话号码
      //  !$params['id_card'] && $this->error('id_card不存在'); //身份证
        !$params['address'] && $this->error('address不存在');//详细地址
      // !$params['business_license'] && $this->error('business_license不存在'); //营业执照
      // !$params['identity_front'] && $this->error('identity_reverse不存在'); //省份证反面
       //!$params['identity_reverse'] && $this->error('type不存在'); //省份证正面
       // !$params['longitude'] && $this->error('longitude不存在'); //经度
     //   !$params['latitude'] && $this->error('latitude不存在'); //维度
       // !$params['opening_hours'] && $this->error('opening_hours不为空'); //营业时间
      //  !$params['closing_hours'] && $this->error('closing_hours不为空'); //营业时间
        !$params['site'] && $this->error('site不为空'); //省市区地址
//        !$params['store_name'] && $this->error('store_name不为空'); //代理店铺名称
        $params['uid'] = $uid;
        $params['order_no'] = order_sn(8);
        $params['apply_money'] = config('site.distribution_apply_money');

        $update = model('User')->where(['id' => $uid])->update(['is_store' => 1]);
        $this->userAgentApply->where(['uid' => $uid])->delete();
        $add_data = $this->userAgentApply->allowField(true)->save($params);
        if ($add_data !== false && $update !== false) {
            $this->success('申请成功', [
                'id' => $this->userAgentApply->getLastInsID(),
                'order_no' => $params['order_no'],
                'apply_money' => $params['apply_money'],
            ]);
        } else {
            $this->error('申请失败');
        }
    }


    /**
     * 是否申请代理商
     * @param vip_status 是否为vip代理商 0)不是 1)
     * @param store_status 申请状态 0）待审核 1）审核通过 2）审核失败
     */
    public function is_Apply($uid = '' ,$type ='')
    {
        $uid = $uid ? $uid : $this->auth->id;
        //查询最后一次申请代理商状态
        $data = $this->userAgentApply->find_data(['uid' => $uid ,'pay_time' => ['gt',0]], 'store_status');
        if (!$data) {
            if($type)
                return 3;
            //没有申请过返回3
            $this->success('', ['store_status' => 3]);
        }
        if($type)
            return $data['store_status'];

        $this->success('', $data);
    }

    /**
     * 获取入驻商家信息
     */
    public function getStoreInfo()
    {
        $uid = $this->auth->id;
        !$uid && $this->error('uid不存在');

        $store_info = $this->store_model->find_data(['uid' => $uid], '*');
        $store_info = empty($store_info) ? '' : $store_info;
        $this->success('成功', $store_info);
    }
}