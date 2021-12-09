<?php

namespace app\admin\controller\coupon;

use app\admin\controller\user\User;
use app\common\controller\Backend;
use GatewayWorker\Lib\Db;

/**
 * 会员管理
 *
 * @icon fa fa-user
 */
class Couponrecord extends Backend
{

    protected $relationSearch = false;


    /**
     * @var \app\admin\model\User
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('Couponrecord');
    }

    public function index(){
        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $total = $this->model
                ->with(['user','coupon'])
                ->where($where)
                ->order($sort, $order)
                ->count();
            $list = $this->model
                ->with(['user','coupon'])
                ->where($where)
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();
            foreach ($list as $k=>$v){
                if($v['status']==1){
                    $list[$k]['status_text']='未使用';
                }
                if($v['status']==2){
                    $list[$k]['status_text']='已使用';
                }
                if($v['status']==3){
                    $list[$k]['status_text']='已过期';
                }
            }
            $list = collection($list)->toArray();
            $result = array("total" => $total, "rows" => $list);

            return json($result);
        }
        return $this->view->fetch();
    }



    public function add()
    {
        if ($this->request->isPost()) {
            $params = $this->request->post("row/a");

            if($params['status'] == 1){
                $ids = db('user')->column('id');
            }else
                $ids = explode(',',$params['week']);

            if(!is_numeric($ids[0]))
               $this->error('请选择赠送用户');

            if($params['coupon_id'] == 0)
                $this->error('请选择优惠券');

            $params['number'] = 1;
            $coupon_info = db('coupon')->where('id',$params['coupon_id'])->find();

            if($coupon_info['status'] !=1)
                $this->error('优惠券已失效');

            //判断优惠券数量是否充足

            $number = count($ids) * $params['number'];//赠送出去数量

            if(($number + $coupon_info['receive_number']) > $coupon_info['number'])
                $this->error('优惠券数量不足');
            //修改优惠券数据
            $update_coupon = model('Coupon')->update_data(['id' => $params['coupon_id']] ,['receive_number' => $coupon_info['receive_number'] + $number]);

            //添加优惠券记录
            for ($i = 0; $i <= (count($ids) - 1); $i++) {
                $coupon_record_info[$i]['uid'] = $ids[$i];
                $coupon_record_info[$i]['create_time'] = time();
                $coupon_record_info[$i]['title'] = $coupon_info['title'];
                $coupon_record_info[$i]['coupon_id'] = $coupon_info['id'];
                $coupon_record_info[$i]['endtime'] = $coupon_info['endtime'];
                $coupon_record_info[$i]['starttime'] = $coupon_info['starttime'];
                $coupon_record_info[$i]['condition'] = $coupon_info['condition'];
                $coupon_record_info[$i]['coupon_price'] = $coupon_info['coupon_price'];
            }
            $add_coupon_record = $this->model->saveAll($coupon_record_info);
             if($update_coupon && $add_coupon_record)
                 $this->success();
             else
                 $this->error();
        }

        $list = model('Coupon')->select_data(['status'=>1],'id,title name');
        $categorydata = [0 => ['type' => 'all', 'name' => __('None')]];
        foreach ($list as $k => $v)
        {
            $categorydata[$v['id']] = $v;
        }
        $this->view->assign("parentList", $categorydata);

        return $this->view->fetch();
    }


    //获取用户列表
    public function select(){
        if ($this->request->isAjax()) {
            $model = new User();
            return $model->index();
        }
        return $this->view->fetch();
    }


}

