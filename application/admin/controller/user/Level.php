<?php

namespace app\admin\controller\user;

use app\common\controller\Backend;
use app\common\model\UserLevel;

/**
 * 会员组管理
 *
 * @icon fa fa-circle-o
 */
class Level extends Backend
{
    
    /**
     * Level模型对象
     * @var \app\admin\model\user\Level
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\user\Level;
        $this->view->assign("statusList", $this->model->getStatusList());
    }
    
    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */
    public function edit($ids = NULL)
    {
        $row = $this->model->get($ids);
        if ($this->request->isPost()) {
            $params = $this->request->post("row/a");
            $row->name = $params['name'];
            $row->upgrade_price=$params['upgrade_price'];
            $row->status=$params['status'];
            if ($row->save()){
                $this->success('ok');
            }
            $this->error('no');
        }
        $this->view->assign("row", $row);
        return $this->view->fetch();
    }

    

}
