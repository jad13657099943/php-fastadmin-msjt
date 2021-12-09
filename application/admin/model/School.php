<?php

namespace app\admin\model;


use app\admin\controller\auth\Admin as AdminContr;
use think\Model;

class School extends Model
{
    // 表名
    protected $name = 'school';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    
    // 追加属性
    protected $append = [

    ];
    

    protected static function init()
    {
        //添加之后
        self::afterInsert(function ($row) {
            $pk = $row->getPk();
            $row->getQuery()->where($pk, $row[$pk])->update(['weigh' => $row[$pk]]);
//            $admin = new AdminContr();
//            $admin->add($row);
        });


//        //修改之前
//        self::beforeUpdate(function ($row) {
//            $admin = new AdminContr();
//            $admin->edit($row['id']);
//        });
    }

    







}
