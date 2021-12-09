<?php

namespace app\common\model;

use think\Model;


class Teacher extends Model
{


    // 表名
    protected $name = 'teacher';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'jointime_text',
        'delete_time_text'
    ];


    public function getJointimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['jointime']) ? $data['jointime'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getDeleteTimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['delete_time']) ? $data['delete_time'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    protected function setJointimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    protected function setDeleteTimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    /**
     * 关联课程分类表
     * @return mixed
     */
    public function coursecateaory()
    {
        return $this
            ->belongsTo('course_category', 'course_category_id')
            ->setEagerlyType(0);
    }

    /**
     * 关联学段年级表
     * @return mixed
     */
    public function subjectcateaory()
    {
        return $this
            ->belongsTo('dan_subject_category', 'subject_category_id')
            ->setEagerlyType(0);
    }

}
