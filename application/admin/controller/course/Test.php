<?php

namespace app\admin\controller\course;

use app\common\controller\Backend;
use app\common\model\Litestorespec as SpecModel;
use app\common\model\Litestorespecvalue as SpecValueModel;
use fast\Tree;
use think\Db;

/**
 *
 *
 * @icon fa fa-circle-o
 */
class Test extends Backend
{
    private $SpecModel;
    private $SpecValueModel;
    protected $noNeedLogin = ['*'];
    /**
     * Litestoregoods模型对象
     * @var \app\admin\model\Litestoregoods
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->SpecModel = new SpecModel;
        $this->SpecValueModel = new SpecValueModel;

        $this->model = model('Litestoregoods');
        $this->goodsstatus = new \app\admin\model\litestore\goods\Status;

        $this->view->assign("goodsstatus", $this->goodsstatus->select_data('', 'id,goods_status'));

        $this->view->assign("specTypeList", $this->model->getSpecTypeList());
        $this->view->assign("deductStockTypeList", $this->model->getDeductStockTypeList());
        $this->view->assign("goodsStatusList", $this->model->getGoodsStatusList());
        $this->view->assign("isDeleteList", $this->model->getIsDeleteList());
        $this->view->assign("spec_attr", '');
        $this->view->assign("spec_list", '');
        $this->item_attr = model('Itemattr');
        $this->CateModel = model('Litestorecategory');
        $tree = Tree::instance();
        $tree->init(collection($this->CateModel->order('weigh desc,id desc')->select())->toArray(), 'pid');
        $this->categorylist = $tree->getTreeList($tree->getTreeArray(0), 'name');
        $categorydata = [0 => ['type' => 'all', 'name' => __('None')]];
        foreach ($this->categorylist as $k => $v) {
            $categorydata[$v['id']] = $v;
        }

        $this->view->assign("parentList", $categorydata);
        $this->groupbuy_goods_model = model('Groupbuygoods');
        $this->cut_down_goods_model = model('Cutdowngoods');
        $this->limit_discount_goods_model = model('Limitdiscountgoods');
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
        //当前是否为关联查询
        $this->relationSearch = true;
        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }

            list($where, $sort, $order, $offset, $limit) = $this->buildparams();

            $total = $this->model
                ->with(['category', 'freight'])
                ->where($where)
                ->order($sort, $order)
                ->count();

            $list = $this->model
                ->with(['category', 'freight'])
                ->where($where)
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();

            foreach ($list as $row) {
                $row->getRelation('category')->visible(['name']);
                $row->getRelation('freight')->visible(['name']);
            }
            $list = collection($list)->toArray();
            $result = array("total" => $total, "rows" => $list);

            return json($result);
        }
        return $this->view->fetch();
    }


    public function addSpec($spec_name, $spec_value)
    {
        // 判断规格组是否存在
        if (!$specId = $this->SpecModel->getSpecIdByName($spec_name)) {
            // 新增规格组and规则值
            if ($this->SpecModel->add($spec_name)
                && $this->SpecValueModel->add($this->SpecModel['id'], $spec_value))
                return $this->success('', '', [
                    'spec_id' => (int)$this->SpecModel['id'],
                    'spec_value_id' => (int)$this->SpecValueModel['id'],
                ]);
            return $this->error();
        }
        //return ;
        // 判断规格值是否存在
        if ($specValueId = $this->SpecValueModel->getSpecValueIdByName($specId, $spec_value)) {
            return $this->success('', '', [
                'spec_id' => (int)$specId,
                'spec_value_id' => (int)$specValueId,
            ]);
        }
        // 添加规则值
        if ($this->SpecValueModel->add($specId, $spec_value))
            return $this->success('', '', [
                'spec_id' => (int)$specId,
                'spec_value_id' => (int)$this->SpecValueModel['id'],
            ]);
        return $this->error();
    }


    /**
     * 添加规格值
     */
    public function addSpecValue($spec_id, $spec_value)
    {
        // 判断规格值是否存在
        if ($specValueId = $this->SpecValueModel->getSpecValueIdByName($spec_id, $spec_value)) {
            return $this->success('', '', [
                'spec_value_id' => (int)$specValueId,
            ]);
        }
        // 添加规则值
        if ($this->SpecValueModel->add($spec_id, $spec_value))
            return $this->success('', '', [
                'spec_value_id' => (int)$this->SpecValueModel['id'],
            ]);
        return $this->error();
    }

