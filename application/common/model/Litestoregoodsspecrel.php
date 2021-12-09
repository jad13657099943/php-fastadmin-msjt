<?php

namespace app\common\model;

use think\Db;
use think\Model;

class Litestoregoodsspecrel extends Model
{
    // 表名
    protected $name = 'litestore_goods_spec_rel';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'create_time';
    protected $updateTime = '';

    /**
     * 关联规格组
     * @return \think\model\relation\BelongsTo
     */
    public function spec()
    {
        return $this->belongsTo('Litestorespec');
    }

    public function specs()
    {
        return $this->belongsTo('Litestorespec', 'spec_id', 'id');
    }


    public function specValue()
    {   //this 模型关联ID
        return $this->belongsTo('Litestorespecvalue', 'spec_value_id', 'id');
    }


    /*
     * 获取模型集合 逗号隔开
     * */
    public function fieid_spec_names($where = [], $field = '*')
    {
        $ids = $this->where($where)->group('spec_id')->column($field);
        $spec_names = \model('Litestorespec')->where(['id' => ['IN', $ids]])->column('spec_name');
        return implode(',', $spec_names);
    }

    /*
   * 获取多规格信息
   * */
    public function select_spec_names($where = [] ,$key_name =null)
    {
        $list = $this->where($where)->group('spec_id')->
        field('goods_id,spec_id')->order('spec_value_id asc')->with('specs')->select()->toArray();


        if ($list != null) {
            foreach ($list as $k => $v) {
                $list[$k]['spec_name'] = $v['specs']['spec_name'];

                $where['spec_id'] = $v['spec_id'];
                $sub = $this->where($where)->with('specValue')->select()->toArray();

                $subs = [];
                if ($sub != null) {
                    foreach ($sub as $key => $value) {
                        unset($value['spec_value']['createtime'], $value['spec_value']['spec_id']);

                        if ($key_name && $list[$k]['spec_name'] == "学校") {
                            if ($value['spec_value']['spec_value'] == $key_name)
                                $subs[] = $value['spec_value'];
                        } else
                            $subs[] = $value['spec_value'];
                    }
                }
                $list[$k]['sub'] = $subs;
                unset($list[$k]['spec_id'], $list[$k]['specs'], $sub);
            }
        }
        return $list;
    }
}