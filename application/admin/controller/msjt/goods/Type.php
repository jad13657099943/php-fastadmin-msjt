<?php

namespace app\admin\controller\msjt\goods;

use app\common\controller\Backend;
use fast\Tree;
use think\Db;
use think\exception\PDOException;
use think\exception\ValidateException;

/**
 * 商品分类
 *
 * @icon fa fa-circle-o
 */
class Type extends Backend
{
    
    /**
     * Type模型对象
     * @var \app\admin\model\msjt\goods\Type
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\msjt\goods\Type;
        $this->view->assign("isRecommendDataList", $this->model->getIsRecommendDataList());
        $this->view->assign("statusList", $this->model->getStatusList());
    }
    
    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */

    /**
     * 查看
     */
    public function index()
    {
        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $total = $this->model
                ->where($where)
                ->order($sort, $order)
                ->count();

            $list = $this->model
                ->where($where)
                ->order($sort, $order)
              //  ->limit($offset, $limit)
                ->select();

            $list = collection($list)->toArray();

            Tree::instance()->init($list);
            $rulelist = Tree::instance()->getTreeList(Tree::instance()->getTreeArray(0), 'name');
            $ruledata = [0 => __('无')];
            foreach ($rulelist as $k => &$v) {
                $ruledata[$v['id']] = $v['name'];
            }
            unset($v);
            $result = array("rows" => $rulelist, "total" => count($rulelist));
            return json($result);
        }
        return $this->view->fetch();
    }




    /**
     * 所属分类
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function typeList(){
        $keyValue = $this->request->request("keyValue");
        $where = [];
        if ($keyValue) $where['id'] = $keyValue;
        $list =collection($this->model->distinct('name')->where($where)->field('id,name,pid')->select())->toArray();
        Tree::instance()->init($list);
        $rulelist = Tree::instance()->getTreeList(Tree::instance()->getTreeArray(0), 'name');
        $ruledata = [0 => __('无')];
        foreach ($rulelist as $k => &$v) {
            $ruledata[$v['id']] = $v['name'];
        }
        unset($v);
        $result = array("rows" => $rulelist, "total" => count($rulelist));
        return json($result);
    }

    public function typeLists(){
        $list =collection($this->model->distinct('name')->field('id,name,pid')->select())->toArray();
        Tree::instance()->init($list);
        $rulelist = Tree::instance()->getTreeList(Tree::instance()->getTreeArray(0), 'name');
        $ruledata = [0 => __('无')];
        foreach ($rulelist as $k => &$v) {
            $ruledata[$v['id']] = $v['name'];
        }
        unset($v);
        return json($rulelist);
    }
}
