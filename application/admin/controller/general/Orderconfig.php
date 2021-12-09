<?php

namespace app\admin\controller\general;

use app\common\controller\Backend;
use app\common\library\Email;
use app\common\model\orderconfig as OrderconfigModel;
use think\Exception;
use think\Config;

/**
 * 系统配置
 *
 * @icon fa fa-cogs
 * @remark 可以在此增改系统的变量和分组,也可以自定义分组和变量,如果需要删除请从数据库中删除
 */
class Orderconfig extends Backend
{

    /**
     * @var \app\common\model\Config
     */
    protected $model = null;
    protected $noNeedRight = ['check'];

    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('Orderconfig');
    }

    /**
     * 查看
     */
    public function index(){
        $configlist=$this->model->column('name,value');
        $this->view->assign('row',$configlist);
        $this->view->assign('subdivision',Config::get('subdivision'));
        return $this->view->fetch();
    }


    /**
     * 编辑
     * @param null $ids
     */
    public function edit($ids = NULL)
    {
        if ($this->request->isPost()) {
            $row = $this->request->post("row/a");
            if ($row) {
                $configList = [];
                foreach ($this->model->all() as $v) {
                    if (isset($row[$v['name']])) {
                        $value = $row[$v['name']];
                        if (is_array($value) && isset($value['field'])) {
                            $value = json_encode(OrderconfigModel::getArrayData($value), JSON_UNESCAPED_UNICODE);
                        } else {
                            $value = is_array($value) ? implode(',', $value) : $value;
                        }
                        $v['value'] = $value;
                        $configList[] = $v->toArray();
                    }
                }
                $this->model->allowField(true)->saveAll($configList);
                try {
                    $this->refreshFile();
                } catch (Exception $e) {
                    $this->error($e->getMessage());
                }
                $this->success();
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }
    }


    /**
     * 刷新配置文件
     */
    protected function refreshFile()
    {
        $config = [];
        foreach ($this->model->all() as $k => $v) {
            $value = $v->toArray();
            if ($value['name'] == 'appoint_timeset') {
                $value['value'] = (array)json_decode($value['value'], TRUE);
            }
            $config[$value['name']] = $value['value'];
        }
        file_put_contents(APP_PATH . 'extra' . DS . 'orderset.php', '<?php' . "\n\nreturn " . var_export($config, true) . ";");
    }


    public function select(){
        if ($this->request->isAjax()) {
            $total=7;
            $list=array(
                0=>['id'=>'周一','name'=>'周一'],
                1=>['id'=>'周二','name'=>'周二'],
                2=>['id'=>'周三','name'=>'周三'],
                3=>['id'=>'周四','name'=>'周四'],
                4=>['id'=>'周五','name'=>'周五'],
                5=>['id'=>'周六','name'=>'周六'],
                6=>['id'=>'周日','name'=>'周日'],

            );
            $result = array("total" => $total, "rows" => $list);

            return json($result);
        }
        return $this->view->fetch();



    }


}
