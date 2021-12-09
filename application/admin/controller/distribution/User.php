<?php

namespace app\admin\controller\distribution;

use addons\epay\library\Service;
use app\common\controller\Backend;
use app\common\model\Litestoreorder;
use app\common\model\Litestoreorderrefund;
use app\common\model\UserAgentApply;
use app\common\model\UserRebate;
use think\Db;

/**
 * 银行类别管理
 *
 * @icon fa fa-circle-o
 */
class User extends Backend
{

    /**
     * Type模型对象
     * @var \app\admin\model\
     */
    protected $model = null;
    protected $noNeedRight = ['getLevel'];

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\User();

    }

    public function refund($ids)
    {
        $row = \app\common\model\UserAgentApply::get($ids);
        if ($row) {

            //判断是否还有下级
            if (UserRebate::getByFirstId($row->uid)) {
                $this->error('该代理商还有分销用户,请转移用户后操作！');
            }

            $this->model->startTrans();

            //转移待核销订单
            $where = ['apply_id' => $ids, 'order_status' => ['in', '20,30'], 'is_status' => 2];
            $field = 'id,apply_id';
            $orderList = model('common/Litestoreorder')->field($field)->where($where)->select();
            if ($orderList) {
                $agent = UserAgentApply::get(56);
                foreach ($orderList as $order) {
                    $order->apply_id = 56;
                    $order->address->address_id = 56;
                    $order->address->name = $agent->store_name;
                    $order->address->detail = $agent->address;
                    $order->address->site = $agent->site;
                    $order->save();
                    $order->address->save();
                }
            }
            $remark = $this->request->param('remark');

            //退款流水数据
            $refundData = [
                'order_no' => $row->order_no,
                'money' => $row->order_no,
                'refund_no' => order_sn(9),
            ];

            //提交数据
            $params = [
                'out_trade_no' => $row->order_no,//商户订单号
                'out_refund_no' => $refundData['refund_no'],//退款单号
                'total_fee' => $row->pay_money * 100,//订单金额 必须是整数
                'refund_fee' => $row->pay_money * 100,//退款金额 必须是整数
                'refund_desc' => $remark,//退款原因
                'notify_url' => config('url_domain_root') . '/api/pay/agentRefund/',
            ];
            $row->store_status = 3;
            $row->msg = $remark;

            $pay = new \Yansongda\Pay\Pay(Service::getConfig('wechat'));
            $user = \app\common\model\User::get($row->uid);
            $user->distributor = 0;
            $user->distributor_id = 0;
            $user->is_store = 0;
            $user->count = 0;
            if ($row->save() && $user->save() && Litestoreorderrefund::create($refundData) && $pay->driver('wechat')->gateway('miniapp')->refund($params)) {
                $first = UserRebate::getByUid($row->uid);
                $first && self::proxyLevel($first->first_id);
                $this->model->commit();
                $this->success('操作成功');
            }

            $this->model->rollback();
            $this->error('操作失败');
        }
        $this->error('记录不存在!');
    }

    /**
     * 获取分销商等级信息
     */
    public function getLevel()
    {
        return model('admin/distributor/Level')->column('id,level');
    }

    /**
     * 查看
     */
    public function index()
    {
        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }
//            $this->relationSearch = true;
//            $wheres['invite_num'] = ['gt', 0];
            $wheres['distributor']=['eq',1];
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $total = $this->model
                ->where($where)
                ->where($wheres)
//                ->with(['agent'])
                ->order($sort, $order)
                ->count();

            $list = $this->model
                ->field('id,avatar,nickname,username,mobile,apply_money,invite_num,count,total_balance,balance,jointime,status')
                ->where($where)
                ->where($wheres)
