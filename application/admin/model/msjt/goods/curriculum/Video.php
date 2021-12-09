<?php

namespace app\admin\model\msjt\goods\curriculum;

use think\Model;

class Video extends Model
{
    // 表名
    protected $name = 'msjt_goods_curriculum_video';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    
    // 追加属性
    protected $append = [
        'freedate_text',
        'status_text',
        'isVipdate_text'
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

    public function getStatusList()
    {
        return ['1' => __('Status 1'),'2' => __('Status 2')];
    }     

    public function getIsvipdateList()
    {
        return ['1' => __('Isvipdate 1'),'2' => __('Isvipdate 2')];
    }     


    public function getFreedateTextAttr($value, $data)
    {        
        $value = $value ? $value : (isset($data['freedate']) ? $data['freedate'] : '');
        $list = $this->getFreedateList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getStatusTextAttr($value, $data)
    {        
        $value = $value ? $value : (isset($data['status']) ? $data['status'] : '');
        $list = $this->getStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getIsvipdateTextAttr($value, $data)
    {        
        $value = $value ? $value : (isset($data['isVipdate']) ? $data['isVipdate'] : '');
        $list = $this->getIsvipdateList();
        return isset($list[$value]) ? $list[$value] : '';
    }




}
