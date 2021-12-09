<?php

namespace app\common\model;

use think\Cache;
use think\Model;

/**
 * 限时折扣模型
 */
class Cutdowrecord extends Model
{
    protected $name = 'cut_down_record';

    /*
     * 增加数据
     */
    public function add_data($data)
    {
        return $this->insert($data);
    }

    /*
     * 修改数据
     */
    public function update_data($where = [], $data)
    {
        return $this->where($where)->update($data);
    }
}

