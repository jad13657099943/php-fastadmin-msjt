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
class Courselist extends Backend
{
    private $SpecModel;
    private $SpecValueModel;
    protected $noNeedLogin = ['*'];
    /**
     * Litestoregoods模型对象
     * @var \app\admin\model\Litestoregoods
     */
    protected $model = null;
    protected $course_category_model = null;
    protected $subject_category_model = null;
    protected $teacher_model = null;
    protected $course_comment_model = null;

    public function _initialize()
    {
        parent::_initialize();

        $this->model = model('Course');
        $this->course_category_model = model('Coursecategory');
        $this->subject_category_model = model('Dansubjectcategory');
        $this->teacher_model = model('Teacher');
        $this->course_comment_model = model('common/Coursecomment');

        //科目段位 课程分类 教师列表
        $list = courseAdnSubject_list();

        $this->view->assign('course_category_list', $list['course']);
        $this->view->assign('subject_category_list', $list['subject']);
        $this->view->assign('teacher_list', $list['teacher_list']);
    }

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
    //
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


    /**
     * 编辑
     */
    public function edit($ids = NULL)
    {
        $course_id = $this->request->param('course_id');
        $ids = $course_id ? $course_id : $ids;
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
        $this->view->assign("course_id", $course_id ? $course_id : 0);
        $this->view->assign("row", $row);
        return $this->view->fetch();
    }

    /**
     * 评论
     * @param int ids
     * @return mixed
     */
    public function discuss($ids = null)
    {
        //当前是否为关联查询
        $this->relationSearch = true;
        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {
            $id = $this->request->param('id');
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();

            $total = $this->course_comment_model
                ->with(['course'])
                ->where($where)
                ->where(['course_id' => $id])
                ->order($sort, $order)
                ->count();

            $list = $this->course_comment_model
                ->with(['course' => function ($query) {
                    $query->withField('id,name');
                }])
                ->where($where)
                ->where(['course_id' => $id])
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();

            $list = collection($list)->toArray();
            $result = array("total" => $total, "rows" => $list);
            return json($result);
        }
        $this->assign('ids', $ids ? $ids : 0);
        return $this->view->fetch();
    }

    /**
     * 删除评论
     */
    public function del($ids = "")
    {
        if ($ids) {
            $pk = $this->course_comment_model->getPk();
            $adminIds = $this->getDataLimitAdminIds();
            if (is_array($adminIds)) {
                $this->course_comment_model->where($this->dataLimitField, 'in', $adminIds);
            }
            $list = $this->course_comment_model->where($pk, 'in', $ids)->select();
            $count = 0;
            foreach ($list as $k => $v) {
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

    /**
     * 评论详情
     * @param null $ids
     * @return mixed
     */
    public function discussdetail($ids = null)
    {
        $row = $this->course_comment_model->get($ids);
        if (!$row)
            $this->error(__('No Results were found'));
        $adminIds = $this->getDataLimitAdminIds();
        if (is_array($adminIds)) {
            if (!in_array($row[$this->dataLimitField], $adminIds)) {
                $this->error(__('You have no permission'));
            }
        }
        $row['createtime'] = date('Y-m-d H:i:s', $row['createtime']);
        $this->view->assign("row", $row);
        return $this->view->fetch('discuss_detail');
    }
}