    /**
     * 添加
     */
    public function add()
    {
        if ($this->request->isPost()) {
            $params = $this->request->post("row/a");

            $params['place_delivery'] = $params['province_id'] . ',' . $params['city_id'] . ',' . $params['area_id'];
            unset($params['province_id']);
            unset($params['city_id']);
            unset($params['area_id']);
            $attr = empty($params['attr']) ? '' : $params['attr'];
            if ($params) {
                unset($params['attr']);
                if (!$params['category_id'])
                    $this->error(__('请选择分类'));

                if ($this->dataLimit && $this->dataLimitFieldAutoFill) {
                    $params[$this->dataLimitField] = $this->auth->id;
                }

                $spec_many_params = $this->request->post("spec_many/a");
                try {

//                    $params['status'] = implode(',',$params['status']);
//                    dump($params['status']);die;
                    //是否采用模型验证
                    if ($this->modelValidate) {
                        $name = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                        $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.add' : true) : $this->modelValidate;
                        $this->model->validate($validate);
                    }
                    //获取视频 视频第一帧图片
                    $params = array_merge($params, getImgs($params['images']));
                    $result = $this->model->allowField(true)->save($params);

                    //\think\Log::write('hawk-1 result'.json_encode($result), \think\Log::NOTICE);
                    if ($result !== false) {
                        //商品参数添加
                        $item_attr = [];
                        if (!empty($attr)) {
                            $attr = json_decode($attr, true);
                            foreach ($attr as $k => $v) {
                                $item_attr[] = [
                                    'name' => $v['name'],
                                    'value' => $v['value'],
                                    'goods_id' => $this->model->goods_id,
                                    'type' => 1,
                                ];
                            }

                            $this->item_attr->insertAll($item_attr);
                        }
                        //成功之后 存储商品规格

                        //\think\Log::write('hawk0 spec_many_params'.json_encode($spec_many_params), \think\Log::NOTICE);
                        $this->model->addGoodsSpec($params, $spec_many_params, $this->request->post("spec/a"));
                        //添加多规格到商品表
                        $this->model->editGoodsPriceSction($this->model->goods_id, $params['spec_type']);

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
     */
    public function edit($ids = NULL)
    {

        /*添加营销商品活动 关联添加活动*/
        $marketing_type = $this->request->param('marketing_type'); //营销活动类型  1)2人团购  2）限时砍价
        $marketing_id = $this->request->param('marketing_id'); //营销活动id

        $ids = $marketing_id ? $this->request->param('goods_id') : $ids;

        $row = $this->model->get($ids, ['specRel', 'spec', 'spec_rel.spec']);

//        dump($row->toArray());die;
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

            $params['place_delivery'] = $params['province_id'] . ',' . $params['city_id'] . ',' . $params['area_id'];
            unset($params['province_id']);
            unset($params['city_id']);
            unset($params['area_id']);


            $cut_down_goods_model = model('Cutdowngoods');
            $cut_down_goods_id = $cut_down_goods_model->find_data(['goods_id' => $ids], 'id');
            if ($params['is_marketing'] == 3 && $cut_down_goods_id)
                $this->error('砍价活动商品不能编辑');

            if ($params) {
                if (!$params['category_id'])
                    $this->error(__('请选择分类'));

//                $params['status'] = implode(',',$params['status']);
                $attr = empty($params['attr']) ? '' : $params['attr'];
                unset($params['attr']);
                try {
                    $params['is_marketing'] = $params['marketing_type'] ? $params['marketing_type'] : $row['is_marketing'];
                    $params['marketing_id'] = $params['marketing_id'] ? $params['marketing_id'] : $row['marketing_id'];
                    if ($params['is_delete'] == 1) { //删除商品操作
                        //营销商品的删除 清除购物车
                        $this->edit_marketing($ids, $params['is_marketing'], 1);
                        $params['is_marketing'] = 0;
                    }

                    //下架商品 清除购物车
                    if ($params['goods_status'] == 20) {
                        //清空购物车
                        model('Shopingcart')->where(['goods_id' => $ids])->delete();
                    }

                    //是否采用模型验证
                    if ($this->modelValidate) {
                        $name = basename(str_replace('\\', '/', get_class($this->model)));
                        $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.edit' : true) : $this->modelValidate;
                        $row->validate($validate);
                    }

                    $params = array_merge($params, getImgs($params['images']));

                    $result = $row->allowField(true)->save($params);
//                    dump($row->getLastSql());die;
                    if ($result !== false) {
                        //成功之后 存储商品规格
                        $spec_many_params = $this->request->post("spec_many/a");

                        $row->addGoodsSpec($params, $spec_many_params, $this->request->post("spec/a"), true);

                        //添加多规格到商品表
                        // $row->editGoodsPriceSction($ids ,$params['spec_type']);

                        $this->item_attr->where(['goods_id' => $ids, 'type' => 1])->delete();

                        //商品参数添加
                        $item_attr = [];
                        if (!empty($attr)) {
                            $attr = json_decode($attr, true);
                            foreach ($attr as $k => $v) {
                                $item_attr[] = [
                                    'name' => $v['name'],
                                    'value' => $v['value'],
                                    'goods_id' => $ids,
                                    'type' => 1,
                                ];
                            }
                            $this->item_attr->insertAll($item_attr);
                        }

                        /*添加 编辑营销活动
                        */
                        if ($params['is_marketing'] == 1 || $params['is_marketing'] == 2)
                            $this->add_marketing($ids, $params['is_marketing'], $params['marketing_id']);

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
        // 多规格信息
        $specData = 'null';
        if ($row['spec_type'] === '20') {
            $specData = json_encode($this->model->getManySpecData($row['spec_rel'], $row['spec'], $marketing_type));
        }
        $row['specData'] = $specData;

        $row['attr'] = $this->item_attr->select_data(['goods_id' => $ids, 'type' => 1], 'name,value');


        $row ['place_delivery'] = explode(',', $row['place_delivery']);
        $row['province_id'] = $row['place_delivery'][0];
        $row['city_id'] = $row['place_delivery'][1];
        $row['area_id'] = $row['place_delivery'][2];
        unset($row['place_delivery']);


        $this->view->assign("row", $row);
        $this->view->assign("marketing_type", $marketing_type ? $marketing_type : 0);
        $this->view->assign("marketing_id", $marketing_id ? $marketing_id : 0);
        return $this->view->fetch();
    }


    public static function before_update($data)
    {
        dump($data);
        exit;
    }

    /**
     * 添加营销活动
     * @param goods_id       商品id
     * @param marketing_id   营销活动id
     * @param marketing_type 营销活动类型 1)2人团购  2)限时抢购
     */
    public function add_marketing($goods_id, $marketing_type, $marketing_id = '')
    {

        $goods_info = $this->model->getLitestoreGoodsInfoByID($goods_id, '*');
        $goods_info = $goods_info->getData();
        $where = [];
        switch ($marketing_type) {
            case 1: //2人团购
                //查询是否已经参与活动
                $info = $this->groupbuy_goods_model->find_data(['goods_id' => $goods_id], 'groupbuy_id ,id');
                $marketing_id = $marketing_id ? $marketing_id : $info['groupbuy_id'];

                //获取活动基本信息
                $model = model('Groupbuy');
                $infos = $model->getLimitDiscountInfoByID($marketing_id, 'status,group_num,hour,upper_num');
                $param = array_merge($goods_info, $infos->getData());
                $param['groupbuy_id'] = $marketing_id;
                $param['goods_price'] = $goods_info['marketing_goods_price'];
                $param['status'] = $goods_info['goods_status'];

                if ($info['id'])
                    $where['id'] = $info['id'];

                return $this->groupbuy_goods_model->allowField(true)->save($param, $where);
                break;
            case 2://限时抢购
                //查询是否已经参与活动
                $info = $this->limit_discount_goods_model->find_data(['goods_id' => $goods_id], 'limit_discount_id ,id');
                $marketing_id = $marketing_id ? $marketing_id : $info['limit_discount_id'];

                $infos = model('Limitdiscount')->getLimitDiscountInfoByID($marketing_id, 'start_time , end_time , upper_num');
                $param = array_merge($goods_info, $infos->getData());
                $param['goods_price'] = $goods_info['marketing_goods_price'];
                $param['limit_discount_id'] = $marketing_id;
                $param['status'] = $goods_info['goods_status'];
                $param['total_stock'] = $goods_info['stock_num'];
                if ($info['id'])
                    $where['id'] = $info['id'];

                return $this->limit_discount_goods_model->allowField(true)->save($param, $where);
                break;
        }
    }

    /**
     * 选择商品
     */
    public function select($goods_id = 0)
    {
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }
            //已经参加活动的商品排除掉
            $groupbuy_goods_ids_arr = model('groupbuy')->where(['end_time' => ['egt', $_SERVER['REQUEST_TIME']]])->column('goods_id');
            $limit_goods_ids_arr = model('Limitdiscountgoods')->where(['end_time' => ['egt', $_SERVER['REQUEST_TIME']]])->column('goods_id');
            $goods_ids_arr = array_merge($groupbuy_goods_ids_arr, $limit_goods_ids_arr);
            $wherea = !empty($goods_ids_arr) ? ['goods_id' => ['not in', $goods_ids_arr]] : Null;
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $total = $this->model
                ->with(['specRel', 'spec', 'spec_rel.spec'])
                ->where($where)
                ->where($wherea)
                ->order($sort, $order)
                ->count();
            $list = $this->model
                ->with(['specRel', 'spec', 'spec_rel.spec'])
                ->where($where)
                ->where($wherea)
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();
            foreach ($list as $k => $row) {
                // 多规格信息
                $specData = 'null';
                if ($row['spec_type'] === '20') {
                    $specData = $this->model->getManySpecData($row['spec_rel'], $row['spec']);
                }
                $list[$k]['specData'] = $specData;
                $list[$k]['spec'] = $row['spec'];
            }
            $list = collection($list)->toArray();
            $result = array("total" => $total, "rows" => $list);

            return json($result);
        }
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
            $list = $this->model->where($pk, 'in', $ids)->field('goods_id,is_marketing')->select();
            $count = 0;
            foreach ($list as $k => $v) {
                //营销商品的删除 清除购物车
                $this->edit_marketing($v['goods_id'], $v['is_marketing'], 1);
                $count += $v->delete();//['is_delete' => 1,'is_marketing'=>0]
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
     * 关联编辑营销活动
     * @param is_marketing 1)团购  2）限时  3）砍价
     * @param goods_id
     * @param type 1)删除  2）编辑
     */
    public function edit_marketing($goos_id, $is_marketing, $type, $status)
    {

        switch ($is_marketing) {
            case 1:
                $model = $this->groupbuy_goods_model;
                break;
            case 2:
                $model = $this->limit_discount_goods_model;
                break;
            case 3:
                $model = $this->cut_down_goods_model;
                break;
            default:
                return true;
                break;
        }
        $where['goods_id'] = $goos_id;
        if ($type == 1) {
            return $model->where($where)->delete();
            //清空购物车
            model('Shopingcart')->where($where)->delete();
        } else {
            return $model->save(['status' => $status], $where);
        }
    }

    /**
     * 读取省市区数据,联动列表
     */
    public function area()
    {
        $province = $this->request->get('row.province_id');
        $city = $this->request->get('row.city_id');
        $where = ['pid' => 0, 'level' => 1];
        $provincelist = null;
        if ($province !== '') {
            if ($province) {
                $where['pid'] = $province;
                $where['level'] = 2;
            }
            if ($city !== '') {
                if ($city) {
                    $where['pid'] = $city;
                    $where['level'] = 3;
                }
                $provincelist = Db::name('area')->where($where)->field('id as value,name')->select();
            }
        }
        $this->success('', null, $provincelist);
    }

    /**
     * @param int goods_status 商品状态:10=出售中,20=已售完,30=仓库中,40=回收站
     * @param int uppershelf_time 商品上架时间
     * @param int stock_num 商品库存
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function automatic()
    {
        $param = $this->model->select_data('', 'goods_id,goods_status,uppershelf_time,stock_num');
        foreach ($param as $k => $v) {

            // 商品状态在仓库中 并  库存大于0  上架时间是小于当前时间的  就将商品状态改成出售中
            if ($v['goods_status'] == '30' && $v['stock_num'] > 0 && $v['uppershelf_time'] < time()) {

                $this->model->update_data(['goods_id' => $v['goods_id']], ['goods_status' => '10']);
            }
            //商品状态不是回收站 并 当商品库存小于等于0时  将商品状态改成已售完
            if($v['goods_status'] != '40'&& $v['stock_num'] <= 0){
                $this->model->update_data(['goods_id' => $v['goods_id']],['goods_status' => '20']);
            }
        }
    }
}
