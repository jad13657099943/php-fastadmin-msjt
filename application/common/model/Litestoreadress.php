<?php

namespace app\common\model;

use think\Model;
use addons\litestore\model\Area as AddArea;

class Litestoreadress extends Model
{
    // 表名
    protected $name = 'litestore_adress';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;

    // 追加属性
//    protected $append = ['Area'];


    /** 获取单条数据
     * @param $where
     * @param $field
     * @param $order
     * @return array
     */
    public function getOneDate($where = array() , $field ='*', $order = 'isdefault desc'){
        $where['isdefault'] = !$where['isdefault'] ? array('neq' , '-1') : $where['isdefault'];
        return $this->where($where)->field($field)->order($order)->find();
    }

    /** 获取多条数据
     * @param $where
     * @param $field
     * @param $order
     * @return array
     */
    public function getWhereDates($where = array()  , $field ='*' , $order = 'isdefault desc'){
        $where['isdefault'] = array('neq' , '-1');
        return $this->where($where)->field($field)->order($order)->select();
    }


    /**
     * 添加
     * @param $data
     * @return bool|string
     */
    public function add_user($data)
    {

        $info = $this->insert($data);

        if ($info) {

            return $this->getLastInsID();
        }

        return false;
    }

    /**
     * 修改信息
     * @param array $where
     * @param array $data
     * @return bool
     */
    public function update_data($where = [],$data = [])
    {
        return $this->where($where)->update($data);
    }


    /**
     * 删除地址
     * @param $where
     * @return bool|mixed
     */
    public function delete_data($where)
    {
        if (empty($where)) {

            return false;
        }
        return $this->where($where)->delete();
    }


    /**
     * 取消默认地址
     * @param $id 不是这个id都取消默认地址
     * @param $uid 用户
     */
    public function cancel_default($uid , $id){
        $where['user_id'] = $uid;
        $where['address_id'] = array('neq' , $id);
        $where['isdefault'] = array('neq' , -1);

        if($this->where($where)->count()){
            $save['isdefault'] = 0;
            return $this->update_data($where , $save);
        }else
            return 1;

    }
    public function get_default($uid,$field)
    {
        $where['user_id'] = $uid;
        $where['isdefault'] = '1';
        return $this->where($where)->field($field)->find();
    }
}