<?php

namespace app\api\controller;

use app\common\controller\Api;
use think\Db;

class Litestoreordergoods extends Api
{
    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];

    public function _initialize()
    {
        parent::_initialize();
//        $this->address = model('Comment');
        $this->item = model('Litestoregoods');
        $this->user = model('User');
        $this->order = model('Litestoreorder');
        $this->order_goods = model('Litestoreordergoods');
        $this->comment = model('Comment');

    }

    /*
     * 获取发表评价页面
     * @param goods_id 商品id
     * @param order_id 订单id
     */
    public function getAddEvaluate()
    {
        $params = $this->request->request();
        $uid = $this->auth->id;
        !$uid && $this->request->request();
        !$params['goods_id'] && $this->error('goods_id不存在');

        $goods_info = $this->item->find_data(['goods_id' => $params['goods_id']],'goods_id,goods_name,images');
        $goods_info['images'] = config('items_url').$goods_info['images'];
        if ($goods_info == null){
            $goods_info = [];
        }
        $this->success('获取成功',['info' => $goods_info]);


    }

    /*
     * 发表评价
     * @param uid 用户id
     * @param goods_id 商品id
     * @param order_id 订单id
     * @param image 评论图片
     * @param content 评论内容
     *
     */
    public function addevaluate()
    {
        $params = $this->request->request();
        $uid = $this->auth->id;
        !$uid && $this->error('请登录后操作');
        !$params['order_id'] && $this->error('订单信息不存在');
        //获取商品id
        $goods_id = $this->order_goods->where(['order_id' => $params['order_id']])->value('goods_id');
        //获取用户信息
        $user_info = $this->user->getUserInfo(['id' => $uid],'id,nickname,avatar');

        $data = [
             'images' => empty($params['image']) ? json_encode([]) : json_encode($params['image']),
//            'image' => empty($data['image']) ?　json_encode([]) : json_encode($data['image']),
            'user_name' => $user_info['nickname'],
            'user_head' => $user_info['avatar'],
            'add_time' => time(),
            'goods_id' => $goods_id,
            'content' => $params['content'],
            'star_num' => $params['star_num'],
            'order_id' => $params['order_id'],
            'uid' => $uid,
        ];
        Db::startTrans();
            //评价表里添加数据
            $add = $this->comment->insert($data);
            if ($add){
                $save = [
                    'order_status' => 50,
                ];
                Db::commit();
                $save_info  = $this->order->update_data(['id' => $data['order_id']],$save);
                $this->success('发表成功',$data);
            }
            if ($save_info == false){
                Db::rollback();
                $this->error('发表失败');
            }


    }
    //上传图片
    public function upload()
    {
        $config = [
            'size' => 2097152,
            'ext'  => 'jpg,gif,png,bmp,txt,zip'
        ];
        $file = $this->request->file('file');
        $upload_path = str_replace('\\', '/', ROOT_PATH . 'public/uploads');
        $save_path   = '/uploads/';
        $info = $file->validate($config)->move($upload_path);

        if ($info) {
            $result = [
                'error' => 0,
                'url'   => str_replace('\\', '/', $save_path . $info->getSaveName())
            ];
        } else {
            $result = [
                'error'   => 1,
                'message' => $file->getError()
            ];
        }

        return json($result);
    }

    /*
     * 我的评论
     * @param image 商品图片
     * @param goods_name 商品名称
     * @param goods_price 商品价格
     */
    public function getMyevaluate()
    {
        $params = $this->request->request();
        $uid = $this->auth->id;
        !$uid && $this->error('请登录后操作');
        //获取评论信息
        $field = 'id,user_head,user_name,add_time,content,goods_id';
        $list = $this->comment->select_page(['uid' => $uid],$field,'add_time desc',$params['page'],$params['pagesize']);
        if ($list){
            foreach ($list as $k => $v){
                $goods_info = $this->item->find_data(['goods_id' => $v['goods_id']],'image,goods_name,goods_price');
                $list[$k]['image'] = config('items_url').$goods_info['image'];
                $list[$k]['goods_name'] = $goods_info['goods_name'];
                $list[$k]['goods_price'] = $goods_info['goods_price'];
            }
        }
    }
}