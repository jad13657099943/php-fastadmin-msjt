<?php

namespace app\admin\controller\cms;

use app\common\controller\Backend;

/**
 * 会员管理
 *
 * @icon fa fa-user
 */
class Adcate extends Backend
{

    protected $relationSearch = false;


    /**
     * @var \app\admin\model\User
     */
    protected $model = null;



    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\cms\Adcate;
        $this->view->assign("statusList", $this->model->getStatusList());
    }

}
