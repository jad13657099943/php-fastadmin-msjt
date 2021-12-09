<?php

namespace app\admin\controller\apply;

use app\common\controller\Backend;
use  app\admin\controller\litestore\Litestoregoods;

/**
 * 拼团管理
 *
 * @icon fa fa-users
 * @remark 一个管理员可以有多个角色组,左侧的菜单根据管理员所拥有的权限进行生成
 */
class Groupbuy extends Backend
{
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('Groupbuy');
        $this->groupbuy_goods_model = model('Groupbuygoods');
        $this->groupbuy_goods_record_model = model('Groupbuygoodsrecord');
        // $this->model_litestore_goods = model('Litestoregoods');
        $this->assignconfig('images_domain', config('site.images_domain'));
    }

    public function index()
    {
        if ($this->request->isAjax()) {
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $total = $this->model
                ->where($where)
                ->order($sort, $order)
                ->count();
            $list = $this->model->getGroupbuyList($where, $sort, $order, $offset, $limit);

            $result = array("total" => $total, "rows" => $list);

            return json($result);
        }
        return $this->view->fetch();
    }

    /**
     * 添加拼团活动
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
                    $result = $this->model->addGroupbuy($params);
                    if ($result !== false) {
                        $params['groupbuy_id'] = $result;
                        //添加到商品记录表
                       // $this->groupbuy_goods_model->addGroupbuyGoods($params);
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
     * 编辑拼团活动
     *http://df0234.com:8081/?appId=newab2019040604
     */
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

                    $result = $this->model->editLimitDiscount($params, ['id' => $params['id']]);
                    if ($result !== false) {
                        $groupbuy_id = $params['id'];
                        unset($params['id'] , $params['title']);
                        $params['status'] = $params['status'] == 0 ? 20 :10;
                        $this->groupbuy_goods_model->addGroupbuyGoods($params ,['groupbuy_id' => $groupbuy_id]);

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
     * 拼团活动已加入商品管理
     */
    public function manage($ids = NULL)
    {
        $this->assignconfig('groupbuy_id', $this->request->param('ids'));
        if ($this->request->isAjax()) {
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $wherea['groupbuy_id'] = $this->request->param('groupbuy_id');
            $total = $this->groupbuy_goods_model
                        ->where($wherea)
                        ->order($sort, $order)
                        ->count();

            $list = $this->groupbuy_goods_model->getGroupbuyList($wherea, $sort, $order, $offset, $limit);
            $result = array("total" => $total, "rows" => $list);
            return json($result);
        }
        $this->view->assign("ids", $ids);
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
                $where['groupbuy_id'] = $v['id'];
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


    //删除营销活动
    public function del_marketing($where){
        //清除营销标记
        $goods_ids = $this->groupbuy_goods_model->where($where)->column('goods_id');
        model('Litestoregoods')->save(['is_marketing' => 0 ,'marketing_id' => 0] , ['goods_id'=> ['in' , $goods_ids]]);
        //删除活动列表
        return $this->groupbuy_goods_model->where($where)->delete();
    }


    /**
     * 限时活动全部商品管理
     */
    public function goodslist($ids = NULL)
    {
        $this->assignconfig('groupbuy_id', $this->request->param('ids'));
        if ($this->request->isAjax()) {

            $model = new Litestoregoods();
            return $model->index();
        }
        $this->view->assign("ids", $ids);
        return $this->view->fetch();
    }

    /**
     * 添加团购活动产品
     */
    public function goods_add($ids = NULL)
    {

        $litestore_goods_model = model('Litestoregoods');
        $ids = $this->request->param('goods_id');
        $row = $litestore_goods_model->get($ids);

        if (!$row)
            $this->error(__('No Results were found'));

        if ($this->request->isPost()) {
            $params = $this->request->post("row/a");
            $groupbuy_id = $this->request->param('groupbuy_id');
            try {

                //判断是否已经参加过活动
                $map = ['groupbuy_id' => $groupbuy_id, 'goods_id' => $ids];

                $check_linmit_disocunt = $this->groupbuy_goods_model->find_data($map, 'id');
                if ($check_linmit_disocunt)
                    $this->error('不能重复参加活动');


                $row = $row->getData();
                $info = $this->model->getLimitDiscountInfoByID($groupbuy_id ,'status,group_num,hour,upper_num');
                $params['upper_num'] = $info['upper_num'];
                $params['hour'] = $info['hour'];
                $params['group_num'] = $info['group_num'];
                $params = array_merge($params,$row);
                $params['status'] = $info['status'];

                $result = $this->groupbuy_goods_model->allowField(true)->save($params);
                if ($result !== false) {
                    $litestore_goods_model->save(['is_marketing' => 1], ['goods_id' => $ids]);

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

        $this->view->assign("row", $row);
        
        $this->view->assign("groupbuy_id", $this->request->param('groupbuy_id'));
        $this->view->assign("goods_id", $this->request->param('goods_id'));
        return $this->view->fetch();
    }


    /**
     * 拼团活动产品删除
     */
    public function limit_goods_del($ids = "")
    {
        if ($ids) {
            $pk = $this->groupbuy_goods_model->getPk();
            $adminIds = $this->getDataLimitAdminIds();
            if (is_array($adminIds)) {
                $this->groupbuy_goods_model->where($this->dataLimitField, 'in', $adminIds);
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
     * 砍价活动产品编辑
     */

    public function limit_goods_edit($ids)
    {
        $row = $this->groupbuy_goods_model->get($ids);
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
                        $name = str_replace("\\model\\", "\\validate\\", get_class($this->groupbuy_goods_model));
                        $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.edit' : $name) : $this->modelValidate;
                        $row->validate($validate);
                    }

                    if($params['stock'] > $params['stock_num'])
                        $this->error(__('Stock_error'));

                    $result = $this->groupbuy_goods_model->editLimitDiscountGoods($params, ['id' => $params['id']]);

                    $map['groupbuy_id'] = $row['groupbuy_id'];
                    $map['goods_id'] = $row['goods_id'];
                    $info = $this->groupbuy_goods_record_model->find_data($map, 'group_price');
                    if ($info) {
                        //获取最小活动价格
                        if ($info['group_price'] > $params['group_price']) {
                            $this->groupbuy_goods_record_model->editLimitDiscount(['group_price' => $params['group_price']], $map);
                        }
                    }

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
