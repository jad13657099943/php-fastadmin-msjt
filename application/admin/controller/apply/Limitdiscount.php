<?php

namespace app\admin\controller\apply;

use app\common\controller\Backend;
use  app\admin\controller\litestore\Litestoregoods;
use fast\Tree;


/**
 * 限时折扣
 *
 * @icon fa fa-users
 * @remark 一个管理员可以有多个角色组,左侧的菜单根据管理员所拥有的权限进行生成
 */
class Limitdiscount extends Backend
{
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('Limitdiscount');
        $this->CateModel = model('Litestorecategory');
        $this->limit_disocunt_goods_model = model('Limitdiscountgoods');
        $tree = Tree::instance();
        $tree->init(collection($this->CateModel->order('weigh desc,id desc')->select())->toArray(), 'pid');
        $this->categorylist = $tree->getTreeList($tree->getTreeArray(0), 'name');
        $categorydata = [0 => ['type' => 'all', 'name' => __('None')]];
        foreach ($this->categorylist as $k => $v) {
            $categorydata[$v['id']] = $v;
        }
        $this->view->assign("parentList", $categorydata);
    }

    public function index()
    {
        if ($this->request->isAjax()) {
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $total = $this->model
                ->where($where)
                ->order($sort, $order)
                ->count();
//            $list = $this->model->getLimitDiscountList($where, $sort, $order, $offset, $limit);
            $list = $this->model
                ->where($where)
                ->field('id,title,start_time,end_time,status,upper_num')
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();
            $result = array("total" => $total, "rows" => $list);

            return json($result);
        }
        return $this->view->fetch();
    }

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
                    $result = $this->model->addLimitDiscount($params);
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
     * 编辑限时活动
     */
    public function edit($ids = NULL)
    {
        $row = $this->model->get($ids);
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
            if ($params) {
                try {
                    //是否采用模型验证
                    if ($this->modelValidate) {
                        $name = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                        $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.edit' : $name) : $this->modelValidate;
                        $row->validate($validate);
                    }
                    $params['start_time'] = strtotime($params['start_time']);
                    $params['end_time'] = strtotime($params['end_time']);
                    $params['status'] = $params['end_time'] < time() ? 0 : $params['status'];
                    $result = $this->model->editLimitDiscount($params, ['id' => $params['id']]);

                    if ($result !== false) {
                        //修改活动商品信息
                        $limit_discount_id = $params['id'];
                        unset($params['id'] , $params['title']);
                        $params['status'] = $params['status'] == 0 || $params['end_time'] < time() ? 20 :10;
                        $this->limit_disocunt_goods_model->editLimitDiscountGoods($params ,['limit_discount_id' => $limit_discount_id]);

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


    /**
     * 限时活动已加入商品管理
     */
    public function manage($ids = NULL)
    {
        $this->assignconfig('limit_discount_id', $this->request->param('ids'));
        if ($this->request->isAjax()) {
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $wherea['limit_discount_id'] = $this->request->param('limit_discount_id');
//            $wheres['is_new']=['neq',2];
            $total = $this->limit_disocunt_goods_model
                ->where($where)
                ->where($wherea)
//                ->where($wheres)
                ->order($sort, $order)
                ->count();
            $list = $this->limit_disocunt_goods_model->getLimitDiscountGoodsList($where, $wherea, $sort, $order, $offset, $limit);
            $result = array("total" => $total, "rows" => $list);
            return json($result);
        }
        $this->view->assign("ids", $ids);
        return $this->view->fetch();
    }


    /**
     * 限时活动全部商品管理
     */
    public function goodslist($ids = NULL)
    {
        $this->litestoregoods_model = model('Litestoregoods');
        $this->assignconfig('limit_discount_id', $this->request->param('ids'));
        if ($this->request->isAjax()) {
            $model = new Litestoregoods();

            return $model->index(1);
        }
        $this->view->assign("ids", $ids);
        return $this->view->fetch();
    }



    /**
     * 添加商品
     */
    public function goods_add($ids = NULL)
    {

        $litestore_goods_model = model('Litestoregoods');
        $ids = $this->request->param('goods_id');
        $row = $litestore_goods_model->get($ids);
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
            $limit_discount_id = $this->request->param('limit_discount_id');
            if ($params) {
                try {

                    //判断是否已经参加过活动
                    $map = ['limit_discount_id' => $limit_discount_id ,'goods_id' => $ids];

                    $check_linmit_disocunt = $this->limit_disocunt_goods_model->find_data($map,'id');
                    if($check_linmit_disocunt)
                        $this->error('不能重复参加活动');


                    $row = $row->getData();
                    $info = $this->model->getLimitDiscountInfoByID($limit_discount_id ,'start_time,end_time,upper_num');
                    $params['start_time'] = $info['start_time'];
                    $params['end_time'] = $info['end_time'];
                    $params['upper_num'] = $info['upper_num'];
                    $params = array_merge($params,$row);
                    $params['status'] = 1;
                    $result = $this->limit_disocunt_goods_model->allowField(true)->save($params);
                    if ($result !== false) {
                        $litestore_goods_model->save(['is_marketing'=>1] ,['goods_id' => $ids]);

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
        $this->view->assign("limit_discount_id", $this->request->param('limit_discount_id'));
        $this->view->assign("goods_id", $this->request->param('goods_id'));
        $this->view->assign("row", $row);
        return $this->view->fetch();
    }



    /**
     * 限时活动产品删除
     */
    public function limit_goods_del($ids = "")
    {
        if ($ids) {
            $pk = $this->limit_disocunt_goods_model->getPk();
            $adminIds = $this->getDataLimitAdminIds();
            if (is_array($adminIds)) {
                $this->limit_disocunt_goods_model->where($this->dataLimitField, 'in', $adminIds);
            }
            $where['id'] = ['in', $ids];
            $count = $this->del_marketing($where);
            if ($count) {
                $this->success();
            } else {
                $this->error(__('No rows were deleted'));
            }
        }
        $this->error(__('Parameter %s can not be empty', 'ids'));
    }

    /**
     * 限时活动产品编辑
     */

    public function limit_goods_edit($ids)
    {
        $row = $this->limit_disocunt_goods_model->get($ids);
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
            if ($params) {
                try {
                    //是否采用模型验证
                    if ($this->modelValidate) {
                        $name = str_replace("\\model\\", "\\validate\\", get_class($this->limit_disocunt_goods_model));
                        $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.edit' : $name) : $this->modelValidate;
                        $row->validate($validate);
                    }
                    $result = $this->limit_disocunt_goods_model->editLimitDiscountGoods($params, ['id' => $params['id']]);
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


    /**
     * 删除
     */
    public function del($ids = "")
    {
        if ($ids) {
            $pk = $this->model->getPk();
            $adminIds = $this->getDataLimitAdminIds();
            if (is_array($adminIds)) {
                $this->model->where($this->dataLimitField, 'in', $adminIds);
            }
            $list = $this->model->where($pk, 'in', $ids)->field('id')->select();
            $count = 0;
            foreach ($list as $k => $v) {
                //营销商品的删除
                $where['limit_discount_id'] = $v['id'];
                $this->del_marketing($where);
                $count += $v->delete();
            }
            if ($count) {
                $this->success();
            } else {
                $this->error(__('No rows were deleted'));
            }
        }
        $this->error(__('Parameter %s can not be empty', 'ids'));
    }


    /**
     * 修改商品限购数量
     * @param $ids
     */
    public function edit_upper_num($ids){
        $row = $this->limit_disocunt_goods_model->get($ids);
        $params = $this->request->param("params");
        $row['upper_num'] = $params;
        $result = $row->save();
        if ($result !== false) {
            $this->success();
        } else {
            $this->error($row->getError());
        }
    }


    //删除营销活动
    public function del_marketing($where){
        //清除营销标记
        $goods_ids = $this->limit_disocunt_goods_model->where($where)->column('goods_id');
        model('Litestoregoods')->save(['is_marketing' => 0 ,'marketing_id' => 0] , ['goods_id'=> ['in' , $goods_ids]]);
        model('Shopingcart')->where(['goods_id'=> ['in' , $goods_ids] ,'type' => 2])->delete();
        //删除活动列表
        return $this->limit_disocunt_goods_model->where($where)->delete();
    }


}
