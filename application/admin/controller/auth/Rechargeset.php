<?php

namespace app\admin\controller\auth;

use app\common\controller\Backend;

use think\Controller;
use think\Request;

/**
 * 充值金额管理
 *
 * @icon fa fa-circle-o
 */
class Rechargeset extends Backend
{
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('Rechargeset');
    }
}