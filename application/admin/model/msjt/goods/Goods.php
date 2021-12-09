<?php

namespace app\admin\model\msjt\goods;

use think\Model;

class Goods extends Model
{
    // 表名
    protected $name = 'msjt_goods_goods';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    
    // 追加属性
    protected $append = [
        'is_recommend_data_text',
        'status_text',

    ];
    

    protected static function init()
    {
        self::afterInsert(function ($row) {
            $pk = $row->getPk();
            $row->getQuery()->where($pk, $row[$pk])->update(['weigh' => $row[$pk]]);
        });
    }

    public function getIsRecommendDataList()
    {
        return ['1' => __('Is_recommend_data 1'),'2' => __('Is_recommend_data 2')];
    }

    public function getStatusList()
    {
        return ['1' => __('Status 1'),'2' => __('Status 2')];
    }

    public function getTypesetList(){
        return ['1' => __('默认排版'),'2' => __('套盒排版')];
    }



    public function getIsRecommendDataTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['is_recommend_data']) ? $data['is_recommend_data'] : '');
        $list = $this->getIsRecommendDataList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    public function getStatusTextAttr($value, $data)
    {        
        $value = $value ? $value : (isset($data['status']) ? $data['status'] : '');
        $list = $this->getStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    public function getTypesetListTextAttr($value, $data){
        $value = $value ? $value : (isset($data['typesetting']) ? $data['typesetting'] : '');
        $list = $this->getTypesetList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    public function type(){
        return $this->hasOne(Type::class,'id','type_id')->field('id,name');
    }


}
