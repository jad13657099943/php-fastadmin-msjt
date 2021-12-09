<?php

namespace app\admin\controller;

use app\common\controller\Backend;
use addons\voicenotice\library\Voicenotice as voice;
use think\Config;

/**
 * 控制台
 *
 * @icon fa fa-dashboard
 * @remark 用于展示当前系统中的统计数据、统计报表及重要实时数据
 */
class Dashboard extends Backend
{

    /**
     * 查看
     */
    public function index()
    {

        //消息队列
        /*
         * $content 文字提示
         * $admin_id 后台管理者id
         * $group_id 通知角色组
         * $loop 是否循环播放 true|false
         * $url  跳转地址
         * 提示方式 open  addons 弹窗|侧边栏
         * */
//        $worker = voice::addNotice($content, $admin_id, $group_id,$loop,$url,'open');
        //查出普通商品待发货订单的数量
    /*    $standby_count = model('Litestoreorder')->where(['order_status' => '20'])->count();
        //待发货订单数量
        $after_sale_count = model('Litestoreorderrefund')->where(['refund_status' => '10'])->count(); //售后待处理订单
        $integral_pending_count = model('Integralorder')->where(['order_status' => 0])->count();
        if ($standby_count){
            $worker = voice::addNotice(config('news')[2].':'.$standby_count . '个', 1, 1,false,config('url')[1],'open');
        }
        if ($after_sale_count){
            $worker = voice::addNotice(config('news')[3].':'.$after_sale_count . '个', 1, 1,false,config('url')[2],'open');
        }
        if ($integral_pending_count){
            $worker = voice::addNotice(config('news')[4].':'.$integral_pending_count . '个', 1, 1,false,config('url')[3],'open');
        }*/

        $seventtime = \fast\Date::unixtime('day', -9);
        $paylist = $createlist = [];
        $this->model = model('Litestoreorder');

        for ($i = 0; $i < 10; $i++)
        {
            $where2 = $where3 = [];
            $time = $seventtime + ($i * 86400);
            $end_time = $time + 86399;
            $where2['createtime'] = ['BETWEEN',$time .','.$end_time];

            $day = date("m/d", $time);
            $createlist[$day] = $this->model->goodsCount($where2); //订单数

            $where3['receipt_time'] = ['BETWEEN',$time .','.$end_time];
            $paylist[$day] =  $this->model->goodsCount($where3); //成交数
        }


        $user_model = model('User');
        $where['logintime'] = ['gt' , strtotime(date('Y-m-d'))];
        $wheres['jointime'] = ['gt' , strtotime(date('Y-m-d'))];
        $where2['createtime'] = ['gt' , strtotime(date('Y-m-d'))];
        $this->view->assign([
            'totaluser'        => $user_model->count(),
            'totalorder'       => $this->model->goodsCount(),
            'totalorderamount' => $this->model->sum('pay_price'),
            'todayuserlogin'   => $user_model->userCount($where),
            'todayusersignup'  => $user_model->userCount($wheres),
            'todayorder'       => $this->model->goodsCount($where2),
            'unsettleorder'    => $this->model->goodsCount(['order_status'=>20]),
            'paylist'          => $paylist,
            'createlist'       => $createlist,
            'totalviews'       => '',
            'addonversion'     => '',
            'uploadmode'     => '',
        ]);

        return $this->view->fetch();
    }

}
