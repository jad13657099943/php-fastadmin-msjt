<?php

namespace app\admin\controller\coupon;

use app\common\controller\Backend;

/**
 *  优惠券管理
 *
 * @icon fa fa-user
 */
class Coupon extends Backend
{

    protected $relationSearch = false;


    /**11
     * @var \app\admin\model\User
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('Coupon');
        $this->view->assign("statusList", $this->model->getStatusList());
    }
}
