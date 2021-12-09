<?php

namespace app\admin\controller\realname;

use app\common\controller\Backend;
use think\Db;
use think\Exception;
use think\exception\PDOException;
use think\exception\ValidateException;

/**
 * 商家实名管理
 *
 * @icon fa fa-circle-o
 */
class Authentication extends Backend
{

    /**
     * Authentication模型对象
     * @var \app\admin\model\Authentication
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Authentication;
        $this->view->assign('storeStatus', $this->model->getStoreStatusList());
    }

    /**
     * 实名审核
     * @param is_store 是否实名认证0）未申请1）申请中 2）申请失败 3）申请成功
     * @param store_status 申请状态 0）待审核 1）审核通过 2）审核失败
     */
    public function refuse($ids = '')
    {

        //判断是否是post提交
        if ($this->request->isPost()) {
            //接收所有
            $refund_reason = $this->request->request();
            !$ids && $this->error('参数有误');

            $row = $this->request->request('row/a');
            $where = ['id' => $ids];
            //查询一条数据
            $user_list = $this->model->find_data($where, 'uid');

            !$user_list && $this->error('数据错误');
            //开启事务
            Db::startTrans();

            $user = new \app\admin\model\User();

            $result = $this->model->update_data($where, ['store_status' => $refund_reason['row']['store_status'], 'refund_reason' => $refund_reason['refund_reason'], 'audit_time' => time()]);
            switch ($refund_reason['row']['store_status']) {
                case 0:
                    $user->update_data(['id' => $user_list['uid']], ['is_store' => 1]);
                    break;
                case 1:
                    $user->update_data(['id' => $user_list['uid']], ['is_store' => 3]);
                    break;
                case 2:
                    $user->update_data(['id' => $user_list['uid']], ['is_store' => 2]);
                    break;
            }
            if (!$result) {
                //事务回滚
                Db::rollback();
                $this->error('操作失败');
            }
            //提交事务
            Db::commit();
            $this->success('操作成功');
        }
        //将数据查询出并渲染到页面
        $this->assign('row', $this->model->find_data("id=$ids"));
        return $this->fetch();
    }


    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
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

//            //修改查询条件
//            $filter = json_decode($this->request->get('filter'), true);
//
//            if($filter['username']){
//
//                $filter['user.username']=$filter['username'];
//                unset($filter['username']);
//
//                $op = json_decode($this->request->get('op'), true);
//                $op['user.username'] = $op['username'];
//                unset($op['username']);
//
//                $this->request->get(['op' => json_encode($op)]);
//                $this->request->get(['filter' => json_encode($filter)]);
//            }


            list($where, $sort, $order, $offset, $limit) = $this->buildparams();

            $total = $this->model
                ->with('user')
                ->where($where)
                ->order($sort, $order)
                ->count();

            $list = $this->model
                ->with('user')
                ->where($where)
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();

            $list = collection($list)->toArray();
            $result = array("total" => $total, "rows" => $list);
//            dump($result  );
            return json($result);

        }
        return $this->view->fetch();
    }

    /**
     * 读取省市区数据,联动列表
     */
    public function area()
    {
        $province = $this->request->get('province_id');
        $city = $this->request->get('city_id');
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
}
