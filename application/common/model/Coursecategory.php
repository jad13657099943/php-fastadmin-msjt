<?php

namespace app\common\model;

use think\Model;

class Coursecategory extends Model
{
    // 表名
    protected $name = 'Course_category';

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
        self::afterInsert(function ($row) {
            $pk = $row->getPk();
            $row->getQuery()->where($pk, $row[$pk])->update(['weigh' => $row[$pk]]);
        });
    }

    /**
     * 读取栏目分类列表
     */
    public function getLitestoreCategoryList($where, $field = '*')
    {
        return $this->field($field)->where($where)->order('weigh desc')->select();
    }

    /**
     * 生成spid
     * @param $pid
     * @return int|string
     */
    public function get_spid($pid)
    {
        if (!$pid) {
            return 0;
        }
        $pspid = $this->where(array('id' => $pid))->value('spid');
        if ($pspid) {
            $spid = $pspid . $pid . '|';
        } else {
            $spid = $pid . '|';
        }
        return $spid;
    }

    /**
     * 获取分类下面的所有子分类的ID集合
     *
     * @param int $id
     * @param bool $with_self
     * @return array $array
     */
    public function get_child_ids($id, $with_self=false) {
        $spid = $this->where(array('id'=>$id))->value('spid');
        $spid = $spid ? $spid .= $id .'|' : $id .'|';
        $id_arr = $this->field('id')->where(array('spid'=>array('like', $spid.'%')))->select();
        $array = array();
        foreach ($id_arr as $val) {
            $array[] = $val['id'];
        }
        $with_self && $array[] = $id;
        return $array;
    }
}
