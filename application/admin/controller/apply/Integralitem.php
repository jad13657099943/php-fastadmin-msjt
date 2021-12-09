<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/5/16
 * Time: 15:31
 */
namespace app\admin\controller\apply;
use app\common\controller\Backend;
class Integralitem extends Backend{

    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('Integralitem');
        $this->item_attr = model('Itemattr');
    }


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
                ->field('status,id,title,img,integral,inventory,sales,ordid,add_time')
                ->where($where)
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();

            $list = collection($list)->toArray();

            $result = array("total" => $total, "rows" => $list);

            return json($result);
        }
        return $this->view->fetch();
    }

    /**
     * 添加
     */
    public function add()
    {
        if ($this->request->isPost()) {
            $params = $this->request->post("row/a");
            $attr = empty($params['attr'])?'':$params['attr'];
            if ($params) {
                unset($params['attr']);
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
                    $params['add_time']=$_SERVER['REQUEST_TIME'];
                    $result = $this->model->addIntegraItem($params);
                    if ($result !== false) {
                        //商品参数添加
                        $item_attr=[];
                        if(!empty($attr)){
                            $attr=json_decode($attr,true);
                            foreach ($attr as $k=>$v){
                                $item_attr[]=[
                                    'name' => $v['name'],
                                    'value' => $v['value'],
                                    'goods_id' => $this->model->goods_id,
                                    'type' => 2,
                                ];
                            }
                            $this->item_attr->insertAll($item_attr);

                        }
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
                $attr = empty($params['attr'])?'':$params['attr'];
                unset($params['attr']);
                try {
                    //是否采用模型验证
                    if ($this->modelValidate) {
                        $name = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                        $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.edit' : $name) : $this->modelValidate;
                        $row->validate($validate);
                    }
                    $condtion=['id'=>$params['id']];
                    $result = $this->model->editIntegralItem($params,$condtion);
                    if ($result !== false) {

                        $this->item_attr->where(['goods_id'=>$ids ,'type' => 2])->delete();
                        //商品参数添加
                        $item_attr=[];
                        if(!empty($attr)){
                            $attr=json_decode($attr,true);
                            foreach ($attr as $k=>$v){
                                $item_attr[]=[
                                    'name' => $v['name'],
                                    'value' => $v['value'],
                                    'goods_id' => $ids,
                                    'type' => 2,
                                ];
                            }
                            $this->item_attr->insertAll($item_attr);

                        }

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
        $row['attr'] = $this->item_attr->select_data(['goods_id'=>$ids ,'type'=>2] ,'name,value');
        $this->view->assign("row", $row);
        return $this->view->fetch();
    }







}