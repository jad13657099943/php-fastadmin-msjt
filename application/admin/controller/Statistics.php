<?php

namespace app\admin\controller;

use app\admin\model\msjt\goods\Goods;
use app\admin\model\msjt\users\Order;
use app\admin\model\msjt\users\OrderGoods;
use app\admin\model\msjt\users\Sale;
use app\admin\model\msjt\users\Users;
use app\common\controller\Backend;
use app\common\model\User;
use app\common\model\Visit;
use app\admin\model\Litestoreorder as OrderModel;
use fast\Date;
use think\Config;

/**
 * 控制台
 *
 * @icon fa fa-dashboard
 * @remark 用于展示当前系统中的统计数据、统计报表及重要实时数据
 */
class Statistics extends Backend
{

    protected $order;
    protected $orders;
    protected $sale;
    protected $goods;
    protected $orderGoods;
    protected $users;

    public function _initialize()
    {

        parent::_initialize();
        $this->order = new Order();
        $this->orders = new \app\admin\model\msjt\goods\curriculum\Order();
        $this->sale = new Sale();
        $this->goods = new Goods();
        $this->orderGoods = new OrderGoods();
        $this->users = new Users();
    }

    /**
     * 查看
     */
    public function index()
    {
        $start_time = strtotime(\date('Y-m-01'));
        // $this->order = model('Litestoreorder');

        for ($i = 0; $i < date("d"); $i++) {
            $starts_time = $start_time + ($i * 86400);
            $end_time = $starts_time + 86399;

            //销售额
            $where = [
                'createtime' => ['BETWEEN', [$starts_time, $end_time]],
                'status' => ['in', [2, 4, 6]],
                'state' => 1
            ];
            $where2 = [
                'status' => 2,
                'createtime' => ['BETWEEN', [$starts_time, $end_time]],
            ];

            //未支付金额
            $unpaid_where['createtime'] = ['BETWEEN', [$starts_time, $end_time]];
            $unpaid_where['status'] = 1;

            //已支付金额
            $sucess[$i] = $this->order->where($where)->sum('money') + $this->orders->where($where2)->sum('money');


            $sucess2[$i] = $this->order->where($unpaid_where)->sum('money') + $this->orders->where($unpaid_where)->sum('money');

            //已支付订单数
            $sum[$i] = $this->order->where($where)->count() + $this->orders->where($where2)->count();

            //未支付订单数
            $sum2[$i] = $this->order->where($unpaid_where)->count() + $this->orders->where($unpaid_where)->count();
            $data[$i] = date('m/d', $starts_time);
        }

        unset($where);
        $where['createtime'] = ['gt', $start_time];
        $last_month_where['createtime'] = ['between', [strtotime(date('Y-m-01', strtotime('-1 month'))), $start_time]];

        $whereStatus['pay_status'] = 20;


        $count_list = [
            'add_order_count' => Order::where('status', 'in', [2, 4, 6])->where('state', 1)->whereTime('createtime', 'month')->count() + $this->orders->where('status', 2)->whereTime('createtime', 'month')->count(),
            'del_order_count' => $this->sale->where('status', 4)->whereTime('createtime', 'month')->count(),//本月退单
            'total_order_count' => Order::where('status', 'in', [2, 4, 6])->where('state', 1)->count(),//总订单数
            'sums_amount' => $this->sale->where('status', 4)->whereTime('createtime', 'month')->sum('sale_money'),//本月退钱
            'moth_pay' => Order::where('status', 'in', [2, 4, 6])->where('state', 1)->whereTime('createtime', 'month')->sum('money') +
                $this->orders->where('status', 2)->whereTime('createtime', 'month')->sum('money'),//本月销售额
            'total_order_pay' => Order::where('status', 'in', [2, 4, 6])->where('state', 1)->sum('money') +
                $this->orders->where('status', 2)->sum('money'),//总销售额
        ];
        //当月的第一天和最后一天
        $startDate = date('Y-m-01', strtotime(date("Y-m-d")));
        $endDate = date('Y-m-d', strtotime("$startDate +1 month -1 day"));
        $allDate_to = [];
        $allDate = $this->getMonthDays();
        $begintime = date("Y-m-d H:i:s", mktime(0, 0, 0, date('m'), date('d'), date('Y')));
        $endtime = date("Y-m-d H:i:s", mktime(0, 0, 0, date('m'), date('d') + 1, date('Y')) - 1);
        foreach ($allDate as $k => $v) {
            if (strtotime($v) <= strtotime($endtime)) {
                $allDate_to[$k]['time'] = $v;
                //开始时间 $v
                $start_time = strtotime($v);
                //结束时间
                $end_time = $start_time + 60 * 60 * 24 - 1;
                $allDate_to[$k]['Order_form'] = $this->order->whereTime('createtime', 'between', [$start_time, $end_time])->where('status', 'in', [2, 4, 6])->where('state', 1)->count()
                    + $this->orders->whereTime('createtime', 'between', [$start_time, $end_time])->where('status', 2)->count();;
                $allDate_to[$k]['Return_order'] = $this->sale->whereTime('createtime', 'between', [$start_time, $end_time])->where('status', 4)->count();
                //获取订单钱
                $pay_pricea = 0;
                //获取订单使用钱
                $use_moneya = 0;
                $allDate_to[$k]['Order_amount'] = $this->order->whereTime('createtime', 'between', [$start_time, $end_time])->where('status', 'in', [2, 4, 6])->where('state', 1)->sum('money') +
                    $this->orders->whereTime('createtime', 'between', [$start_time, $end_time])->where('status', 2)->sum('money');;

                //    $pay_price = orderModel::whereTime('createtime', 'between', [$start_time, $end_time])->where('order_refund', 1)->sum('pay_price');
                //   $use_money = orderModel::whereTime('createtime', 'between', [$start_time, $end_time])->where('order_refund', 1)->sum('use_money');
                $allDate_to[$k]['Refund_amount'] = $this->sale->whereTime('createtime', 'between', [$start_time, $end_time])->where('status', 4)->sum('sale_money');


                /*  $allDate_to[$k]['use_money'] = orderModel::whereTime('createtime', 'between', [$start_time, $end_time])->where('pay_status', 20)->where('order_refund', 'neq', 1)->where('order_status', 40)->sum('use_money');
                  $allDate_to[$k]['use_code'] = orderModel::whereTime('createtime', 'between', [$start_time, $end_time])->where('pay_status', 20)->where('order_refund', 'neq', 1)->sum('use_integral');*/
                /* foreach ($allDate_to as $key => $val) {
                     $allDate_to[$k]['sum_amount'] = $val['Order_amount'] - $val['Refund_amount'];
                 }*/
            }

        }
        krsort($allDate_to);
        $this->view->assign([
            'sum' => $sum,
            'sum2' => $sum2,
            'sucess' => $sucess,
            'sucess2' => $sucess2,
            'data' => $data,
            'count_list' => $count_list,
            //当月第一天
            'startDate' => $startDate,
            'endDate' => $endDate,
            'allDate_to' => $allDate_to,
        ]);
        return $this->view->fetch();
    }

