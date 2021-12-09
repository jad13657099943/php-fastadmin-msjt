<?php


namespace app\api\services\msjt\withdraw;


use app\api\model\msjt\Balance;
use app\api\model\msjt\Config;
use app\api\model\msjt\Users;
use app\api\model\msjt\Withdraw;
use app\api\services\PublicService;
use think\Db;

class WithdrawService extends PublicService
{
    private $uid;
    private $type;
    private $money;
    private $info;

    public function __construct($uid, $type, $money, $info)
    {
        $this->uid = $uid;
        $this->type = $type;
        $this->money = $money;
        $this->info = $info;
    }

    /**
     * 验证提现方式
     * @throws \think\Exception
     */
    public function checkType()
    {
        if (!in_array($this->type, [1, 2])) $this->error('提现方式异常');
    }

    /**
     * 验证是否正整数
     * @throws \think\Exception
     */
    public function checkInteger()
    {
        if ($this->money <= 0) $this->error('提现金额必须为正整数');
    }


    /**
     * 验证提现配置
     * @throws \think\Exception
     */
    public function checkConfig()
    {
        switch ($this->type) {
            case 1:
                if ($this->money < $this->Config('wx_min_money')) $this->error('小于最低提现');
                break;
            case 2:
                if ($this->money < $this->Config('min_money')) $this->error('小于最低提现');
                break;
        }
    }

    /**
     * 获取提现配置
     * @param $name
     * @return float|mixed|string
     */
    public function Config($name)
    {
        return Config::whereValue(['name' => $name], 'value');
    }

    public function checkBalance()
    {
        if ($this->money > $this->getBalance()) $this->error('余额不足');
    }

    /**
     * 获取余额
     * @return float|mixed|string
     */
    public function getBalance()
    {
        return Users::whereValue(['id' => $this->uid], 'balance');
    }

    /**
     * 减少余额并记录
     * @throws \think\Exception
     */
    public function sub()
    {
        Users::whereSetDec(['id' => $this->uid], 'balance', $this->money);
        Balance::whereInsert([
            'user_id' => $this->uid,
            'name' => '提现',
            'money' => '-' . $this->money,
        ]);
    }

    /**
     * 记录提现申请
     */
    public function add()
    {
        $status = Withdraw::whereInsert([
            'user_id' => $this->uid,
            'type' => $this->type,
            'info' => $this->info,
            'money' => $this->money
        ]);
        return $this->statusReturn($status, '申请提交成功', '申请提交失败');
    }

    /**
     * 提交
     * @return mixed
     */
    public function submit()
    {
        return Db::transaction(function () {
            $this->checkType();
            $this->checkInteger();
            $this->checkConfig();
            $this->checkBalance();
            $this->sub();
            return $this->add();
        });
    }
}