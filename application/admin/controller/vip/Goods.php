<?php

namespace app\admin\controller\vip;

use app\admin\model\Litestoregoodsspec;
use app\common\controller\Backend;
use app\common\model\Litestorespec as SpecModel;
use app\common\model\Litestorespecvalue as SpecValueModel;
use fast\Date;
use fast\Tree;
use think\Db;

/**
 *
 *
 * @icon fa fa-circle-o
 */
class Goods extends Backend
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
        $this->view->assign("goodsStatusList", $this->model->getGoodsStatusList());
        $this->item_attr = model('Itemattr');
        $categorydata = [0 => ['type' => 'all', 'name' => __('None')]];

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

            $wheres = 'litestoregoods.status=9999';
            $total = $this->model
                ->with(['category', 'freight'])
                ->where($where)
                ->where($wheres)
                ->order($sort, $order)
                ->count();

            $list = $this->model
                ->field('goods_id,goods_name,vip_level,is_recommend,goods_status,images,spec_type,content,is_recommend')
                ->with(['category', 'freight'])
                ->where($where)
                ->where($wheres)
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

    public function add()
    {
        if ($this->request->isPost()) {
            $params = $this->request->post('row/a');
            if ($params) {
                $this->model->startTrans();
                $goodsSpec = new Litestoregoodsspec();
                if ($this->model->allowField(true)->save($params)) {
                    $params['goods_id'] = $this->model->goods_id;
                    $params['spec_image'] = $params['image'];

                    if ($goodsSpec->allowField(true)->save($params)) {
                        $this->model->commit();
                        $this->success('添加成功');
                    }
                }
                $this->model->rollback();
                $this->error('添加失败');
            }
        }
        return $this->fetch();
    }


    /**
     * 编辑
     */
    public function edit($ids = NULL)
    {
        $row = $this->model->get($ids, ['spec']);
        !$row && $this->error(__('No Results were found'));

        if ($this->request->isPost()) {
            $params = $this->request->post('row/a');
            if ($params) {
                $this->model->startTrans();
                if ($row->allowField(true)->save($params)) {
                    $params['spec_image'] = $params['image'];
                    if ($row->spec[0]->allowField(true)->save($params)) {
                        $this->model->commit();
                        $this->success('编辑成功');
                    }
                }
                $this->model->rollback();
                $this->error('编辑失败');
            }
        }

        $this->view->assign("row", $row);
        return $this->view->fetch();
    }

    public function del($ids = "")
    {
        $where = ['goods_id' => ['in', $ids]];
        if ($this->model->where($where)->delete() && model('Litestoregoodsspec')->where($where)->delete()) {
            $this->success('删除成功');
        }
        $this->error('删除失败');
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
}
