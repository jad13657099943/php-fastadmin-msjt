<?php

namespace app\common\model;

use think\Model;

class Jpushlog extends Model
{

    // 表名
    protected $name = 'jpush_log';
    // 开启自动写入时间戳字段
   /* protected $autoWriteTimestamp = 'datetime';
    protected $dateFormat = 'Y-m-d H:i:s';*/
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;


    /**
  * 添加数据
  * @param  $param 查询条件
  */
    public function add_data($param)
    {
        return $this->allowField(true)->save($param);
    }

    /**
     * 修改数据
     */
    public function update_data($where=array() ,$data)
    {
        return $this->where($where)->update($data);
    }

    /**
     * 查询多条列表
     */
    public function getPageDate($where = array(), $field ='*' , $order = 'id desc', $page = 1, $pagesize = 10){
        $list =  $this->where($where)->field($field)->order($order)
            ->limit(($page-1)*$pagesize, $pagesize)->select();
        if ($list){
            foreach ($list as $k => $v){
                $list[$k]['image'] = config('item_url') . $v['image'];
            }
        }
        return $list;
    }
}