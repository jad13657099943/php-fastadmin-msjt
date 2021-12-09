<?php

namespace app\admin\controller\course;

use app\common\controller\Backend;
use fast\Tree;

/**
 * 教师管理
 *
 * @icon fa fa-circle-o
 */
class Teacher extends Backend
{

    /**
     * Teacher模型对象
     * @var \app\admin\model\Teacher
     */
    protected $model = null;
    protected $course_category_model = null;
    protected $subject_category_model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('common/Teacher');
        $this->course_category_model = model('Coursecategory');
        $this->subject_category_model = model('Dansubjectcategory');
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
                ->with(['coursecateaory', 'subjectcateaory'])
                ->where($where)
                ->order($sort, $order)
                ->count();

            $list = $this->model
                ->with(['coursecateaory' => function ($query) {
                    $query->withField('id,name');
                }, 'subjectcateaory' => function ($query) {
                    $query->withField('id,name');
                }])
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
            $province = $this->request->request('province');
            $city = $this->request->request('city');
            $area = $this->request->request('area');

            if ($params) {
                //添加籍贯
                $params['native_place'] = $province . ',' . $city;
                !empty($area) && $params['native_place'] .= ',' . $area;

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
                    if ($params['downloadurl']) {
                        $params['downloadurl'] = renames($params['downloadurl'], $params['newversion']);
                    }
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
        } else {
            //科目段位 课程分类
            $list = courseAdnSubject_list();

            $this->view->assign('course_category_list', $list['course']);
            $this->view->assign('subject_category_list', $list['subject']);
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
            $province = $this->request->request('province');
            $city = $this->request->request('city');
            $area = $this->request->request('area');

            if ($params) {
                //添加籍贯
                $params['native_place'] = $province . ',' . $city;
                !empty($area) && $params['native_place'] .= ',' . $area;

                try {
                    //是否采用模型验证
                    if ($this->modelValidate) {
                        $name = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                        $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.edit' : $name) : $this->modelValidate;
                        $row->validate($validate);
                    }
                    if ($params['downloadurl']) {
                        $params['downloadurl'] = renames($params['downloadurl'], $params['newversion']);
                    }
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
        } else {
            //科目段位 课程分类
            $list = courseAdnSubject_list();

            $this->view->assign('course_category_list', $list['course']);
            $this->view->assign('subject_category_list', $list['subject']);
        }
        $row = $row->toArray();
        $row['native_place'] = explode(',', $row['native_place']);
        $this->view->assign("row", $row);
        return $this->view->fetch();
    }

}
