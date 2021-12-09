<?php

namespace app\common\model;

use think\Model;

class Litestorecategory extends Model
{
    // 表名
    protected $name = 'litestore_category';
    
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
    public function getLitestoreCategoryList($where,$field='*'){
        return $this->field($field)->where($where)->order('weigh desc')->select();
    }
     //只有前十条

    public function getLitestoreCategoryLists($where,$field='*'){
        return $this->field($field)->where($where)->order('weigh desc')->order("weigh desc,id desc")->limit(10)->select();
    }

    /**
     * 获取单条数据
     * @param $where
     * @param $field
     * @return array|false|\PDOStatement|string|Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function find_data($where,$field){
        return $this->field($field)->where($where)->order('weigh desc')->find();
    }

        /**
     * 读取栏目分类列表商品
     */
    public function lassificationnamelist($field='*'){
        return  $this->hasMany('Litestoregoods','category_id')->field($field);
    }

    /**
     * 生成spid
     *
     * @param int $pid 父级ID
     */
    public function get_spid($pid) {
        if (!$pid) {
            return 0;
        }
        $pspid = $this->where(array('id'=>$pid))->getField('spid');
        if ($pspid) {
            $spid = $pspid . $pid . '|';
        } else {
            $spid = $pid . '|';
        }
        return $spid;
    }







}
