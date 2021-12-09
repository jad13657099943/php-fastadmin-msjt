<?php

namespace app\admin\model\litestore\goods;

use think\Model;

class Status extends Model
{
    // 表名
    protected $name = 'litestore_goods_status';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';

    // 追加属性
    protected $append = [
        'deletetime_text'
    ];


    /**
     * 查询数据
     * @param array $where
     * @param string $field
     * @return array
     */
    public function select_data($where = [], $field = '*')
    {
        return $this->where($where)->column($field);
    }


    public function getDeletetimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['deletetime']) ? $data['deletetime'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    protected function setDeletetimeAttr($value)
    {
        return $value && !is_numeric($value) ? strtotime($value) : $value;
    }


}
