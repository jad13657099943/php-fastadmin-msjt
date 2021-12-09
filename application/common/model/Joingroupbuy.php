<?php

namespace app\common\model;

use think\Model;

/**
 * 购物车模型
 */
class Joingroupbuy Extends Model
{

    // 表名
    protected $name = 'join_groupbuy';

    /*获取分页列表
     * @param  $where 查询条件
     * @param  $field 需要查询字段
     * @param  $order 排序字段
     * @param  $page 第几页
     * @param  $pagesize 每页几条数据
     * */

    public function getPageList($where = [], $field = '*', $order = 'id desc', $page = 1, $pagesize = 10,$uid ='')
    {
        $list =  $this->where($where)->field($field)
                 ->order($order)->limit(($page - 1) * $pagesize, $pagesize)
                 ->select();

        if($list !=null){
            foreach ($list as $k=>$v){
                $list[$k]['avatar'] = $v['avatar'] ? config('item_url').$v['avatar'] : '';
                if ($v['uid']){
                    $list[$k]['avatar'] = model('User')->getAvatar($v['uid']);
                }
                if ($uid) {
                    $list[$k]['join_status'] = $this->where(['pid' => $v['id'], 'uid' => $uid])->count() ? 1 : 0;
                }
                $list[$k]['miao'] = (($v['add_time'] + $v['hour']*3600) - time()) > 0 ? ($v['add_time'] + $v['hour']*3600) - time(): 0; //倒计时
                $list[$k]['need_number'] = ($v['group_num'] - $v['join_num']) > 0 ? $v['group_num'] - $v['join_num'] : 0;//还差人数
                if ($list[$k]['miao'] == 0){
                    //拼团状态失效
                    $this->where(['id' => $v['id']])->update(['type' => 3]);
                }
            }
        }
        return $list;
    }


    /*
     * 获取单条数据
     * @param  $where 查询条件
     * @param  $field 需要查询字段
     * @param  $order 排序字段
     */
    public function find_data($where = [], $field = '*', $order = 'id desc')
    {
        return $this->where($where)->field($field)->order($order)->find();
    }

    /*
     * 查询多条数据
     * @param  $where 查询条件
     * @param  $field 需要查询字段
     * @param  $order 排序字段
     */
    public function select_data($where, $field = '*', $order = 'id desc')
    {
        return $this->where($where)->field($field)->order($order)->select();
    }


    /**
     * 添加数据
     * @param  $param 查询条件
     */
    public function add_data($param)
    {
        $this->allowField(true)->save($param);
        return $this->getLastInsID();
    }

    /**
     * 修改信息
     * @param array $where
     * @param array $data
     * @return bool
     */
    public function update_data($where = [],$data = [])
    {
        if (empty($where)) {
            return false;
        }
        return $this->where($where)->update($data);
    }


    /**
     * 删除
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

    /*
   * 统计购物车数量
   * @param $uid 用户ID
   * */
    public function getGroupbuyNum($where = []){
        return $this->where($where)->count();
    }

    //订单获取拼团商品的倒计时
    public function getMiao($where)
    {
        $info = $this->where($where)->field('add_time ,hour')->find();
        $info['miao'] = (($info['add_time'] + $info['hour']*3600) - time()) > 0 ? ($info['add_time'] + $info['hour']*3600) - time(): 0; //倒计时
        return $info['miao'];
    }

    public function select_page($where = [], $field = '*', $order = 'id desc', $page = 1, $pagesize = 10)
    {

        $list =  $this->where($where)->field($field)
            ->order($order)->limit(($page - 1) * $pagesize, $pagesize)
            ->select();

        if($list !=null){
            foreach ($list as $k=>$v){
                if ($v['uid']){
                    $list[$k]['avatar'] = model('User')->getAvatar($v['uid']);
                }
            }
        }
        return $list;
    }

}