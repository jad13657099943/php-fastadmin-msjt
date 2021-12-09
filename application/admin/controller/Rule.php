<?php

namespace app\admin\controller\general;

use app\common\controller\Backend;
/**
 * 会员管理
 *
 * @icon fa fa-user
 */
class Rule extends Backend
{

    protected $relationSearch = true;

    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('Cmsarchives');
    }

    public function rule()
    {
        $id = input('id');
        $info = $this->article->where(['id' => $id])->field('id,content')->find();

        $this->view->assign("info", $info);
        return $this->fetch();
    }
}