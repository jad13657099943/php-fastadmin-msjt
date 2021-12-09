<?php

namespace app\admin\controller\coupon;

use app\common\controller\Backend;

/**
 *
 *
 * @icon fa fa-circle-o
 */
class Litestorecoupon extends Backend
{

    /**
     * Coupon模型对象
     * @var \app\admin\model\litestore\Coupon
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\litestore\Coupon;
        $this->view->assign("limitTypeList", $this->model->getLimitTypeList());
        $this->view->assign("couponTypeList", $this->model->getCouponTypeList());
        $this->view->assign("getTypeList", $this->model->getGetTypeList());
        $this->view->assign("isLimitLevelList", $this->model->getIsLimitLevelList());
        $this->view->assign("userLevelDataList", $this->model->getUserLevelDataList());
        $this->view->assign("limitGoodsCategoryList", $this->model->getLimitGoodsCategoryList());
        $this->view->assign("limitGoodsList", $this->model->getLimitGoodsList());
        $this->view->assign("typeDataList", $this->model->getTypeDataList());
    }

    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
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

            $this->relationSearch = true;

            $total = $this->model
                ->with(['category'])
                ->where($where)
                ->order($sort, $order)
                ->count();

            $list = $this->model
                ->field('id,category_id,name,enough,discount,coupon_type,total,deduct,receive_num,use_num,remainder_num,weigh')
                ->with(['category'])
                ->where($where)
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();

            $list = collection($list)->toArray();
//            dump($list);die;
            $result = array("total" => $total, "rows" => $list);

            return json($result);
        }
        return $this->view->fetch();
    }


    public function edit($ids = NULL)
    {
        if ($this->request->isPost()) {
            $params = $this->request->post("row/a");
//            $time= time();
//            $date = date('m-d H:i:s', strtotime( '-5 Minute', $time));
//            if ($params['receive_start_time'] >=$date )
//            {
//                $this->error('领取时间开始前5分钟不能修改');
//            }
            if ($params['is_index'] && $this->model->getIndex($ids)) {
                $this->error('当前已有首页推荐优惠券!');
            }
            $total = $this->model->where('id',$ids)->find();
            $total->name=$params['name'];
            $total->enough=$params['enough'];
            $total->category_id=$params['category_id'];
            $total->type_data=$params['type_data'];
            $total->weigh=$params['weigh'];
            $total->timedays=$params['timedays'];
            $total->limit_type=$params['limit_type'];
            $total->use_time_range=$params['use_time_range'];
            $total->use_start_time=$params['use_start_time'];
            $total->use_end_time=$params['use_end_time'];
            $total->deduct=$params['deduct'];
            $total->get_type=$params['get_type'];
            $total->get_max=$params['get_max'];
            $total->receive_start_time=$params['receive_start_time'];
            $total->receive_end_time=$params['receive_end_time'];
            $total->is_index=$params['is_index'];
            $total->total=$params['total'];
            $total->limit_goods_category=$params['limit_goods_category'];
            $total->litestore_category_ids=$params['litestore_category_ids'];
            $total->limit_goods=$params['limit_goods'];
            $total->litestore_goods_ids=$params['litestore_goods_ids'];
            if ($total->save()){
                $this->success('修改ok');
            }else{
                $this->error('修改失败');
            };



        }
        return parent::edit($ids);
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
            if ($params) {

                try {
                    if ($params['is_index'] && $this->model->getIndex()) {
                        $this->error('当前已有首页推荐优惠券!');
                    }
                    $params['remainder_num'] = $params['total'];
                    $result = $this->model->addcoupon($params);
                    if ($result !== false) {
                        $this->success();
                    } else {
                        $this->error($this->modle->getError());
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
     * 删除
     * @param string $ids
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function del($ids = "")
    {
        if ($ids) {
            $pk = $this->model->getPk();
            $adminIds = $this->getDataLimitAdminIds();
            if (is_array($adminIds)) {
                $this->model->where($this->dataLimitField, 'in', $adminIds);
            }
            $list = $this->model->where($pk, 'in', $ids)->select();
            $count = 0;
            foreach ($list as $k => $v) {
                $i = $v->delete();
                $count += $i;
                if ($i != 0) {
                    model('common/litestore/CouponData')->where('litestore_coupon_id', 'eq', $v['id'])->delete();
                }
            }
            if ($count) {
                $this->success();
            } else {
                $this->error(__('No rows were deleted'));
            }
        }
        $this->error(__('Parameter %s can not be empty', 'ids'));
    }


}
