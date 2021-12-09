<?php

namespace app\admin\controller\litestore;

use app\api\controller\Writeoff;
use app\common\controller\Backend;
use app\common\model\Config;
use app\common\model\Litestoreorderwriteoff;
use app\api\controller\Order;
use app\common\model\User;

/**
 *
 *
 * @icon fa fa-circle-o
 */
class Litestoreorder extends Backend
{

    /**
     * Litestoreorder模型对象
     * @var \app\admin\model\Litestoreorder
     */
    protected $model = null;

    public function _initialize()
    {

        parent::_initialize();
        $this->model = model('Litestoreorder');
        $this->order_goods = model('Litestoreordergoods');
        $this->view->assign("payStatusList", $this->model->getPayStatusList());
        $this->view->assign("freightStatusList", $this->model->getFreightStatusList());
        $this->view->assign("receiptStatusList", $this->model->getReceiptStatusList());
        $this->view->assign("orderStatusList", $this->model->getOrderStatusList());

        $this->kdniao_model = model('Kdniao');
        $companyList = $this->kdniao_model->select_data(['status' => 1], 'company')->toArray();
        foreach ($companyList as $k => $v) {
            $categorydata[$v['company']] = $v;
        }
        $this->view->assign("companyList", $categorydata);

        $this->withdraw = model('admin/Withdraw');
        $this->distributor_apply = model('common/Useragentapply');
        $this->litestore_order_refund = model('admin/Litestoreorderrefund');
        $this->config = new Config();

     
    }

    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */


