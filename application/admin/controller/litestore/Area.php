<?php

namespace app\admin\controller\litestore;

use app\common\controller\Backend;
use fast\Tree;
use think\Db;

/**
 * 地区管理
 *
 * @icon fa fa-circle-o
 */
class Area extends Backend
{

    /**
     * Area模型对象
     * @var \app\admin\model\litestore\Area
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\litestore\Area;

        $tree = Tree::instance();
        $tree->nbsp = "       ";
        $tree->init(collection($this->model->order('weigh desc')->select())->toArray(), 'pid');
        $this->area = $tree->getTreeList($tree->getTreeArray(0), 'name');

    }

    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */
    public function index()
    {
        if ($this->request->isAjax()) {
            $result = array("total" => count($this->area), "rows" => $this->area);
            return json($result);
        }
        return $this->view->fetch();
    }

    /**
     * 添加
     * @return string
     * @throws \think\Exception
     */
    public function add()
    {
        if ($this->request->isPost()) {
            $params = $this->request->post("row/a");
            if ($params['city_id']) {
                $params['pid'] = $params['city_id'];
                $params['level'] = 3;
                unset($params['city_id']);
            } elseif ($params['province_id']) {
                $params['pid'] = $params['province_id'];
                $params['level'] = 2;
                unset($params['province_id']);
            }

            if ($params) {
                if ($this->dataLimit && $this->dataLimitFieldAutoFill) {
                    $params[$this->dataLimitField] = $this->auth->id;
                }
                try {
                    //是否采用模型验证
                    if ($this->modelValidate) {
                        $name = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                        $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.add' : $name) : $this->modelValidate;
                        $this->model->validate($validate);
                    }
                    $result = $this->model->allowField(true)->save($params);
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

    /**
     * 编辑
     * @param null $ids
     * @return string
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function edit($ids = NULL)
    {
        $row = $this->model->get($ids);

        //查看上级是否是省
        $ppid = Db::name('area')->where(['id' => $row['pid']])->field('pid')->find();

        if ($ppid['pid']) {
            $row['province_id'] = $ppid['pid'];
            $row['city_id'] = $row['pid'];
        } else {
            $row['province_id'] = $row['pid'];
            $row['city_id'] = '';
        }

        if (!$row)
            $this->error(__('No Results were found'));
        $adminIds = $this->getDataLimitAdminIds();
        if (is_array($adminIds)) {
            if (!in_array($row[$this->dataLimitField], $adminIds)) {
                $this->error(__('You have no permission'));
            }
        }
        if ($this->request->isPost()) {
            $params = $this->request->post("row/a");

            if ($params['city_id']) {
                $params['pid'] = $params['city_id'];
                $params['level'] = 3;
                unset($params['city_id']);
            } elseif ($params['province_id']) {
                $params['pid'] = $params['province_id'];
                $params['level'] = 2;
                unset($params['province_id']);
            }

            if ($params) {
                try {
                    //是否采用模型验证
                    if ($this->modelValidate) {
                        $name = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                        $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.edit' : $name) : $this->modelValidate;
                        $row->validate($validate);
                    }
                    $result = $row->allowField(true)->save($params);
                    if ($result !== false) {
                        $this->success();
                    } else {
                        $this->error($row->getError());
                    }
                } catch (\think\exception\PDOException $e) {
                    $this->error($e->getMessage());
                } catch (\think\Exception $e) {
                    $this->error($e->getMessage());
                }
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $this->view->assign("row", $row);
        return $this->view->fetch();
    }


}
