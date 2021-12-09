<?php

namespace app\admin\controller\coupon;

use app\admin\model\User;
use app\admin\model\litestore\Coupon;
use app\common\controller\Backend;

/**
 * 
 *
 * @icon fa fa-circle-o
 */
class Litestorecoupondata extends Backend
{
    
    /**
     * Data模型对象
     * @var \app\admin\model\litestore\coupon\Data
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\litestore\coupon\Data;
        $this->view->assign("getTypeList", $this->model->getGetTypeList());
        $this->view->assign("userLevelDataList", $this->model->getUserLevelDataList());
    }

    public function index()
    {
        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $total = $this->model
                ->where($where)
                ->order($sort, $order)
                ->count();

            $list = $this->model
                ->field('id,user_id,litestore_coupon_id,get_type,add_time,use_time,order_sn')
                ->with(['user' => function($query) {
                    return $query->field('id, nickname, mobile');
                }, 'coupon' => function($query) {
                    return $query->field('id, name');
                }])
                ->where($where)
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();
            $list = collection($list)->toArray();
            $result = array("total" => $total, "rows" => $list);

            return json($result);
        }
        return $this->view->fetch();
    }


    public function sendCoupon(User $userModel,  Coupon $couponModel)
    {
        $request = $this->request->param()['row'];
        $query = $userModel;
        switch ($request['send_obj']) {
            case 'specifi_user':
                if (!$request['user_id']) {
                    $this->error(__('请选择需要发送的用户'));
                }
                $query->whereIn('id', explode(',', $request['user_id']));
                break;
            case 'user_level':
                if (!$request['user_level']) {
                    $this->error(__('请选择需要发送的等级'));
                }
                $query->whereIn('level_id', $request['user_level_data']);
                break;
            case 'user_birthday':
                $query->whereNotNull('birthday')
                    ->where('birthday', '>', $request['birthday_start'])
                    ->where('birthday', '<', $request['birthday_end']);
                break;
            case 'all':
                break;
        }
        $coupon = $couponModel->where('id', $request['litestore_coupon_id'])->find();
        $query->field('id')->chunk(25, function ($users) use ($coupon, $request) {
            $coupon_data = [];
            foreach ($users as $k => $user) {
                for ($i = 0; $i < $request['per_send_num']; $i++) {
                    $coupon_data[] = [
                        'user_id' => $user['id'],
                        'litestore_coupon_id' => $coupon->id,
                        'add_time' => time(),
                        'use_start_time' => $coupon->getCanUseTime('use_start_time'),
                        'use_end_time' => $coupon->getCanUseTime('use_end_time'),
                    ];
                }
            }
            $this->model->insertAll($coupon_data);
        });
        $send_num = $query->count() * $request['per_send_num'];
        $coupon->save(['receive_num'=>$coupon->receive_num + $send_num]);
        $this->success();
    }
}