//                ->with(['agent'])
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();

            $list = collection($list)->toArray();
            if ($list != null) {
                $withdraw = model('admin/Withdraw');
                foreach ($list as $k => $v) {
                    //$uids = db('user_rebate')->where(['first_id' => $v['id']])->column('uid');
                    // $list[$k]['count'] = $this->model->where(['id' => ['in', $uids], 'vip_type' => ['neq', 0]])->count();

                    $list[$k]['total_withdraw'] = $withdraw->where(['uid' => $v['id'], 'status' => 5])->sum('money');
                }
            }
            return array("total" => $total, "rows" => $list);
        }
        return $this->view->fetch();
    }


    public function detail($ids = '')
    {
        $info = model('Useragentapply')->where(['uid' => $ids, 'store_status' => 1])->order('create_time desc')->find();
        $this->assign('row', $info);
//        dump($info->toArray());die;
        return $this->fetch();
    }

    /**
     * 我的团队-一级分销员
     *
     * */

    public function team($ids = NULL)
    {
        if ($this->request->isAjax()) {

            $user_rebate_model = model('UserRebate');
            $userids = $user_rebate_model->where(['first_id' => $ids])->order('add_time desc')->column('uid');

            $wheres['id'] = ['in', $userids];

            list($where, $sort, $order, $offset, $limit) = $this->buildparams();//dump($where); exit();
            $result = admin_list($this->model, $where, $sort, $order, $offset, $limit, $wheres);
            return json($result);
        }
        $this->assignconfig('ids', $ids);
        $this->view->assign('ids', $ids);
        return $this->view->fetch();
    }


    /**
     * 我的团队 二级分销员
     *
     * */

    public function secondteam($ids = NULL)
    {
        if ($this->request->isAjax()) {
            $user_rebate_model = model('UserRebate');
            $userids = $user_rebate_model->where(['first_id' => $ids])->order('add_time desc')->column('uid');
            $wheres['id'] = ['in', $userids];
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $result = admin_list($this->model, $where = [], $sort, $order, $offset, $limit, $wheres);
            return json($result);
        }
        $this->assignconfig('ids', $ids);
        return $this->view->fetch();
    }


    /**
     * 佣金明细
     * */

    public function moneylog($ids = NULL)
    {
        if ($this->request->isAjax()) {
            $filter = (array)json_decode($this->request->get("filter", ''), TRUE);

            $wheres['uid'] = $filter['ids'];
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $member_log_model = model('common/Commission');
            $result = admin_list($member_log_model, $where = [], $sort, $order, $offset, $limit, $wheres);
            return json($result);
        }
        $this->assignconfig('ids', $ids);
        return $this->view->fetch();
    }


    /**
     * 分销订单
     * */

    public function order($ids = NULL)
    {
        if ($this->request->isAjax()) {
            $filter = (array)json_decode($this->request->get("filter", ''), TRUE);

            $wheres['superior_id'] = $filter['ids'];
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $user_rebate_back_model = model('UserRebateBack');
            $result = admin_list($user_rebate_back_model, $where = [], $sort, $order, $offset, $limit, $wheres);


            foreach ($result['rows'] as $k => $value) {
                $result['rows'][$k]['nickname'] = $this->model->where(['id' => $value['uid']])->value('nickname');

            }
            return json($result);
        }
        $this->assignconfig('ids', $ids);
        return $this->view->fetch();
    }

    /**
     * 转让用户
     */
    public function transfer($ids = '')
    {
        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }
            $wheres['distributor_id'] = ['neq', 0];
            $wheres['distributor'] = ['in', '1,2'];
            $wheres['id'] = ['neq', $ids];
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $total = $this->model
                ->where($where)
                ->where($wheres)
                ->order($sort, $order)
                ->count();

            $list = $this->model
                ->where($where)
                ->where($wheres)
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();

            $list = collection($list)->toArray();
            return array("total" => $total, "rows" => $list);
        }
        $this->assignconfig('ids', $ids);
        return $this->view->fetch();
    }

    /**
     * 转让操作
     */
    public function transferUser()
    {
        $users = $this->request->param('users');
        $fromUser = $this->request->param('fromUser');
        $toUser = $this->request->param('toUser');
        $userArray = explode(',', $users);

        //转让数量
        $total = count($userArray);

        //转让用户
        $fromUserInfo = \app\common\model\User::get($fromUser);

        //被转让用户
        $toUserInfo = \app\common\model\User::get($toUser);

        $fromUserInfo->invite_num -= $total;
        $toUserInfo->invite_num += $total;
        $where = [
            'uid' => ['in', $users],
            'first_id' => $fromUser,
        ];
        try {
            Db::startTrans();
            $result1 = $fromUserInfo->save();
            $result2 = $toUserInfo->save();
            $result3 = UserRebate::update(['first_id' => $toUser], $where);
//            $result4 = \app\common\model\User::update_data(['id' => ['IN',$userArray]],['pid' => $toUser]);
            if ($userArray) {
                foreach ($userArray as $userId) {
                    $userInfo = \app\common\model\User::get($userId);
                    $userInfo->pid = $toUser;
                    $userInfo->save();
                }
            }
            if ($result1 && $result2 && $result3) {
                self::proxyLevel($fromUserInfo->id);
                Db::commit();
                $this->success('转让成功');
            }
        } catch (\think\exception\PDOException $e) {
        }
        Db::rollback();
        $this->error('转让失败');
    }

    public function edit($ids)
    {
//        if ($this->request->isPost()) {
//            $filter = (array)json_decode($this->request->get("filter", ''), TRUE);
//            $wheres['uid'] = $filter['ids'];
//
//            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
//            $user_agent_apply = model('Useragentapply');
//            $result = admin_list($user_agent_apply, $where = [], $sort, $order, $offset, $limit, $wheres);
//            return json($result);
//        }

        $user_agent_apply = model('Useragentapply');
        $row = $user_agent_apply->get(['uid' => $ids]);
        if (!$row)
            $this->error(__('No Results were found'));
        $this->view->assign('row', $row);
        return $this->view->fetch();
    }

    public static function proxyLevel($userId)
    {
        $userRebate = model('UserRebate');
        $user = model('User');
        $userInfo = $user->get($userId);
        $userIds = $userRebate->where(['first_id' => $userId])->column('uid');
        $info = $user->where(['distributor' => ['neq', 0], 'distributor_id' => ['neq', 0], 'id' => ['in', $userIds]])->find();
        if (!$info && $userInfo && $userInfo->distributor_id != 0 && $userInfo->distributor == 2) {
            $userInfo->distributor = 1;
            $userInfo->save();
            return true;
        }
        return false;
    }

    /*
 * 导出
 */
    public function export()
    {
        $params = $this->request->request();

        //去除的字段
        $params['columns'] = str_replace("avatar,", "", $params['columns']);
//        $params['columns'] = str_replace("count,", "", $params['columns']);

        //详情表导出数据
        $columns2 = 'username,mobile,hours,id_card,site,address';
        $columns2 = explode(',', $columns2);

        $columns = explode(',', $params['columns']);


        $filter = json_decode($params['filter'], true);
        $op = json_decode($params['op'], true);

        //基础条件
        $where = ['distributor_id' => ['neq', 0], 'distributor' => ['in', '1,2']];
        $params['ids'] != 'all' && $where['user.id'] = ['in', $params['ids']];

        //拼接搜索条件
        foreach ($filter as $k => $v) {
            $op[$k] == 'LIKE' && $v = '%' . $v . '%';

            $where[$k] = [$op[$k], $v];
        }
        $result1 = $this->model->where($where)->with(['Useragentapply', 'agent'])->order('id desc')->select();


        $filename = "代理商数据";
        vendor('PHPExcel.PHPExcel');
        $objPHPExcel = new \PHPExcel();
        //设置保存版本格式
        $objWriter = new \PHPExcel_Writer_Excel5($objPHPExcel);

        //设置代理商表头
        $columns_key = 0;
        $letter = '';
        $array = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
        foreach ($columns as $k => $v) {
            $columns_key = $k + 2;
            $k > 25 ? $letter = 'A' . $array[$k - 26] : $letter = $array[$k + 1];
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($letter . '3', __($v));
        }

        //设置详情表头
        $columns_key2 = 0;
        foreach ($columns2 as $k => $v) {
            $columns_key2 = $k + $columns_key;
            $k + $columns_key > 25 ? $letter = 'A' . $array[$k + $columns_key - 26] : $letter = $array[$k + $columns_key];
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($letter . '3', __($v));
        }


        //改变此处设置的长度数值
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(35);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(12);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(13);
        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('N')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('O')->setWidth(12);
        $objPHPExcel->getActiveSheet()->getColumnDimension('P')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('Q')->setWidth(40);
        $objPHPExcel->getActiveSheet()->getColumnDimension('R')->setWidth(30);

        //输出表格
        $myRow = 3;
        foreach ($result1 as $key => $val) {
            $i = $key + 4;//表格是从4开始的
            $myRow += 1;

            //替换数据
            $val['time'] = date("Y-m-d H:i:s", $val['jointime']);
            $val['status'] == 'normal' ? $val['status'] = __('normal') : $val['status'] = __('hidden');
            $val['user_agent_apply']['id_card'] = ' ' . $val['user_agent_apply']['id_card'];

            //主表
            foreach ($columns as $k => $v) {
                $v == 'jointime' && $v = 'time';
                $objPHPExcel->getActiveSheet()->setCellValue($array[$k + 1] . $i, $val[$v]);
            }

            //详情表
            foreach ($columns2 as $k => $v) {
                $val['user_agent_apply']['hours'] = $val['user_agent_apply']['opening_hours'] . '-' . $val['user_agent_apply']['closing_hours'];
                $objPHPExcel->getActiveSheet()->setCellValue($array[$k + $columns_key] . $i, $val['user_agent_apply'][$v]);
            }
        }

        $objPHPExcel->getActiveSheet()->setCellValue('B2', '代理商基本信息');
        $objPHPExcel->getActiveSheet()->setCellValue($array[$columns_key] . '2', '代理商详细信息');

        //合并单元
        $objPHPExcel->getActiveSheet()->mergeCells('B2' . ':' . $array[$columns_key - 1] . '2');
        $objPHPExcel->getActiveSheet()->mergeCells($array[$columns_key] . '2' . ':' . $array[$columns_key2] . '2');

        //设置边框
        $style_array2 = array(
            'borders' => array(
                'allborders' => array(
                    'style' => \PHPExcel_Style_Border::BORDER_THIN
                )
            ));
        $objPHPExcel->getActiveSheet()->getStyle('B2:' . $letter . $myRow)->applyFromArray($style_array2);


        //改变此处设置表格样式
        $objPHPExcel->getActiveSheet()->getStyle('A1:AZ' . $myRow)->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);/*垂直居中*/
        $objPHPExcel->setActiveSheetIndex(0)->getstyle('A1:Az' . $myRow)->getAlignment()->setHorizontal(\PHPExcel_style_Alignment::HORIZONTAL_CENTER);/*水平居中*/

        $objPHPExcel->getActiveSheet()->getStyle('A2:AZ2')->getFont()->setSize(14);//表头字体大小
        $objPHPExcel->getActiveSheet()->getStyle('A2:AZ3')->getFont()->setBold(true);//表头字体加粗
        $objPHPExcel->getActiveSheet()->getStyle('A2:AZ3')->getFont()->setName('微软雅黑');//表头改变字体

        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
        header("Content-Type:application/force-download");
        header("Content-Type:application/vnd.ms-execl");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");
        header('Content-Disposition:attachment;filename=' . $filename . '.xls');
        header("Content-Transfer-Encoding:binary");
        $objWriter->save('php://output');
    }


}