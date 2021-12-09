<?php

namespace app\api\controller;

use app\common\controller\Api;
use think\Db;


class Bank extends Api
{
    protected $noNeedLogin = ['check_bank', 'bank_list'];
    protected $noNeedRight = ['*'];
    protected $rsa_key = 'ylyy';

    public function _initialize()
    {

        parent::_initialize();
        $this->bank = model('Bank');
        $this->bank_type = model('Banktype');
        $this->withdraw = model('Withdraw');
        $this->user = model('User');
        $this->accountlogs = model('Accountlogs');

    }

    /**
     * 用户添加银行卡
     * @param account 卡号
     * @param openbank 开户行
     * @parma realname 真实姓名
     * @param branchbank 开户支行
     */
    public function save_bank_card()
    {
        $params = $this->request->request();
        $params['uid'] = $this->auth->id;
        if (strlen($params['account']) < 16 || strlen($params['account']) > 19) {
            $this->error("银行卡号错误");
        }

        empty($params['openbank']) && $this->error('缺少开户行');
        empty($params['realname']) && $this->error('缺少持卡人');

        $data = [
            'uid' => $params['uid'],
            'realname' => $params['realname'],
            'account' => $params['account'],
            'openbank' => $params['openbank'],
            'branchbank' => $params['branchbank'],
        ];
        /*//验证银行卡信息
        $this->check_bank($params['realname'] ,$params['account'],$params['openbank']);*/

//        if (empty($params['bank_id'])) {
//            $res = $this->bank->add_data($data);
//        } else {
//            $res = $this->bank->edit_data(['id' => $params['bank_id']], $data);
//        }
        $res = $this->bank->add_data($data);
        if ($res) {
            $this->success('操作成功');
        } else {
            $this->error("操作失败");
        }
    }

    /*
      * 验证银行卡 真实项目
      *
      * */
    public function check_bank($name, $bank_cark, $openbank)
    {

        $host = "https://bank.market.alicloudapi.com";
        $path = "/bank2";
        $method = "GET";
        $appcode = "64918896db894c0aac133c2cb46e167e";
        $headers = array();
        array_push($headers, "Authorization:APPCODE " . $appcode);
        $querys = "acct_name=" . $name . "&acct_pan=" . $bank_cark . "&needBelongArea=true";
        $bodys = "";
        $url = $host . $path . "?" . $querys;
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_FAILONERROR, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, true);

        if (1 == strpos("$" . $host, "https://")) {
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        }
        $output = curl_exec($curl);
        curl_close($curl);

