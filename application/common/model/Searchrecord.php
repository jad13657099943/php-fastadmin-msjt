<?php

namespace app\common\model;

use think\Model;

/**
 * 配置模型
 */
class Searchrecord extends Model
{
    // 表名,不含前缀
    protected $name = 'search_record';

    protected $autoWriteTimestamp = false;

    public function search($keyword, $uid)
    {
        $data = ['uid' => $uid, 'search_name' => $keyword];
        $search = $this->where($data)->find();
        if ($search) {
            ++$search->ordid;
            $search->save();
        } else {
            $data['ordid'] = 1;
            $data['add_time'] = time();
            $this->save($data);
        }
    }

}