    //获取本月所有时间
    private function getMonthDays()
    {
        $monthDays = [];
        $firstDay = date('Y-m-01', time());
        $i = 0;
        $lastDay = date('Y-m-d', strtotime("$firstDay +1 month -1 day"));
        while (date('Y-m-d', strtotime("$firstDay +$i days")) <= $lastDay) {
            $monthDays[] = date('Y-m-d', strtotime("$firstDay +$i days"));
            $i++;
        }
        return $monthDays;
    }

    //统计
    public function goods()
    {

        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }

            $order_no = $this->order->where('status', 'in', [2, 4, 6])->where('state', 1)->column('order_no');
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();

            $total = $this->goods
                ->where($where)
                ->order($sort, $order)
                ->count();

            $list = $this->goods->with('type')
                ->where($where)
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();

            foreach ($list as $item) {
                $orderNoArray = $this->orderGoods->where('order_no', 'in', $order_no)->where('goods_id', $item->id)->where('status', 1)->column('order_no');
                $orderArray = array_unique($orderNoArray);
                $item->goods_count = count($orderArray);
                $userIdArray = $this->order->where('order_no', 'in', $orderArray)->column('user_id');
                $item->user_count = count(array_unique($userIdArray));
                $item->sales_actual = $this->orderGoods->where('order_no', 'in', $order_no)->where('goods_id', $item->id)->where('status', 1)->sum('num');
                $item->sales_price = $this->orderGoods->where('order_no', 'in', $order_no)->where('goods_id', $item->id)->where('status', 1)->sum('money');
            }

            $list = collection($list)->toArray();

            $result = array("total" => $total, "rows" => $list);

