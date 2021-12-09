<?php

namespace app\common\model;

use think\Model;

class Coursecomment extends Model
{
    // 表名
    protected $name = 'Course_comment';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';

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
