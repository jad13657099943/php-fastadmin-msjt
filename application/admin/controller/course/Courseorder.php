<?php

namespace app\admin\controller\course;

use app\common\controller\Backend;

/**
 * 课程订单管理
 *
 * @icon fa fa-circle-o
 */
class Courseorder extends Backend
{


    /**
     * Courseorder模型对象
     * @var \app\admin\model\Courseorder
     */
    protected $model = null;

    public function _initialize()
    {

        parent::_initialize();

        $this->model = model('Courseorder');

        //科目段位 课程分类 教师列表
        $list = courseAdnSubject_list();

        $this->view->assign('course_category_list', $list['course']);
        $this->view->assign('subject_category_list', $list['subject']);

        $this->view->assign("typeList", $this->model->getTypeList());
        $this->view->assign("zfTypeList", $this->model->getZfTypeList());
        $this->view->assign("orderStatusList", $this->model->getOrderStatusList());
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
                ->with(['course', 'coursecateaory', 'subjectcateaory', 'teacher'])
                ->where($where)
                ->order($sort, $order)
                ->count();

            $list = $this->model
                ->with(['course' => function ($query) {
                    $query->withField('id,name,video_type');
                }, 'coursecateaory' => function ($query) {
                    $query->withField('id,name');
                }, 'subjectcateaory' => function ($query) {
                    $query->withField('id,name');
                }, 'teacher' => function ($query) {
                    $query->withField('id,nickname');
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
     * 详情
     * @param null $ids
     * @return mixed
     */
    public function detail($ids = NULL)
    {
        $this->relationSearch = true;

        list($where) = $this->buildparams();

        $field = 'courseorder.id,order_on,uid,username,course_id,courseorder.subject_category_id,
        courseorder.course_category_id,courseorder.teacher_id,course_price,pay_price,
        remake,courseorder.createtime,courseorder.updatetime';
        $row = $this->model
            ->with(['course' => function ($query) {
                $query->withField('id,name,image');
            }, 'coursecateaory' => function ($query) {
                $query->withField('id,name');
            }, 'subjectcateaory' => function ($query) {
                $query->withField('id,name');
            }, 'teacher' => function ($query) {
                $query->withField('id,nickname');
            }])
            ->where($where)
            ->field($field)
            ->find();

        //时间搓 转换为 时间日期
        $row['createtime'] = date('Y-m-d H:i:s', $row['createtime']);
        $row['updatetime'] = date('Y-m-d H:i:s', $row['updatetime']);

        $this->view->assign("row", $row);
        return $this->view->fetch();
    }

}