    /**
     * 查看
     */
    public function index()
    {
        //当前是否为关联查询
        $this->relationSearch = true;
        //设置过滤方法
        $this->request->filter(['strip_tags']);

        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField'))
                return $this->selectpage();
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $wheres['litestoreorder.order_type'] = '10';
            //$wheres['litestoreorder.is_del'] = '0';
            $wheres['litestoreorder.total_price'] = array('>=', '0');
            $wheres['litestoreorder.type'] = 0;
            $wheres['litestoreorder.refund_status'] = array('neq', '10');
            $total = $this->model
                ->with(['address', 'goods', 'users'])
                ->where($wheres)
                ->where($where)
                ->order($sort, $order)
                ->count();
            $field = 'id,order_no,pay_status,pay_time,express_price,order_status,total_price,order_type,refund_status,
                      coupon_price,total_num,remark,nickname,mobile,freight_price,is_status,pay_price,consignee,reserved_telephone,use_integral,use_money,createtime';
            $list = $this->model
                ->field($field)
                ->where($where)
                ->where($wheres)
                ->with(['address' => function ($query) {
                    $query->field('order_id,name,phone,site');
                }, 'goods' => function ($query) {
                    $query->field('order_id,goods_name,key_name,goods_price,total_num,images,total_price,is_refund');
                }/*, 'users' => function ($query) {
                    $query->field('nickname,mobile');
                }*/
                ]                )
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();
            $scoreconfig=$this->config->getConfigData(['name'=> 'product_points']);
            foreach ($list as $k => $row) {
                $row['use_integral']=$row['use_integral'] /$scoreconfig;
                unset($list[$k]['address'], $list[$k]['users']);
                $row['createtime'] =date('Y-m-d H:i:s',$row['createtime']);
            }
            $result = array("total" => $total, "rows" => $list);

            return json($result);
        }
        //统计订单数量
        $rows = $this->model->statisticsCount(10 ,$this->auth->school_id);
        $this->view->assign('row', $rows);
        return $this->view->fetch();
    }


    public function detail()
    {
        if ($this->request->isPost()) {
            $id = input('post.ids');
            $row = $this->model->get($id);
            $param = $this->request->param();


//            if ($param['virtual_sn']) {
                $row['freight_status'] = "20";
                $row['freight_time'] = time();
//                $row['express_company'] = input('post.virtual_name');
//                $row['express_no'] = input('post.virtual_sn');
                $row['order_status'] = '30';
//                if ($row['express_company']) {
//                    $row['shipper_code'] = $this->kdniao_model->where(['company' => $row['express_company']])->value('code');
//                }

//                $row->save();
//            } else {

                if ($param['pay_price'] > 0) {
                    $row['pay_price'] = $param['pay_price'];
                }
                $row->save();
//            }
            //修改地址
            unset($param['pay_price'], $param['ids'], $param['virtual_name'], $param['virtual_sn'], $param['ids']);
            model('Litestoreorderaddress')->where(['order_id' => $id])->update($param);
            $this->success();
        }

        $param = $this->request->param();
        $row = $this->model->get($param['ids']);
        $litestoreordergoods_model = model('Litestoreordergoods');
        //订单信息
        $row['goods'] = $litestoreordergoods_model->select_data(['order_id' => $param['ids']], 'goods_name,goods_price,total_num,images ,key_name,is_refund');

        $this->view->assign('vo', $row);
        $this->view->assign('type', $param['type']);
        return $this->view->fetch();
    }

    public  function qrcode(){
        if ($this->request->isPost()) {
            $param = $this->request->param();
            //提货码
            !$param['qrcode'] && $this->error('提货码不能为空');
            $orderInfo = \app\admin\model\Litestoreorder::where('qrcode',$param['qrcode'])->find();
            !$orderInfo->id && $this->error('订单不存在');
            //验证订单状态
            $orderInfo->order_status == 50 && $this->error('此订单已核销');
            if ($orderInfo->type == 0){
                $orderInfo->order_status = 40;
            }
            $data = [
                'order_id' => $orderInfo->id,
                'uid' => $orderInfo->user_id,
                'remark' => '到店自取',
                'nickname' => $orderInfo->nickname,
                'express_no' => $orderInfo->express_no,
            ];
           $writeoff=new Litestoreorderwriteoff();
           if ($writeoff->save($data) && $orderInfo->save()){
               $config = new Config();
               $scoreconfig=$config ->getConfigData(['name'=> 'score_ratio']);
               $score=$orderInfo->integral * $scoreconfig;
               User::score($score, $orderInfo->user_id, '自提下单获得积分', 1);
               $this->success('核销成功');
           }

        }
        return $this->view->fetch();
     }
    /**
     * 待发货退款
     *
     *bu
     * */
    public function refund()
    {
        if ($this->request->isPost()) {

            //生成售后订单
            $order = new Order();

            //同意退款 ids status=1
//            $model = new Litestoreorderrefund();
            if ($order->applyAfterSale()) {
                $this->success();
            } else {
                $this->error();
            }
        }

        $param = $this->request->param();
        $row = $this->model->get($param['ids']);

        $row->goods_price = $row->pay_price + $row->coupon_price - $row->express_price;

        $row->pay_price = $row->pay_price - $row->refund_money;


        $litestoreordergoods_model = model('Litestoreordergoods');
        $row['goods'] = $litestoreordergoods_model->select_data(['order_id' => $param['ids']], 'id,total_price,goods_id ,goods_name,goods_price,total_num,images ,key_name,is_refund');
        foreach ($row['goods'] as $k => $goods) {
            $row['goods'][$k]->refund_money = round($goods->total_price - $goods->total_price / $row->goods_price * $row->coupon_price, 2);
        }
        //dump($row); exit();
        $this->view->assign('vo', $row);
        $this->view->assign('type', $param['type']);
        return $this->view->fetch();
    }

    /**
     * 导出订单数据
     * @throws \PHPExcel_Exception
     * @throws \PHPExcel_Writer_Exception
     */
    public function goods_out($ids = '')
    {

        $this->model = model('Litestoreordergoods');
        $this->order = model('Litestoreorder');
        $this->status = model('litestorecategory');
        $this->_model = model('Litestoregoods');
        $visit = new Visit();

        set_time_limit(0);
        $this->relationSearch = true;
        $search = $this->request->post('search');
        $ids = $this->request->post('ids');
        $filter = $this->request->post('filter');
        $op = $this->request->post('op');
        $this->request->get(['search' => $search, 'ids' => $ids, 'filter' => $filter, 'op' => $op]);

        //查询已支付订单id
        $pay_id = $this->order->where(['order_status' => ['gt', '10'], 'refund_status' => ['NEQ', '20'], 'pay_status' => 20])->column('id');
        $id = implode(',', $pay_id);



        list($where, $sort, $order, $offset, $limit) = $this->buildparams();

        $wheres = [
            'is_refund' => ['neq', 2],
            'order_id' => ['in', $id]
        ];
        $list = $this->model
            ->where($where)
            ->where($wheres)
            ->field('id,goods_id,sum(total_num) sales_actual,sum(total_price) sales_price,createtime')
            ->group('goods_id')
            ->order('sales_actual', 'desc')
            ->limit($offset, $limit)
            ->select();

        $datas = [];

        foreach ($list as $k => $v) {
            $item = $this->_model->where(['goods_id' => $v['goods_id']])->field('goods_price,goods_name,category_id,vip_price')->find();
            $data = $this->status->where(['id' => $item['category_id']])->value('name');
            //统计商品订单量
            $list[$k]['goods_count'] =
                //成交用户量
            $list[$k]['user_count'] = $this->model->where($where)->where($wheres)->group('user_id')->count();
            $visit_visit = $visit->where($where)->where(['goods_id' => $v['goods_id']])->count();
            $list[$k]['visit'] = $visit_visit ? $visit_visit : 0;

            $datas[$k]['id'] = $k+1;
            $datas[$k]['goods_name'] = $item['goods_name'];
            $datas[$k]['status'] = $data;
            $datas[$k]['goods_price'] = $item['goods_price'];
            $datas[$k]['goods_count'] = $this->model->where($where)->where(['goods_id' => $v['goods_id'], 'order_id' => ['in', $id]])->count(goods_id);
            $datas[$k]['visit'] = $list[$k]['visit'];
            $datas[$k]['user_count'] = $list[$k]['user_count'];
            $datas[$k]['sales_actual'] = $v['sales_actual'];
            $datas[$k]['sales_price'] = $v['sales_price'];
        }

        $field_name = ['序号', '商品名称', '商品分类', '商品单价','订单量','访问量','成交用户量','销量','销售额'];

        $this->arrayToExcel('访问数据统计', $datas, '统计列表', $field_name);
    }



    /**
     * 导出订单数据
     * @throws \PHPExcel_Exception
     * @throws \PHPExcel_Writer_Exception
     */
    public function out($ids = '')
    {
        $params = $this->request->request();

        $filter = json_decode($params['filter'], true);
        $op = json_decode($params['op'], true);
//        dump($ids);die;
        //基础条件
        $where = [];
        $ids == 'all' && $where = [
            'order_type' => '10', 'is_del' => 0,
            'refund_status' => array('gt', '20'),
            'total_price' => array('gt', '0'),
            'type' => 0
        ];
        //拼接搜索条件
        foreach ($filter as $k => $v) {
            if ($op[$k] == 'RANGE') {
                $k = 'litestoreorder.' . $k;
                $op[$k] = 'between';
                $v = [strtotime(explode(' - ', $v)['0']), strtotime(explode(' - ', $v)['1'])];
            }
            $where[$k] = [$op[$k], $v];
        }

        //数据集
        $result = $this->model->with(['address', 'goods', 'user'])->where($where)->order('id desc')->select();

        $ids != 'all' && $where = ['litestoreorder.id' => ['in', $ids]];
        out2($result, '普通订单数据');
    }

    /**
     * 订单轮询
     */
    public function ajax_crontab()
    {
        $new_order_count = $this->model->where(['is_message' => 1, 'pay_status' => '20', 'activity_type' => 1, 'type' => 0])->count();
        $new_cut_down_order_count = $this->model->where(['is_message' => 1, 'pay_status' => '20', 'activity_type' => ['in', '4,5'], 'type' => 0])->count();
        $new_withdraw_count = $this->withdraw->where(['is_message' => 1, 'status' => 1])->count();
        $new_apply_count = $this->distributor_apply->where(['is_message' => 1, 'store_status' => 0])->count();
        $new_apply_update_count = $this->distributor_apply->where(['is_message' => 1, 'store_status' => 1])->count();
        $new_refund_count = $this->litestore_order_refund->where(['is_message' => 1, 'apply_status' => '1'])->count();
        $count = $new_order_count + $new_cut_down_order_count + $new_apply_update_count + $new_withdraw_count + $new_apply_count + $new_refund_count;
        return json(['is_tk' => $count > 0 ? 1 : 0, 'new_order_count' => $new_order_count, 'new_cut_down_order_count' => $new_cut_down_order_count,
            'new_withdraw_coun' => $new_withdraw_count, 'new_apply_count' => $new_apply_count, 'new_apply_update_count' => $new_apply_update_count, 'new_refund_count' => $new_refund_count]);

    }

    /**
     * 关闭弹窗
     */
    public function close_window()
    {
        $this->model->where(['is_message' => 1, 'pay_status' => '20'])->update(['is_message' => 0]);
        $this->withdraw->where(['is_message' => 1])->update(['is_message' => 0]);
        $this->distributor_apply->where(['is_message' => 1, 'status' => 0])->update(['is_message' => 0]);
        $this->litestore_order_refund->where(['is_message' => 1])->update(['is_message' => 0]);
    }
    /**
     * 批量打印
     */
    public function prints($ids = '')
    {
        //查询下单人名称  和收货
        $row = $this->model->with([
            'user' => function ($query) {
                $query->field('id,username');
            }
            , 'addresss' => function ($query) {
                $query->field('order_id,name,phone,site')->select();
            }
        ])->where(['id' => ['in',$ids]])->field('id,user_id,order_no,createtime')->select();

        foreach ($row as $k => $v){
            $money = 0;
            $row[$k]['order'] = $this->order_goods->where(['order_id' => $v['id']])->field('order_id,goods_name,total_num,goods_price,total_price')->select();
            foreach ($row[$k]['order'] as $key => $value){
                $money +=  $value['total_price'];
            }
            $row[$k]['sum_money'] =$money;
            $row[$k]['stringmoney'] = $this->num_to_rmb($money);
        }

        $kf_phone = config('site.kf_phone');

        $this->view->assign('vo', $row);
        $this->view->assign('kf_phone', $kf_phone);
        $this->view->assign('ren', $this->auth->nickname);
        return $this->view->fetch();
    }
    /**
     *数字金额转换成中文大写金额的函数
     *String Int $num 要转换的小写数字或小写字符串
     *return 大写字母
     *小数位为两位
     **/
    function num_to_rmb($num)
    {
        $c1 = "零壹贰叁肆伍陆柒捌玖";
        $c2 = "分角元拾佰仟万拾佰仟亿";
        //精确到分后面就不要了，所以只留两个小数位
        $num = round($num, 2);
        //将数字转化为整数
        $num = $num * 100;
        if (strlen($num) > 10) {
            return "金额太大，请检查";
        }
        $i = 0;
        $c = "";
        while (1) {
            if ($i == 0) {
                //获取最后一位数字
                $n = substr($num, strlen($num) - 1, 1);
            } else {
                $n = $num % 10;
            }
            //每次将最后一位数字转化为中文
            $p1 = substr($c1, 3 * $n, 3);
            $p2 = substr($c2, 3 * $i, 3);
            if ($n != '0' || ($n == '0' && ($p2 == '亿' || $p2 == '万' || $p2 == '元'))) {
                $c = $p1 . $p2 . $c;
            } else {
                $c = $p1 . $c;
            }
            $i = $i + 1;
            //去掉数字最后一位了
            $num = $num / 10;
            $num = (int)$num;
            //结束循环
            if ($num == 0) {
                break;
            }
        }
        $j = 0;
        $slen = strlen($c);
        while ($j < $slen) {
            //utf8一个汉字相当3个字符
            $m = substr($c, $j, 6);
            //处理数字中很多0的情况,每次循环去掉一个汉字“零”
            if ($m == '零元' || $m == '零万' || $m == '零亿' || $m == '零零') {
                $left = substr($c, 0, $j);
                $right = substr($c, $j + 3);
                $c = $left . $right;
                $j = $j - 3;
                $slen = $slen - 3;
            }
            $j = $j + 3;
        }
        //这个是为了去掉类似23.0中最后一个“零”字
        if (substr($c, strlen($c) - 3, 3) == '零') {
            $c = substr($c, 0, strlen($c) - 3);
        }
        //将处理的汉字加上“整”
        if (empty($c)) {
            return "零元整";
        } else {
            return $c . "整";
        }
    }

}
