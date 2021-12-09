<?php

namespace app\api\controller;

use app\common\controller\Api;
use think\Db;

class Evaluate extends Api
{
    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];

    public function _initialize()
    {
        parent::_initialize();
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
        !$params['order_id'] && $this->error('order_id不存在');
        $goods_info = $this->order_goods->where(['order_id' => $params['order_id']])->field('goods_id,goods_name,images')->select();
        $goods_info = empty($goods_info) ? [] : $goods_info;
        if ($goods_info) {
            foreach ($goods_info as $k => $v) {
                $goods_info[$k]['images'] = config('item_url') . $v['images'];
            }
        }
        $this->success('获取成功', ['info' => $goods_info]);


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
    public function addevaluate($orderId = '', $user_id = '', $status='')
    {
        $params = $this->request->except('token');
        $uid = !$status ? $this->auth->id : $user_id;
        $order_id = !$status ? $params['order_id'] : $orderId;
        $default_content = config('DEFAULT_COMMENT');
        $num = mt_rand(0,count($default_content)-1);
        $params['content'] = !$status ? $params['content'] : $default_content[$num];
        $params['order_id'] = $order_id;

        //获取用户信息
        $user_info = $this->user->getUserInfo(['id' => $uid], 'id,username user_name');
        $user_info['avatar'] = $this->user->getAvatar($uid);
        //获取商品信息
        $goods_info = $this->order_goods->select_data(['order_id' => $order_id], 'goods_id,goods_name,images,key_name goods_sku');

        Db::startTrans();
        if ($goods_info) {
            foreach ($goods_info as $k => $v) {
                $data = [
                    'images' => empty($params['image']) ? '' : $params['image'],
                    'goods_image' => $v['image'],
                    'user_head' => $user_info['avatar'],
                    'add_time' => time(),
                    'uid' => $uid,
                ];
                $add_r = array_merge($params, $user_info->toArray(), $v->toArray(), $data);
                if ($add_r) {
                    unset($add_r['image']);
                    unset($add_r['url']);
                    unset($add_r['avatar']);
                    unset($add_r['username']);
                    unset($add_r['id']);
                    $add = $this->comment->allowField(true)->save($add_r);
                }
            }
        }
        $save = ['order_status' => 50];
        $this->order->update_data(['id' => $order_id], $save);
        if ($add) {
            Db::commit();
            $this->success('发表成功');
        } else {
            Db::rollback();
            $this->error('发表失败');
        }

    }

    //上传图片
    public function upload()
    {
        $config = [
            'size' => 2097152,
            'ext' => 'jpg,gif,png,bmp,txt,zip'
        ];
        $file = $this->request->file('file');
        $upload_path = str_replace('\\', '/', ROOT_PATH . 'public/uploads');
        $save_path = '/uploads/';
        $info = $file->validate($config)->move($upload_path);

        if ($info) {
            $result = [
                'error' => 0,
                'url' => str_replace('\\', '/', $save_path . $info->getSaveName())
            ];
        } else {
            $result = [
                'error' => 1,
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
        $field = 'id,user_head,user_name,add_time,content,goods_id,star_num,images,order_id';
        $list = $this->comment->select_page(['uid' => $uid], $field, 'add_time desc', $params['page'], $params['pagesize']);
        if ($list) {
            foreach ($list as $k => $v) {
                $goods_info = $this->order_goods->find_data(['order_id' => $v['order_id']], 'goods_id,goods_name,images,goods_price,key_name');
                $list[$k]['user_head'] = config('item_url') . $this->user->getField(['id' => $uid], 'avatar');
                $list[$k]['images'] = $this->setPlitJointImages($v['images']);
                $list[$k]['image'] = empty($goods_info['images']) ? '' : config('item_url') . $goods_info['images'];
                $list[$k]['goods_name'] = $goods_info['goods_name'];
                $list[$k]['goods_price'] = $goods_info['goods_price'];
                $list[$k]['key_name'] = $goods_info['key_name'];
            }
            $list = empty($list) ? [] : $list;
        }
        $this->success('获取成功', ['list' => $list]);
    }
}