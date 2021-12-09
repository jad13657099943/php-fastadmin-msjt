<?php

namespace app\admin\controller\member;

use app\common\controller\Backend;

/**
 * 会员管理
 *
 * @icon fa fa-user
 */
class Member extends Backend
{

    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('Member');
    }
}
