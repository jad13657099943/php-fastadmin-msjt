<?php

namespace app\admin\controller\apply;

use app\common\controller\Backend;
use fast\Tree;

use think\Loader;


/**
 * 砍价活动表
 *
 * @icon fa fa-users
 * @remark 一个管理员可以有多个角色组,左侧的菜单根据管理员所拥有的权限进行生成
 */
class Cutdown extends Backend
{
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('Cutdown');
        $this->CateModel = model('Litestorecategory');
        $this->cut_down_goods_model = model('Cutdowngoods');
        $tree = Tree::instance();
        $tree->init(collection($this->CateModel->order('weigh desc,id desc')->select())->toArray(), 'pid');
        $this->categorylist = $tree->getTreeList($tree->getTreeArray(0), 'name');
        $categorydata = [0 => ['type' => 'all', 'name' => __('None')]];
        foreach ($this->categorylist as $k => $v)
        {
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
            $list = $this->model->getLimitDiscountList($where, $sort, $order, $offset, $limit);
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
                    $params['end_time'] = strtotime($params['end_time']); //dump($params); exit;
                    $params['status'] = $params['end_time'] < time() ? 0 : $params['status'];
                    $result = $this->model->editLimitDiscount($params, ['id' => $params['id']]);
                    if ($result !== false) {
                        $cut_down_id = $params['id'];
                        unset($params['id'] , $params['title']);
                        $params['status'] = $params['status'] == 0 || $params['end_time'] < time() ? 20 :10;
                        $this->cut_down_goods_model->editLimitDiscountGoods($params ,['cut_down_id' => $cut_down_id]);
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
                $where['cut_down_id'] = $v['id'];
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
        $goods_ids = $this->cut_down_goods_model->where($where)->column('goods_id');
        model('Litestoregoods')->save(['is_marketing' => 0 ,'marketing_id' => 0] , ['goods_id'=> ['in' , $goods_ids]]);
        //删除活动列表
        return $this->cut_down_goods_model->where($where)->delete();
    }


    /**
     * 限时活动已加入商品管理
     */
    public function manage($ids = NULL)
    {
        $this->assignconfig('cut_down_id', $this->request->param('ids'));
        if ($this->request->isAjax()) {
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $wherea['cut_down_id'] = $this->request->param('cut_down_id');
            $total = $this->cut_down_goods_model
                            ->where($where)
                            ->where($wherea)
                            ->order($sort, $order)
                            ->count();

            $list = $this->cut_down_goods_model->getLimitDiscountGoodsList($where, $wherea, $sort, $order, $offset, $limit);

            if($list != null){
                $litestore_goods_spec_model = model('Litestoregoodsspec');
                foreach ($list as $k=>$v){
                    $info = $litestore_goods_spec_model->getLitestoreGoodsSpecInfoByID($v['goods_spec_id'] ,'key_name');
                    $list[$k]['key_name'] = $info['key_name'] ?$info['key_name'] :'单规格';
                }
            }

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
        $this->assignconfig('cut_down_id', $this->request->param('ids'));
        if ($this->request->isAjax()) {
            list($where, $sort, $order, $offset, $limit) = $this->buildparamsc();
            $goods_spec_model = model('Litestoregoodsspec');
            $mapwhere['g.is_marketing'] = 0;
            $mapwhere['g.goods_status'] = '10';
            $mapwhere['g.is_delete'] = '0';
            $total = $goods_spec_model
                ->alias('s')
                ->join('litestore_goods g', 'g.goods_id = s.goods_id', 'LEFT')
                ->where($where)
                ->where($mapwhere)
                ->order($sort, $order)
                ->count();
            $list = $goods_spec_model->where($where)
                ->where($mapwhere)
                ->alias('s')
                ->join('litestore_goods g', 'g.goods_id = s.goods_id', 'LEFT')
                ->field('g.image,g.goods_name,s.goods_price,s.goods_spec_id,s.goods_id,s.key_name,s.stock_num')
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();

            foreach ($list as $k => $v) {
                $list[$k]['images'] = $v['image'];
                $list[$k]['key_name'] = $v['key_name'] ?$v['key_name'] :"单规格";
            }

            $result = array("total" => $total, "rows" => $list);
            return json($result);
        }
        $this->view->assign("ids", $ids);
        return $this->view->fetch();
    }

    /**
     * 添加砍价活动产品
     */
    public function goods_add($ids = NULL)
    {
        if ($this->request->isPost()) {
            $params = $this->request->post("row/a");
            if ($params) {
                if ($this->dataLimit && $this->dataLimitFieldAutoFill) {
                    $params[$this->dataLimitField] = $this->auth->id;
                }

                //判断是否参加活动
                $limit_discount_info = $this->model->getLimitDiscountInfoByID($params['cut_down_id'], 'start_time');
                $map['end_time'] = array('gt', $limit_discount_info['start_time']);
                $map['goods_id'] = $params['goods_id'];
                $map['goods_spec_id'] = $params['goods_spec_id'];
                $groupbuy_info = model('cut_down_goods')->getLimitDiscountGoods($map);
                if (!empty($groupbuy_info)) {
                    $this->error('该商品已经参加了活动');
                }
                try {
                    if($params['floor_price'] > $params['highest_price'])
                        $this->error(__('Price_error'));

                    if($params['stock'] > $params['stock_num'])
                        $this->error(__('Stock_error'));

                    $limit_discount_info = $this->model->getLimitDiscountInfoByID($params['cut_down_id']);
                    
                    $params['start_time'] = $limit_discount_info['start_time'];
                    $params['end_time'] = $limit_discount_info['end_time'];
                    $goods_info = model('Litestoregoods')->getLitestoreGoodsInfoByID($params['goods_id']);
                    $params['image'] = $goods_info['image'];
                    $params['goods_name'] = $goods_info['goods_name'];
                    $params['goods_id'] = $params['goods_id'];

                    $result = $this->cut_down_goods_model->addLimitDiscountGoods($params);
                    if ($result !== false) {
                        model('Litestoregoods')->save(['is_marketing' => 3 ,
                                                       'marketing_goods_price' => $params['discount_price'],
                                                       'marketing_id' => $params['cut_down_id']],
                                                       ['goods_id' => $params['goods_id']]);
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
        $goods_spec_model = model('Litestoregoodsspec');
        $row = $goods_spec_model->getLitestoreGoodsSpecInfoByID($ids);
        $row['key_name'] = $row['key_name'] ? $row['key_name']:'单规格';
        if (!$row)
            $this->error(__('No Results were found'));
        $this->view->assign("row", $row);
        $this->view->assign("cut_down_id", $this->request->param('cut_down_id'));
        $this->view->assign("goods_id", $this->request->param('goods_id'));
        return $this->view->fetch();
    }

    /**
     * 限时活动产品删除
     */
    public function limit_goods_del($ids = "")
    {
        if ($ids) {
            $pk = $this->cut_down_goods_model->getPk();
            $adminIds = $this->getDataLimitAdminIds();
            if (is_array($adminIds)) {
                $this->cut_down_goods_model->where($this->dataLimitField, 'in', $adminIds);
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
        $row = $this->cut_down_goods_model->get($ids);
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
                        $name = str_replace("\\model\\", "\\validate\\", get_class($this->cut_down_goods_model));
                        $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.edit' : $name) : $this->modelValidate;
                        $row->validate($validate);
                    }

                    if($params['floor_price'] > $params['highest_price'])
                        $this->error(__('Price_error'));

                    if($params['stock'] > $params['stock_num'])
                        $this->error(__('Stock_error'));

                    $result = $this->cut_down_goods_model->editLimitDiscountGoods($params, ['id' => $params['id']]);
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


    protected function buildparamsc($searchfields = null, $relationSearch = null)
    {
        $searchfields = is_null($searchfields) ? $this->searchFields : $searchfields;
        $relationSearch = is_null($relationSearch) ? $this->relationSearch : $relationSearch;
        $search = $this->request->get("search", '');
        $filter = $this->request->get("filter", '');
        $op = $this->request->get("op", '', 'trim');
        $sort = $this->request->get("sort", "id");
        $order = $this->request->get("order", "DESC");
        $offset = $this->request->get("offset", 0);
        $limit = $this->request->get("limit", 0);
        $filter = (array)json_decode($filter, TRUE);
        $op = (array)json_decode($op, TRUE);
        $filter = $filter ? $filter : [];
        $tree = Tree::instance();
        $son_ids=$tree->getChildrenIds($filter['category_id'],true);
        $filter['category_id']=$son_ids;
        $op['category_id']='IN';
        $where = [];
        $tableName = '';
        if ($relationSearch) {
            if (!empty($this->model)) {
                $name = \think\Loader::parseName(basename(str_replace('\\', '/', get_class($this->model))));
                $tableName = $name . '.';
            }
            $sortArr = explode(',', $sort);
            foreach ($sortArr as $index => & $item) {
                $item = stripos($item, ".") === false ? $tableName . trim($item) : $item;
            }
            unset($item);
            $sort = implode(',', $sortArr);
        }
        $adminIds = $this->getDataLimitAdminIds();
        if (is_array($adminIds)) {
            $where[] = [$tableName . $this->dataLimitField, 'in', $adminIds];
        }
        if ($search) {
            $searcharr = is_array($searchfields) ? $searchfields : explode(',', $searchfields);
            foreach ($searcharr as $k => &$v) {
                $v = stripos($v, ".") === false ? $tableName . $v : $v;
            }
            unset($v);
            $where[] = [implode("|", $searcharr), "LIKE", "%{$search}%"];
        }
        /*    if(!empty($filter['category_id'])){
                $tree = Tree::instance();
                $son_category_ids=$tree->getChildrenIds($filter['category_id'],true);
                $filter['category_id']=['in',$son_category_ids];
            }*/
        foreach ($filter as $k => $v) {
            $sym = isset($op[$k]) ? $op[$k] : '=';
            if (stripos($k, ".") === false) {
                $k = $tableName . $k;
            }
            $v = !is_array($v) ? trim($v) : $v;
            $sym = strtoupper(isset($op[$k]) ? $op[$k] : $sym);
            switch ($sym) {
                case '=':
                case '!=':
                    $where[] = [$k, $sym, (string)$v];
                    break;
                case 'LIKE':
                case 'NOT LIKE':
                case 'LIKE %...%':
                case 'NOT LIKE %...%':
                    $where[] = [$k, trim(str_replace('%...%', '', $sym)), "%{$v}%"];
                    break;
                case '>':
                case '>=':
                case '<':
                case '<=':
                    $where[] = [$k, $sym, intval($v)];
                    break;
                case 'FINDIN':
                case 'FINDINSET':
                case 'FIND_IN_SET':
                    $where[] = "FIND_IN_SET('{$v}', " . ($relationSearch ? $k : '`' . str_replace('.', '`.`', $k) . '`') . ")";
                    break;
                case 'IN':
                case 'IN(...)':
                case 'NOT IN':
                case 'NOT IN(...)':
                    $where[] = [$k, str_replace('(...)', '', $sym), is_array($v) ? $v : explode(',', $v)];
                    break;
                case 'BETWEEN':
                case 'NOT BETWEEN':
                    $arr = array_slice(explode(',', $v), 0, 2);
                    if (stripos($v, ',') === false || !array_filter($arr))
                        continue 2;
                    //当出现一边为空时改变操作符
                    if ($arr[0] === '') {
                        $sym = $sym == 'BETWEEN' ? '<=' : '>';
                        $arr = $arr[1];
                    } else if ($arr[1] === '') {
                        $sym = $sym == 'BETWEEN' ? '>=' : '<';
                        $arr = $arr[0];
                    }
                    $where[] = [$k, $sym, $arr];
                    break;
                case 'RANGE':
                case 'NOT RANGE':
                    $v = str_replace(' - ', ',', $v);
                    $arr = array_slice(explode(',', $v), 0, 2);
                    if (stripos($v, ',') === false || !array_filter($arr))
                        continue 2;
                    //当出现一边为空时改变操作符
                    if ($arr[0] === '') {
                        $sym = $sym == 'RANGE' ? '<=' : '>';
                        $arr = $arr[1];
                    } else if ($arr[1] === '') {
                        $sym = $sym == 'RANGE' ? '>=' : '<';
                        $arr = $arr[0];
                    }
                    $where[] = [$k, str_replace('RANGE', 'BETWEEN', $sym) . ' time', $arr];
                    break;
                case 'LIKE':
                case 'LIKE %...%':
                    $where[] = [$k, 'LIKE', "%{$v}%"];
                    break;
                case 'NULL':
                case 'IS NULL':
                case 'NOT NULL':
                case 'IS NOT NULL':
                    $where[] = [$k, strtolower(str_replace('IS ', '', $sym))];
                    break;
                default:
                    break;
            }
        }
        $where = function ($query) use ($where) {
            foreach ($where as $k => $v) {
                if (is_array($v)) {
                    call_user_func_array([$query, 'where'], $v);
                } else {
                    $query->where($v);
                }
            }
        };
        return [$where, $sort, $order, $offset, $limit];
    }




}