            return json($result);
        }
        return $this->view->fetch();
    }


    /**
     * 查看
     */
    public function user()
    {
        $today_time = strtotime(\date('Y-m-d')); //今天凌晨时间错
        $month_time = strtotime(\date('Y-m-01')); //本月月初时间错


        $visit = new Visit();
        $user_model = model('User');

        for ($i = 0; $i < date('d'); $i++) {
            $start_time = $month_time + ($i * 86400);
            $end_time = $start_time + 86399;

            $where = [];
            $day = date("m/d", $start_time);
            $data[$i] = $day;

            $where['createtime'] = ['BETWEEN', $start_time . ',' . $end_time];
            $reg[$i] = $this->users->where($where)->count(); //注册用户

            //  $login[$i] = $this->users->where(['createtime' => ['between', [$start_time, $end_time]], 'status' => 2])->group('user_id')->count();//登陆
        }


        //今日访客数
        /*  $where_data['create_time'] = ['gt', $today_time];
          $where_data['status'] = 2;
          $day_reg = $visit->where($where_data)->count();*/

        //今日注册量
        $where_day['createtime'] = ['gt', $today_time];
        $day_login = $this->users->where($where_day)->count();

        //本月访客数
        /* $where_data['create_time'] = ['gt', $month_time];
         $month_reg = $visit->where($where_data)->group('user_id')->count();*/

        //本月注册用户
        $where_day['createtime'] = ['gt', $month_time];
        $month_login = $this->users->where($where_day)->count();

        //总访客数
        /*   $count_reg = $visit->where(['status' => 2])->group('user_id')->count();*/

        //总用户
        $monthcount = $this->users->count();


        $visit_where['create_time'] = ['gt', $today_time];
        $today = $this->count_visit($visit_where, $month_time); //统计今天

        $visit_where['create_time'] = ['gt', $month_time];
        $month = $this->count_visit($visit_where, $month_time); //统计这个月

        $this->view->assign([
            'reg' => $reg,
            'login' => $login,
            'data' => $data,
            'day_login' => $day_login,
            'month_login' => $month_login,
            'monthcount' => $monthcount,
            'oldvisit' => $today['old_visit'],
            'newvisit' => $today['new_visit'],
            'oldvisitmonth' => $month['old_visit'],
            'newvisitmonth' => $month['new_visit'],
        ]);


        //设置过滤方法
        $this->request->filter(['strip_tags']);
        //当前是否为关联查询
//        $this->relationSearch = true;
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }

            list($where, $sort, $order, $offset, $limit) = $this->buildparams();


            $total = $visit
                ->where(['visit.status' => 2])
                ->where($where)->with(['user' => function ($query) {
                    $query->field('user.id as user_idd,nickname,avatar,mobile');
                }])
                ->order($sort, $order)
                ->count();
            $list = $visit
                ->where(['visit.status' => 2])
                ->where($where)->with(['user' => function ($query) {
                    $query->field('user.id as user_idd,nickname,avatar,mobile');
                }])
                ->order('id desc')
                ->limit($offset, $limit)
                ->select();

            $result = array("total" => $total, "rows" => $list);

            return json($result);
        }
        return $this->view->fetch();
    }


    //统计新客户 老客户说访问数量
    public function count_visit($where, $month_time)
    {

        $visit = new Visit();
        $res = $visit->group('user_id')->where($where)->where('status', 2)->select(); //查询访问用户

        $new_visit = $old_visit = 0;
        foreach ($res as $k => $v) {
            $count = $visit->where('user_id', $v->user_id)->where(['create_time' => ['gt', $month_time], 'status' => 2])->count(); //统计新老客户
            if ($count == 1) {
                $new_visit++;
            } else
                $old_visit++;
        }

        return compact('new_visit', 'old_visit');
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

        $ids && $wheres['goods_id'] = ['in', $ids];
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
            //成交用户量
            $list[$k]['user_count'] = $this->model->where($where)->where($wheres)->group('user_id')->count();
            $visit_visit = $visit->where($where)->where(['goods_id' => $v['goods_id']])->count();
            $list[$k]['visit'] = $visit_visit ? $visit_visit : 0;

            $datas[$k]['id'] = $k + 1;
            $datas[$k]['goods_name'] = $item['goods_name'];
            $datas[$k]['status'] = $data;
            $datas[$k]['goods_price'] = $item['goods_price'];
            $datas[$k]['goods_count'] = $this->model->where($where)->where(['goods_id' => $v['goods_id'], 'order_id' => ['in', $id]])->count('goods_id');
            $datas[$k]['visit'] = $list[$k]['visit'];
            $datas[$k]['user_count'] = $list[$k]['user_count'];
            $datas[$k]['sales_actual'] = $v['sales_actual'];
            $datas[$k]['sales_price'] = $v['sales_price'];
        }

        $field_name = ['序号', '商品名称', '商品分类', '商品单价', '订单量', '访问量', '成交用户量', '销量', '销售额'];

        $this->arrayToExcel('访问数据统计', $datas, '统计列表', $field_name);
    }


    /**
     * 导出数据
     * @throws \PHPExcel_Exception
     * @throws \PHPExcel_Writer_Exception
     */
    public function out($ids = '')
    {
        set_time_limit(0);
        $this->relationSearch = true;
        $search = $this->request->post('search');
        $ids = $this->request->post('ids');
        $filter = $this->request->post('filter');
        $op = $this->request->post('op');
        $this->request->get(['search' => $search, 'ids' => $ids, 'filter' => $filter, 'op' => $op]);

        list($where, $sort, $order, $offset, $limit) = $this->buildparams();

        //查询已支付订单id
        $visit = new Visit();

        if ($ids != 'all') {
            unset($where);
            $where['jrkj_visit.id'] = ['in', $ids];
        }


        $list = $visit
            ->where(['visit.status' => 2])
            ->where($where)->with(['user' => function ($query) {
                $query->field('nickname,mobile');
            }])
            ->field('user_id,create_time')
            ->order($sort, $order)
            ->limit($offset, $limit)
            ->select();

        $datas = [];

        foreach ($list as $k => $v) {
            $datas[$k]['id'] = $k + 1;
            $datas[$k]['nickname'] = $v->user->nickname; //名称
            $datas[$k]['mobile'] = $v->user->mobile; //名称
            $datas[$k]['create_time'] = date('Y-m-d H:i:s', $v->create_time); //商品价格
        }


        $field_name = ['序号', '昵称', '手机号码', '访问时间'];

        $this->arrayToExcel('访问数据统计', $datas, '统计列表', $field_name);
    }

    /**
     * 数据导出到excel
     * @param string $filename 导出文件名称   例：test
     * @param array $array 导出数据 二维数组    例：[['张三',15,'男'],['李四',18,'男']]
     * @param string $title Excel表格文件标题,不传则代表不设置标题    例：成员信息表
     * @param array $head excel表格列头   例：['姓名','年龄','性别']
     * @throws \PHPExcel_Exception
     * @throws \PHPExcel_Reader_Exception
     * @throws \PHPExcel_Writer_Exception
     */
    public function arrayToExcel($filename, array $array, $title = null, array $head = [])
    {
        vendor("phpoffice.phpexcel.Classes.PHPExcel");
        vendor("phpoffice.phpexcel.Classes.PHPExcel.PHPExcel_IOFactory");
        vendor("phpoffice.phpexcel.Classes.PHPExcel.Style.Alignment");

        //实例化excel对象
        $excel = new \PHPExcel();
        $excel->setActiveSheetIndex(0);

        $cells = [
            'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z',
            'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI', 'AJ', 'AK', 'AL', 'AM', 'AN', 'AO', 'AP', 'AQ', 'AR', 'AS', 'AT', 'AU', 'AV', 'AW', 'AX', 'AY', 'AZ'
        ];
        $cell = $excel->getActiveSheet();

        $headCount = count($head);
        $row = 1;
        if ($title) {
            $cell->setCellValue('A1', $title);
            $cell->getStyle('A1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $len = $headCount ? $headCount : count($array[0]) - 1;
            $len && $cell->mergeCells('A1:' . ($cells[$len - 1]) . '1');
            ++$row;
        }
        if ($headCount) {
            for ($j = 0; $j < $headCount; $j++) {
                $cell->setCellValue($cells[$j] . $row, $head[$j]);
            }
            ++$row;
        }

        $len = count($array);
        for ($i = 0; $i < $len; $i++) {
            $arr_len = count($array[$i]);
            for ($j = 0; $j < $arr_len; $j++) {
                $cell->setCellValue($cells[$j] . ($i + $row), array_shift($array[$i]));
            }
        }

        header('Content-Type: application/vnd.ms-excel');
        header("Content-Disposition: attachment;filename=$filename.xls");
        header('Cache-Control: max-age=1');
        $excelWrite = \PHPExcel_IOFactory::createWriter($excel, 'Excel5');
        $excelWrite->save('php://output');
    }
}
