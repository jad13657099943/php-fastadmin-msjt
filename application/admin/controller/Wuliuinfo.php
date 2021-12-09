<?php

namespace app\admin\controller;

use app\common\controller\Backend;

use think\Controller;
use think\Request;

/**
 * 物流公司
 *
 * @icon fa fa-circle-o
 */
class Wuliuinfo extends Backend
{

    protected $model = null;
    protected $modelValidate = true;
    protected $modelSceneValidate = true;

    public function _initialize()
    {
        parent::_initialize();




    }

    public function selectpage()
    {
        return parent::selectpage();
    }
}
