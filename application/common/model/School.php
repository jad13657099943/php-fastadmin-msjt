<?php

namespace app\common\model;

use think\Model;


class School extends Model
{
    // 表名


    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';

    // 追加属性
    protected $append = [
    ];

    /*获取分页列表
     * @param  $where 查询条件
     * @param  $field 需要查询字段
     * @param  $order 排序字段
     * @param  $page 第几页
     * @param  $pagesize 每页几条数据
     * */

    public static function getPageList($where = [], $field = '*', $order = 'weigh asc', $page = 1, $pagesize = 10)
    {
        return self::where($where)->field($field)->order($order)->limit(($page - 1) * $pagesize, $pagesize)->select();
    }


    /*
     * 获取单条数据
     * @param  $where 查询条件
     * @param  $field 需要查询字段
     * @param  $order 排序字段
     */
    public static function find_data($where = [], $field = '*', $order = 'weigh asc')
    {
        return self::where($where)->field($field)->order($order)->find();
    }


    /**
     * 获取学校名称
     * @param $school_id
     * @return mixed
     */
    public static function getSchoolName($school_id){
        return self::where('id' , $school_id)->value('school_name');
    }

}