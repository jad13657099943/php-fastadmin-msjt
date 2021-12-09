<?php

namespace app\admin\controller\cms;

use app\common\controller\Backend;
use app\admin\controller\litestore\Litestoregoods;
/**
 * 广告信息表
 *
 * @icon fa fa-th-large
 */
class Block extends Backend
{

    /**
     * 广告模型对象
     */
    protected $model = null;
    protected $relationSearch = true;
    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\cms\Block;
        $this->adcate_model= new \app\admin\model\cms\Adcate;

        $this->view->assign("statusList", $this->model->getStatusList());
//        dump(config('jump'));die;
        $this->view->assign("cateList", config('jump'));
        $parentList=$this->adcate_model->select_data('','','id asc', '', '','id,name');
        $parentList=collection($parentList)->toArray();
        $this->view->assign('parentList',$parentList);
    }

    public function index()
    {
        $typeArr = \app\admin\model\cms\Block::distinct('type')->column('type');
        $this->view->assign('typeList', $typeArr);
        $this->assignconfig('typeList', $typeArr);
        //return parent::index();

        //设置过滤方法

        $this->request->filter(['strip_tags']);

        if ($this->request->isAjax())
        {

            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField'))
            {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $total = $this->model
                ->with('group')
                ->where($where)
                ->order($sort, $order)
                ->count();
            $list = $this->model
                ->field('id,name,image,createtime,updatetime,jump_status,status,weigh')
                ->with('group')
                ->where($where)
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();
            $result = array("total" => $total, "rows" => $list);

            return json($result);
        }
        return $this->view->fetch();

    }

    public function selectpage_type()
    {
        $list = [];
        $word = (array)$this->request->request("q_word/a");
        $field = $this->request->request('showField');
        $keyValue = $this->request->request('keyValue');
        if (!$keyValue) {
            if (array_filter($word)) {
                foreach ($word as $k => $v) {
                    $list[] = ['id' => $v, $field => $v];
                }
            }
            $typeArr = \app\admin\model\cms\Block::column('type');
            $typeArr = array_unique($typeArr);
            foreach ($typeArr as $index => $item) {
                $list[] = ['id' => $item, $field => $item];
            }
        } else {
            $list[] = ['id' => $keyValue, $field => $keyValue];
        }
        return json(['total' => count($list), 'list' => $list]);
    }

    public function import()
    {
        return parent::import();
    }

    /**
     * 编辑
     */
    public function edit($ids = NULL)
    {
        $row = $this->model->get($ids);
        if (!$row)
            $this->error(__('No Results were found'));
        $this->view->assign('groupList', build_select('row[cate_id]', \app\admin\model\cms\Adcate::column('id,name'), $row['cate_id'], ['class' => 'form-control selectpicker']));
        return $this->rewrite_edit($ids);
    }

    /**
     * 编辑
     */
    public function rewrite_edit($ids = NULL)
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
                    $params['jump_status'] = $params['cate_id'] == '19' ? $params['jump_status'] : -1;
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


    /**
     * 编辑
     */
    public function add()
    {
      //  $data =\app\admin\model\cms\Adcate::column('id,name');

        $grouplist = build_select('row[cate_id]', \app\admin\model\cms\Adcate::column('id,name'), 1, ['class' => 'form-control selectpicker']);

        $this->view->assign('groupList', $grouplist);
        return $this->view->fetch();
    }


    /*
    * 获取商品列表
    */
    public function select(){
        return $this->view->fetch();
    }
}