        !strstr($output, '认证通过') && $this->error('卡号与持卡人信息不匹配');
        !strstr($output, $openbank) && $this->error('卡号与开户行信息不匹配');
        return true;
    }

    /**
     * 用户删除银行卡
     */
    public function delete_bank_card()
    {
        $params = $this->request->request();
        $params['uid'] = $this->auth->id;
        empty($params['bank_id']) && $this->error('缺少银行卡id');

        $res = $this->bank->delete_data(['id' => $params['bank_id']]);
        if ($res) {
            $this->success('删除成功');
        } else {
            $this->error("删除失败");
        }
    }

    /*
     * 用户银行卡列表
     * @param account 卡号
     * @param openbank 开户行
     * @parma realname 真实姓名
     * @param branchbank 开户支行
     *
     */
    public function user_bank_list()
    {
        $params = $this->request->request();
        $params['uid'] = $this->auth->id;
//        !$params['uid'] && $this->error('用户信息不存在');
//        !$params['page'] && $this->error('page不存在');
//        !$params['pagesize'] && $this->error('pagesize不存在');

        $where = ['uid' => $params['uid']];
        $filed = "id,account,openbank,realname,branchbank";
        $bank_list = $this->bank->select_data($where, $filed, 'id desc');
        if ($bank_list) {
            foreach ($bank_list as $k => $v) {
                $bank_list[$k]['bankaccount'] = str_replace_bank_card($v['account']);
            }
            $this->success('获取成功', ['list' => $bank_list]);
        }
        $this->error('获取成功', new \ArrayObject());
    }

    /*
     * 提现列表
     */
    public function withdraw_list()
    {
        $params = $this->request->request();
        $params['uid'] = $this->auth->id;
        !$params['uid'] && $this->error('用户信息不存在');

        $where = ['uid' => $params['uid'], 'status' => 1, 'type' => 1];
        $field = "id,amount,add_time,desc";
        $list = $this->account_logs->get_page_data($where, $field, '', $params['page'], $params['pagesize']);
        $this->error('获取成功', ['list' => $list]);
    }

    /*
     * 银行列表
     * @param name 银行名称
     */
    public function bank_list()
    {
        $params = $this->request->request();
        $where = ['status' => 1];
        $field = "id,name";
        $bank_list = $this->bank_type->select_data($where, $field);
        if ($bank_list) {
            $this->success('获取成功', ['list' => $bank_list]);
        }
        $this->error('获取失败', new \ArrayObject());
    }


    public function apply_withdraw()
    {
        $type = $this->request->param('type');
        $money = $this->request->param('money');

        !$type && $this->error('type不能为空');
        !$money && $this->error('money不能为空');

        if($type == 1){
            config('site.wx_min_money') > $money && $this->error('提现金额必须大于:'.config('site.wx_min_money'));
        }else{
            config('site.min_money') > $money && $this->error('提现金额必须大于:'.config('site.min_money'));
        }


        $this->auth->balance < $money && $this->error('余额不足');

        $data = [
            'uid' => $this->auth->id,
            'type' => $type,
            'money' => number_format($money, 2),
            'order_sn' => order_sn(7)
        ];
        if ($type == 1) {//微信提现
            $name = $this->request->param('name');
            !$name && $this->error('name不能为空');
            $data['realname'] = $name;
        } elseif ($type == 2) {//银行卡提现
            $bankId = $this->request->param('bank_id');
            !$bankId && $this->error('bank_id不能为空');
            $bankInfo = \app\common\model\Bank::get($bankId)->toArray();
            (!$bankInfo || $bankInfo['uid'] != $this->auth->id) && $this->error('bank_id有误');

            unset($bankInfo['id'], $bankInfo['uid']);
            $data = array_merge($data, $bankInfo);
        }
        Db::startTrans();
        if ($this->withdraw->save($data) && \app\common\model\User::balance(-$money, $this->auth->id)) {
            Db::commit();
            $this->success('申请提现成功');
        }
        Db::rollback();
        $this->error('申请提现失败');
    }

    /*
     * 申请提现
     *
     */
    /*public function apply_withdraw()
    {
        $params = $this->request->request();
        $params['bank_id'] = $this->request->request('bank_id');
        $params['pay_password'] = $this->request->request('pay_password');
        $params['money'] = $this->request->request('money');
        $params['uid'] = $this->auth->id;

        empty($params['money']) && $this->error('缺少提现金额');
        empty($params['type']) && $this->error('请选择提现方式');

        if ($params['type'] == 1) {
            //微信提现
            empty($params['wx_account']) && $this->error('请填写微信账户');
        } elseif ($params['type'] == 2) {
            //银行卡提现
            $bank_card_info = $this->bank->find_data(['id' => $params['bank_id']]);
            !$bank_card_info && $this->error('银行卡信息不存在');
        }

        $user_pay_password = $this->user->getUserInfo(['id' => $params['uid']], 'mobile,pay_password,balance');


        //校验支付密码
        if (empty($params['pay_password'])) {
            $this->error('请输入支付密码');
        }

        if (empty($user_pay_password['pay_password'])) {
            $this->error('未设置支付密码');
        }

        if ($user_pay_password['pay_password'] != $params['pay_password']) {
            $this->error('支付密码错误');
        }

        if ($user_pay_password['balance'] < $params['money']) {
            $this->error("余额不足");
        }
        //获取最小提现余额
        $min_withdraw = model('config')->where(['name' => 'min_withdraw'])->value('value');
        if ($min_withdraw > $params['money']) {
            $this->error('未到达最小提现额');
        }
        $this->withdraw->startTrans();
        try {
            $data = [
                'uid' => $params['uid'],
                'user_mobile' => $user_pay_password['mobile'],
                'type' => $params['type'],
                'wx_account' => $params['wx_account'],
                'realname' => $bank_card_info['realname'],
                'account' => $bank_card_info['account'],
                'openbank' => $bank_card_info['openbank'],
                'branchbank' => $bank_card_info['branchbank'],
                'add_time' => time(),
                'money' => round($params['money'], 2),
                'status' => 1,
                'order_sn' => order_sn(7)
            ];
            $res = $this->withdraw->add_data($data);
            if (!$res) {
                $this->withdraw->rollback();
                $this->error("申请失败");
            }
//            $result = $this->user->where(['id' => $params['uid']])->setDec('balance', round($params['money'], 2));
            //提现记录
            $account['add_time'] = time();
            $account['desc'] = "提现申请";
            $account['type'] = 1;
            $account['uid'] = $params['uid'];
            $account['status'] = 1;
            $account['amount'] = round($params['money'], 2);
            $account['order_sn'] = $data['order_sn'];
            $account['zf_type'] = 1;
            $es = $this->accountlogs->add_data($account);
            if (!$es) {
                $this->accountlogs->rollback();
                $this->error('提现记录生成失败');
            }
            //余额日志
            $es_money = $this->user->balance(-(round($params['money'], 2)), $params['uid'], '提现佣金', '10');
            if (!$es_money) {
                $this->user->rollback();
                $this->error('余额日志记录失败');
            }
            $this->withdraw->commit();
            $this->success('申请成功');
        } catch (Exception $exception) {
            $this->withdraw->rollback();
            $this->error("申请失败" . $exception->getMessage());
        }
    }*/

    /*
     * 提现页面信息
     * @param min_withdraw 最少提现额
     * @param account 卡号
     * @param openbank 开户行
     */
    public function withdraw_info()
    {
        $params['uid'] = $this->auth->id;
//        !$params['uid'] && $this->error('用户信息不存在');

//        $bank_info = $this->bank->find_data(['uid' => $params['uid']], 'id,account,openbank');
//        //获取卡号后四位
//        if ($bank_info['account']) {
//            $bank_info['account'] = substr($bank_info['account'], -4);
//
//        }
//        $balance = $this->user->getField(['id' => $params['uid']], 'balance');
        $user_info = [
            'uid' => $this->auth->id,
            'money' => $this->auth->money,
            'balance' => $this->auth->balance,
        ];
        //获取最小提现额
        $min_withdraw = config('site.min_money');
        $wx_min_withdraw = config('site.wx_min_money');
        $balance = empty($balance) ? 0 : $balance;
//        $this->success('获取成功', ['bank_info' => $bank_info, 'balance' => $balance, 'min_withdraw' => $min_withdraw]);
        $this->success('获取成功', ['user_info' => $user_info, 'min_withdraw' => $min_withdraw, 'wx_min_withdraw' => $wx_min_withdraw]);
    }


}