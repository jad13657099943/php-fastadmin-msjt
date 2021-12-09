<?php

namespace app\admin\controller\distribution;

use app\common\controller\Backend;

class SetWithdrawMoney extends Backend
{
    public function _initialize()
    {
        return parent::_initialize();
    }

    public function index()
    {
        if ($this->request->isPost()) {
            $params = $this->request->request('row/a');
            controller('app\admin\controller\general\Config')->edit();
            if (setConfig(['min_money'], array_values($params))) {
                $this->success('保存成功');
            }
            $this->error('保存失败');
        }
        return $this->fetch();
    }
}