<?php

namespace app\admin\model\cms;

use think\Model;

class Adcate extends Model
{

    // 表名
    protected $name = 'cms_adcate';
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';

    //protected $updateTime = 'updatetime';

//    public function getJointimeTextAttr($value, $data)
//    {
//        $value = $value ? $value : $data['createtime'];
//        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
//    }


    public function select_data($where, $sort, $order, $offset, $limit,$field='*')
    {
        return $this->field($field)->where($where)->order($sort, $order)->limit($offset, $limit)->select();
    }

    public function getStatusList()
    {
        return ['normal' => __('Normal'), 'hidden' => __('Hidden')];
    }

    /**
     * 添加
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
                    //是否采用模型验证
                    if ($this->modelValidate) {
                        $name = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                        $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.add' : $name) : $this->modelValidate;
                        $this->model->validate($validate);
                    }
                    $save['createtime']=$_SERVER['REQUEST_TIME'];
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



}
