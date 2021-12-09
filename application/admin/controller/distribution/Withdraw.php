<?php

namespace app\admin\controller\distribution;

use addons\epay\library\Service;
use app\common\controller\Backend;
use app\common\model\MoneyLog;
use fast\Http;
use fast\Random;
use think\Exception;

/**
 * 提现管理
 *
 * @icon fa fa-circle-o
 */
class Withdraw extends Backend
{

    /**
     * Withdraw模型对象
     * @var \app\admin\model\Withdraw
     */
    protected $model = null;
    private $config = [];
    protected $noNeedLogin = ['getTransferInfo'];

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Withdraw;
        $this->config = Service::getConfig('wechat')['wechat'];
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
                ->field('id,realname,money,type,status,create_time')
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

    public function edit($ids = NULL)
    {
        $row = $this->model->get($ids);
        if ($this->request->isPost()) {
            $params = $this->request->post("row/a");
            if ($params) {
                if ($params['status'] == 2 || $params['status'] == 3)
                    $params['over_time'] = time();
                switch ($params['status']) {
                    case 2://通过时
                        if ($row->status == 1 && $row->type == 2) {
                            $params['status'] = 4;
                        }
                        break;
                    case 3://拒绝时
                        $params['refuse_reason'] == '' && $this->error('请填写拒绝理由');
                        \app\common\model\User::balance($row->money, $row->uid);
                        break;
                    case 4://已经通过审核，确认转款时
                        $params['status'] = 5;
                        $params['arrive_time'] = time();
                        break;
                    default:
                        $this->error('请选择审核状态');
                        break;
                }

                try {
                    $result = $row->allowField(true)->save($params);
                    if ($result !== false) {
                        if ($row->type == 1 && $params['status'] == 2) {
                            $this->transfers($row);
                        }
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
        }
        $this->view->assign("row", $row);
        return $this->view->fetch();
    }

    /**
     * 企业付款到微信
     * @param $row
     * @throws \think\exception\DbException
     */
    public function transfers($row)
    {
        $url = "https://api.mch.weixin.qq.com/mmpaymkttransfers/promotion/transfers";
        $user = \app\common\model\User::get($row->uid);
        $params = [
            'mch_appid' => $this->config['miniapp_id'],
            'mchid' => $this->config['mch_id'],
            'nonce_str' => Random::numeric(10),
            'partner_trade_no' => $row->order_sn,
            'openid' => $user->wxopenid,
            'check_name' => 'FORCE_CHECK',
            're_user_name' => $row->realname,
            'desc' => '国赣臻品分销佣金',
            'amount' => $row->money * 100,
            'spbill_create_ip' => $this->request->ip(),
        ];

        //签名
        $params['sign'] = $this->sign($params);

        $result = $this->post($url, $params);
        //请求成功时
        if (isset($result['return_code']) && $result['return_code'] == 'SUCCESS') {

            //判断付款状态
            switch ($result['result_code']) {
                case 'SUCCESS'://付款成功
                    $row->payment_no = $result['payment_no'];
                    $row->arrive_time = strtotime($result['payment_time']);
                    $row->status = 5;
                    break;

                //需要查看支付状态
                case 'SEND_FAILED'://付款错误
                case 'SYSTEMERROR'://系统繁忙，请稍后再试。
                    $row->status = 7;
                    break;

                default://支付失败情况
                    $row->status = 6;
                    $row->error_msg = $result['err_code_des'];
                    \app\common\model\User::balance($row->money, $row->uid);
                    break;
            }
        } else {
            //请求失败时，订单状态更改为异常
            $row->status = 7;
        }
        $row->save();
    }

    /**
     * 定时任务处理异常订单
     */
    public function getTransferInfo()
    {
        $url = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/gettransferinfo';
        $params = [
            'mch_id' => $this->config['mch_id'],
            'appid' => $this->config['miniapp_id'],
        ];

        $list = $this->model->where(['status' => 7])->select();
        !$list && die;

        foreach ($list as $k => $item) {
            $params['partner_trade_no'] = $item->order_sn;
            $params['nonce_str'] = Random::numeric(10);
            $params['sign'] = $this->sign($params);
            $result = $this->post($url, $params);
            if ($result['return_code'] == 'SUCCESS') {
                if ($result['result_code'] == 'SUCCESS') {
                    switch ($result['status']) {
                        case 'SUCCESS'://成功
                            $item->status = 5;
                            $item->arrive_time = strtotime($result['payment_time']);
                            $item->payment_time = $result['detail_id'];
                            $item->save();
                            break;
                        case 'FAILED'://失败
                            $item->status = 6;
                            $item->error_msg = $result['reason'];
                            \app\common\model\User::balance($item->money, $item->uid);
                            $item->save();
                            break;
                    }
                } elseif ($result['result_code'] == 'FAIL' && $result['err_code'] == 'NOT_FOUND') {
                    $item->status = 6;
                    $item->error_msg = $result['err_code_des'];
                    \app\common\model\User::balance($item->money, $item->uid);
                    $item->save();
                }
            }
        }
    }


    protected function post($url, $params)
    {
        //请求证书
        $options = [
            CURLOPT_SSLCERTTYPE => 'PEM',
            CURLOPT_SSLCERT => $this->config['cert_client'],
            CURLOPT_SSLKEYTYPE => 'PEM',
            CURLOPT_SSLKEY => $this->config['cert_key'],
        ];

        //发送企业付款请求
        return $this->fromXml(Http::post($url, $this->toXml($params), $options));
    }


    /**
     * 签名
     * @param $data
     * @param $key
     * @return string
     */
    private function sign($data)
    {
        ksort($data);
        $sign = '';
        foreach ($data as $k => $value) {
            $sign .= "$k=$value&";
        }
        $sign .= "key=" . $this->config['key'];
        return strtoupper(md5($sign));
    }

    protected function toXml($data)
    {
        $xml = '<xml>';
        foreach ($data as $key => $val) {
            $xml .= is_numeric($val) ? '<' . $key . '>' . $val . '</' . $key . '>' :
                '<' . $key . '><![CDATA[' . $val . ']]></' . $key . '>';
        }
        $xml .= '</xml>';

        return $xml;
    }

    protected function fromXml($xml)
    {
        return json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA), JSON_UNESCAPED_UNICODE), true);
    }
}
