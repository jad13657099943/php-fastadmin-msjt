<?php

namespace app\api\controller;

use app\common\controller\Api;
use app\common\model\Area;

class Adress extends Api
{
    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];

    public function _initialize()
    {
        parent::_initialize();
        $this->address = model('Litestoreaddress');
    }


    /**
     * 获取省市区
     * pid
     * type 1)省份  2）城市 3）区域
     */
    public function getArea()
    {
        $param = $this->request->request();
        $pid = $param['type'] == 0 ? 0 : $param['id'];
        $area_all = Area::getSelectId(['pid' => $pid], 'id,name');
        $this->success('12', ['list' => $area_all]);
    }


    /**
     * 添加地址
     * @param mobile
     * @param name
     * @param province
     * @param city
     * @param district
     * @param address
     * @param is_default 是否是默认地址 1）是 0）不是
     * @param address_id 地址id 删除 设为默认地址传
     */

    public function addAddress()
    {
        $data = $this->request->request();
        $data['uid'] = $this->auth->id;
        $data['school_id'] = $data['school_id'] ? $data['school_id'] : 0;
        !$data['uid'] && $this->error('请登录后操作');
        !$data['phone'] && $this->error('用户手机号不为空');
        !$data['name'] && $this->error('用户名不为空');

        if($data['school_id'] == 0){
            !$data['province'] && $this->error('province不为空');
            !$data['city'] && $this->error('city不为空');
            !$data['region'] && $this->error('region不为空');
        }

        !$data['details'] && $this->error('details不为空');
        $info = Area::getCityId($data['city']);
        $add = [
            'isdefault' => $data['isdefault'],
            'user_id' => $data['uid'],
            'phone' => $data['phone'],
            'name' => $data['name'],
            'province' => $data['school_id'] == 0 ? $data['province'] : '',
            'city' => $data['school_id'] == 0 ? $data['city'] : '',
            'province_id' => $info ? $info->pid : 0,
            'city_id' => $info ? $info->id : 0,
            'region' => $data['school_id'] == 0 ? $data['region'] :'',
            'site' => $data['school_id'] == 0 ? $data['province'] . $data['city'] . $data['region'] . $data['details'] : $data['details'],
            'createtime' => time(),
            'school_id' => $data['school_id'],
            'detail' => $data['details'],
        ];
//        dump($add);die;

        //添加
        $this->address->startTrans();
        $setid = $this->address->insertGetId($add);

        //判断是否有默认地址
        $map['isdefault'] = 1;
        $map['user_id'] = $data['uid'];

        $map['address_id'] = array('neq', $setid);
        $add_id = $this->address->getOneDate($map, 'address_id');
        //取消
        if ($data['isdefault'] == 1 && $add_id['address_id']) {
            $setid2 = $this->address->cancel_default($data['uid'], $setid);
        } else {
            $setid2 = 1;
        }
        if ($setid !== false) {
            $this->address->commit();
            $this->success('操作成功');
        } else {
            $this->address->rollback();
            $this->success('操作失败');
        }


    }

    /*
     * 删除地址
     * @param uid 用户id
     * @param address_id 地址id
     *
     */
    public function delAddress()
    {
        $data = $this->request->request();
        $data['uid'] = $this->auth->id;
        !$data['uid'] && $this->error('请登录后操作');
        !$data['address_id'] && $this->error('address_id不能为空');
        $where = [
            'user_id' => $data['uid'],
            'address_id' => $data['address_id']
        ];
        $save['isdefault'] = '-1';
        $setid = $this->address->update_data($where, $save);

        if ($setid !== false) {
            $this->success('操作成功');
        } else {
            $this->error('操作失败');
        }
    }

    /*
     * 编辑地址
     * @param address_id 地址id
     * @param province 省
     * @param city 市
     * @param region 区
     * @param isdefault 是否为默认地址 0)否 1)是
     */
    public function updateAddress()
    {
        $param = $this->request->except(['s', 'token']);
        !$this->auth->id && $this->error('请登录后操作');
        !$param['address_id'] && $this->error('address_id不能为空');
        $where['address_id'] = $param['address_id'];
        $param['user_id'] = $this->auth->id;
        $param['updatetime'] = time();
        if ($param['province'] == 'undefined') unset($param['province'], $param['city'], $param['region']);
        if (isset($param['city'])) {
            $info = Area::getCityId($param['city']);
            $param['province_id'] = $info ? $info->pid : 0;
            $param['city_id'] = $info ? $info->id : 0;
            $param['site'] = $param['province'] . $param['city'] . $param['region'] . $param['detail'];
        }
        $setid = $this->address->update_data($where, $param);

        //判断是否是默认地址，是就取消别的默认地址
        $map['isdefault'] = '1';
        $map['user_id'] = $this->auth->id;
        $map['address_id'] = array('neq', $param['address_id']);
        $add_id = $this->address->getOneDate($map, 'address_id');
        //设置是默认地址，取消其他默认地址
        if ($param['isdefault'] == 1 && $add_id['address_id']) {
            $setid2 = $this->address->cancel_default($this->auth->id, $param['address_id']);
        } else {
            $setid2 = 1;
        }
        if ($setid && $setid2 !== false) {
            $this->success('修改成功');
        } else {
            $this->error('修改失败');
        }
    }

    /*
     * 设为默认地址
     * @param address_id 地址id
     * @param isdefault  是否为默认 0)否 1)是
     */
    public function setDefault()
    {
        $param = $this->request->request();
        !$this->auth->id && $this->error('请登录后操作');
        !$param['address_id'] && $this->error('address_id不能为空');

        $where['address_id'] = $param['address_id'];
        $save['isdefault'] = '1';
        $setid = $this->address->update_data($where, $save);
        $this->address->cancel_default($this->auth->id, $param['address_id']);
        if ($setid !== false) {
            $this->success('设置默认地址成功');
        } else {
            $this->error('设置默认地址失败');
        }
    }


    /**
     *获取所有地址  获取地址
     * @param type 1)获取所有地址  2）获取单条地址
     * @param
     *
     */
    public function getAddress()
    {
        $param = $this->request->request();
        !$this->auth->id && $this->error('请登录后操作');

        $where = array('isdefault' => ['neq', -1], 'user_id' => $this->auth->id);
        $param['school_id'] && $where['school_id'] = $param['school_id'];

        $field = 'address_id ,name ,phone,isdefault,province,city,region,detail,site';
        $row = $this->address->getWhereDates($where, $field, 'isdefault desc', $param['page'], $param['pagesize']);
        $this->success('获取成功', ['list' => $row]);
    }

    /**
     * 获取地址
     */
    public function getAddressById()
    {
        $data = $this->request->request(['address_id']);
        $data['uid'] = $this->auth->id;
        !$data['uid'] && $this->error('请登录后操作');
        $data['school_id'] && $where['school_id'] = $data['school_id'];
        $where['address_id'] = $data['address_id'];
        $field = 'address_id ,name ,phone,isdefault,province,city,region,detail,site';

        $row = $this->adress->getOneDate($where, $field, 'isdefault desc');

        $this->success('获取成功', ['list' => $row]);
    }


}




