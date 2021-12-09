<?php

namespace app\admin\model;

use think\Model;

class Searchrecord extends Model
{
    // 表名
    protected $name = 'search_record';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = true;

    // 定义时间戳字段名
    protected $createTime = 'add_time';
    protected $updateTime = 'add_time';

    public function user()
    {
        return $this->belongsTo('User', 'uid');
    }

}
