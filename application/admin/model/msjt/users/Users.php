<?php

namespace app\admin\model\msjt\users;

use think\Model;

class Users extends Model
{
    // 表名
    protected $name = 'msjt_users_users';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    
    // 追加属性
    protected $append = [
        'grade_text',
        'dai_text'
    ];
    

    
    public function getGradeList()
    {
        return ['1' => __('Grade 1'),'2' => __('Grade 2')];
    }     

    public function getDaiList()
    {
        return ['1' => __('Dai 1'),'2' => __('Dai 2')];
    }     


    public function getGradeTextAttr($value, $data)
    {        
        $value = $value ? $value : (isset($data['grade']) ? $data['grade'] : '');
        $list = $this->getGradeList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getDaiTextAttr($value, $data)
    {        
        $value = $value ? $value : (isset($data['dai']) ? $data['dai'] : '');
        $list = $this->getDaiList();
        return isset($list[$value]) ? $list[$value] : '';
    }




}
