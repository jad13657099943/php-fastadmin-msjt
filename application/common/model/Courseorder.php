<?php

namespace app\common\model;

use think\Model;
use traits\model\SoftDelete;

class Courseorder extends Model
{

    use SoftDelete;


    // 表名
    protected $name = 'course_order';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = 'deletetime';

    // 追加属性
    protected $append = [
        'type_text',
        'zf_type_text',
        'order_status_text'
    ];


    public function getTypeList()
    {
        return [' 0' => __('Type  0'), '1' => __('Type 1')];
    }

    public function getZfTypeList()
    {
        return ['10' => __('Zf_type 10'), '20' => __('Zf_type 20'), '30' => __('Zf_type 30')];
    }

    public function getOrderStatusList()
    {
        return [' 0' => __('Order_status  0'), '10' => __('Order_status 10'), '20' => __('Order_status 20')];
    }


    public function getTypeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['type']) ? $data['type'] : '');
        $list = $this->getTypeList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getZfTypeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['zf_type']) ? $data['zf_type'] : '');
        $list = $this->getZfTypeList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getOrderStatusTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['order_status']) ? $data['order_status'] : '');
        $list = $this->getOrderStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    /**
     * 关联课程分类表
     * @return mixed
     */
    public function coursecateaory()
    {
        return $this
            ->belongsTo('coursecategory', 'course_category_id')
            ->setEagerlyType(0);
    }

    /**
     * 关联学段年级表
     * @return mixed
     */
    public function subjectcateaory()
    {
        return $this
            ->belongsTo('dansubjectcategory', 'subject_category_id')
            ->setEagerlyType(0);
    }

    /**
     * 关联教师表
     * @return mixed
     */
    public function teacher()
    {
        return $this
            ->belongsTo('teacher', 'teacher_id')
            ->setEagerlyType(0);
    }

    /**
     * 关联课程表
     * @return mixed
     */
    public function course()
    {
        return $this->belongsTo('course', 'course_id')
            ->setEagerlyType(0);
    }


}
