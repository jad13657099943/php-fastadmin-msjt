<?php

namespace app\admin\controller;

use app\common\controller\Backend;

/**
 * 
 *
 * @icon fa fa-circle-o
 */
class Limituser extends Backend
{
    
    /**
     * Limituser模型对象
     * @var \app\admin\model\Limituser
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Limituser;
        $goods_id = $this->request->param('ids');
        if ($goods_id) {
            $_SESSION['goods_id'] = $goods_id;
        } else {
            $goods_id = $_SESSION['goods_id'];
        }
        $this->assign('goods_id', $goods_id);

    }
    
    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */

    /**
     * 添加限时活动
     */
    public function add()
    {
        if ($this->request->isPost()) {
            $params = $this->request->post("row/a");
            if ($params) {
                if ($this->dataLimit && $this->dataLimitFieldAutoFill) {
                    $params[$this->dataLimitField] = $this->auth->id;
                }

                try {
                    //判断手机号码是否存在
                    $where = ['mobile' => $params['mobile'] , 'goods_id' =>$params['goods_id']];
                    $check_mobile = $this->model->find_data($where);
                    if($check_mobile){
                        $this->error('此号码已经限制');
                    }

                   if(!$user_info = model('User')->find_data(['mobile' => $params['mobile']] ,'id')){
                       $this->error('此号码还不是会员');
                   }
                   $params['uid'] = $user_info['id'];

                    $result = $this->model->insert($params);
                    if ($result !== false) {
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
            $this->error(__('Parameter %s can not be empty', ''));
        }
        return $this->view->fetch();
    }

}
