<?php

namespace app\admin\model\msjt\goods;

use think\Model;

class Curriculum extends Model
{
    // 表名
    protected $name = 'msjt_goods_curriculum';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    
    // 追加属性
    protected $append = [
        'freedate_text',
        'statedata_text',
        'status_text',
        'is_recommend_data_text',
        'is_hot_text'
    ];
    

    protected static function init()
    {
        self::afterInsert(function ($row) {
            $pk = $row->getPk();
            $row->getQuery()->where($pk, $row[$pk])->update(['weigh' => $row[$pk]]);
        });
    }

    
    public function getFreedateList()
    {
        return ['1' => __('Freedate 1'),'2' => __('Freedate 2')];
    }     

    public function getStatedataList()
    {
        return ['1' => __('Statedata 1'),'2' => __('Statedata 2')];
    }     

    public function getStatusList()
    {
        return ['1' => __('Status 1'),'2' => __('Status 2')];
    }
    public function getIsRecommendDataList()
    {
        return ['1' => __('Is_recommend_data 1'),'2' => __('Is_recommend_data 2')];
    }

    public function getIsHotList()
    {
        return ['1' => __('是'),'2' => __('否')];
    }
    public function getFreedateTextAttr($value, $data)
    {        
        $value = $value ? $value : (isset($data['freedate']) ? $data['freedate'] : '');
        $list = $this->getFreedateList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getStatedataTextAttr($value, $data)
    {        
        $value = $value ? $value : (isset($data['statedata']) ? $data['statedata'] : '');
        $list = $this->getStatedataList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getStatusTextAttr($value, $data)
    {        
        $value = $value ? $value : (isset($data['status']) ? $data['status'] : '');
        $list = $this->getStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    public function getIsRecommendDataTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['is_recommend_data']) ? $data['is_recommend_data'] : '');
        $list = $this->getIsRecommendDataList();
        return isset($list[$value]) ? $list[$value] : '';
    }
    public function getIsHotTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['is_hot']) ? $data['is_hot'] : '');
        $list = $this->getIsHotList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    public function type(){
        return $this->hasOne(\app\admin\model\msjt\goods\curriculum\Type::class,'id','type_id')->field('id,name');
    }

}
