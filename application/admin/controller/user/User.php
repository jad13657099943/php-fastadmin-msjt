<?php

namespace app\admin\controller\user;

use app\common\controller\Backend;
use app\common\library\Auth;
use fast\Random;
use think\Db;

/**
 * 会员管理
 *
 * @icon fa fa-user
 */
class User extends Backend
{

    protected $relationSearch = false;


    /**
     * @var \app\admin\model\User
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\User();
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

            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $total = $this->model
                ->where($where)
//                ->where('mobile', '<>', '')
                ->order($sort, $order)
                ->count();
            $list = $this->model
                ->where($where)
//                ->where('mobile', '<>', '')
                ->field('id,avatar,nickname,mobile,vip_type,score,distributor,status,createtime,pid,money')
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();

            $result = array("total" => $total, "rows" => $list);

            return json($result);
        }
        return $this->view->fetch();
    }

    /**
     * 编辑
     */
    public function edit($ids = NULL)
    {
        $row = $this->model->get($ids);
        if (!$row)
            $this->error(__('No Results were found'));
        if ($this->request->isPost()) {
            $params = $this->request->post("row/a");

            /*     if($params['email']){

                     $count = $this->model->where(['email' => $params['email'], 'id' => ['neq', $row['id']]])->count();
                     if ($count > 0) {
                         $this->error(__('邮箱已经被占用'));
                     }
                 }*/
            $expiration_time = $this->model->where(['mobile' => $params['mobile'], 'id' => ['neq', $row['id']]])->value('expiration_time');
            if (!$expiration_time && !$params['vip_type'] ==1){
                $params['expiration_time']=0;
            }else{
                $now = date('Y-m-d H:i:s',time());
                $params['expiration_time']=strtotime("+1years",strtotime($now));
            }
            $count = $this->model->where(['mobile' => $params['mobile'], 'id' => ['neq', $row['id']]])->count();
            if ($count > 0) {
                $this->error(__('该手机号已经占用'));
            }
            if ($params) {
                try {
                    //是否采用模型验证
                    if ($this->modelValidate) {
                        $name = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                        $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.edit' : $name) : $this->modelValidate;
                        $row->validate($validate);
                    }
                    if($params['downloadurl']){
                        $params['downloadurl'] = renames($params['downloadurl'] , $params['newversion']);
                    }
                    $result = $row->allowField(true)->save($params);
                    if ($result !== false) {
                        $this->success();
                    } else {
                        $this->error($row->getError());
                    }
                } catch (\think\exception\PDOException $e) {
                    $this->error($e->getMessage());
                } catch (\think\Exception $e) {
                    $this->error($e->getMessage());
                }
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $this->view->assign('groupList', build_select('row[group_id]', \app\admin\model\UserGroup::column('id,name'), $row['group_id'], ['class' => 'form-control selectpicker']));
        $this->view->assign("row", $row);
        return $this->view->fetch();
//        return parent::edit($ids);
    }


    public function add()
    {
        if ($this->request->isPost()) {
            $params = $this->request->post("row/a");
            if ($params) {

                $count = $this->model->where(['email' => $params['email']])->count();
                if ($count > 0) {
                    $this->error(__('邮箱已经被占用'));
                }

                $count = $this->model->where(['mobile' => $params['mobile']])->count();
                if ($count > 0) {
                    $this->error(__('该手机号已经占用'));
                }


                try {
                    $params['status'] = 'normal';
                    $params['jointime'] = time();
                    $params['salt'] = Random::alpha();
                    $params['password'] = Auth::instance()->getEncryptPassword($params['password'], $params['salt']);
                    $result = $this->model->adduser($params);
                    if ($result !== false) {
                        $this->success();
                    } else {
                        $this->error($this->model->getError());
                    }
                } catch (\think\exception\PDOException $e) {
                    $this->error($e->getMessage());
                } catch (\think\Exception $e) {
                    $this->error($e->getMessage());
                }
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }
        return $this->view->fetch();
    }

    /**
     * 佣金明细
     * */
    public function moneylog($ids = NULL)
    {
        if ($this->request->isAjax()) {
            $filter = (array)json_decode($this->request->get("filter", ''), TRUE);

            $wheres['user_id'] = $filter['ids'];
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $member_log_model = model('MoneyLog');
            $result = admin_list($member_log_model, $where = [], $sort, $order, $offset, $limit, $wheres);
            return json($result);
        }
        $this->assignconfig('ids', $ids);
        return $this->view->fetch();
    }

    public function scorelog($ids = NULL)
    {
        if ($this->request->isAjax()) {
            $filter = (array)json_decode($this->request->get("filter", ''), TRUE);

            $wheres['user_id'] = $filter['ids'];
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $member_log_model = model('ScoreLog');
            $result = admin_list($member_log_model, $where = [], $sort, $order, $offset, $limit, $wheres);
            return json($result);
        }
        $this->assignconfig('ids', $ids);
        return $this->view->fetch();
    }

    /*
     * 导出
     */
    public function export()
    {
        $params = $this->request->request();

        $params['columns'] = str_replace("avatar,", "", $params['columns']);
        $columns = explode(',', $params['columns']);
        $params['ids'] != 'all' ? $where = ['id' => ['in', $params['ids']]] : $where = '';

        $result = $this->model->where($where)->order('id desc')->select();

        $filename = "用户数据";
        vendor('PHPExcel.PHPExcel');
        $objPHPExcel = new \PHPExcel();
        //设置保存版本格式
        $objWriter = new \PHPExcel_Writer_Excel5($objPHPExcel);

        //设置表头
        $array = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
        foreach ($columns as $k => $v) {
            $k > 25 ? $letter = 'A' . $array[$k - 26] : $letter = $array[$k];
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($letter . '1', __($v));
        }

        //改变此处设置的长度数值
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(20);

        //输出表格
        $myRow = 1;
        foreach ($result as $key => &$val) {
            $i = $key + 2;//表格是从2开始的
            $myRow += 1;

            //替换数据
            empty($val['createtime']) ? $pay_time = '' : $val['createtime'] = date('Y-m-d h:i:s', $val['createtime']);
            $val['status'] == 'normal' ? $val['status'] = __('normal') : $val['status'] = __('hidden');
            $val['distributor'] == '0' ? $val['distributor'] = __('distributor 0') : $val['distributor'] = __('distributor 2');
            $val['vip_type'] == '0' && $val['vip_type'] = '普通用户';
            $val['vip_type'] == '1' && $val['vip_type'] = '普通VIP';
            $val['vip_type'] == '2' && $val['vip_type'] = '尊享VIP';

            foreach ($columns as $k => $v) {
                $objPHPExcel->getActiveSheet()->setCellValue($array[$k] . $i, $val[$v]);
            }
        }

        //改变此处设置表格样式
        $objPHPExcel->getActiveSheet()->getStyle('A1:AZ' . $myRow)->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);/*垂直居中*/
        $objPHPExcel->setActiveSheetIndex(0)->getstyle('A1:Az' . $myRow)->getAlignment()->setHorizontal(\PHPExcel_style_Alignment::HORIZONTAL_CENTER);/*水平居中*/

        $objPHPExcel->getActiveSheet()->getStyle('A1:AZ1')->getFont()->setBold(true);//表头字体加粗
        $objPHPExcel->getActiveSheet()->getStyle('A1:AZ1')->getFont()->setName('微软雅黑');//表头改变字体

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

    public function transferUser()
    {
        $users = $this->request->param('users');
        $toUser = $this->request->param('toUser');
        $userArray = explode(',',$users);

        //转让数量
        $total = count($userArray);
        !$total && $this->error('抱歉，您并未选择用户');

        //判断所选用户 是否已经绑定上级
        foreach ($userArray as $user){
            $first_id = model('UserRebate')->where('uid',$user)->value('first_id');
            $first_info = $this->model->get($first_id);
            if ($first_id)
                $this->error('您所选的会员已经所属昵称为：'.$first_info->username.' '.'手机号为：'.$first_info->mobile.'代理商');
        }

        //被转让用户
        $toUserInfo = \app\common\model\User::get($toUser);

        //被转让用户邀请人数增加
        $toUserInfo->invite_num += $total;
        //分销关系增加
        $add_r = [
            'first_id' => $toUser,
            'add_time' => time(),
        ];

        try {
            Db::startTrans();
            foreach ($userArray as $user){
                $add_r['uid'] = $user;
                $userInfo = \app\common\model\User::get($user);//用户信息
                $userInfo->pid = $toUser;
                $userInfo->save();
                model('UserRebate')->insert($add_r);
            }
            $res = $toUserInfo->save();
            if ($res){
                Db::commit();
                $this->success('转让成功');
            }
        }catch (\think\exception\PDOException $e) {
        }
        Db::rollback();
        $this->error('转让失败');
    }

    public function moneyadd($ids = NULL){
        if ($this->request->isPost()) {
            $params = $this->request->post();
            //获取金额
            $money=$params['money'];
            if ($money < 1){
                $this->error('金额不能小于1元');
            }
            $memo='线下充值金额';
            \app\common\model\User::money($money, $ids, $memo, 70,0);
            $this->success('成功');
            }
        return $this->view->fetch();
    }
    public function qrcodeadd($ids = NULL){
        if ($this->request->isPost()) {
            $params = $this->request->post();
            //获取金额
            $qrcode=$params['qrcode'];
            if ($qrcode < 1){
                $this->error('积分不能小于1元');
            }
            $memo='充值添加积分';
            \app\common\model\User::score($qrcode, $ids, $memo, 1);
            $this->success('成功');
        }
        return $this->view->fetch();
    }



}
