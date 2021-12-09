<?php

namespace app\api\controller;

use app\common\controller\Api;
use think\Db;

// 微淘
class Microscouring extends Api
{
    protected $noNeedLogin = ['article_list'];
    protected $noNeedRight = ['*'];

    public function _initialize()
    {
        parent::_initialize();
        $this->article = model('Cmsarchives');
        $this->user = model('User');
        $this->item = model('Litestoregoods');
        $this->comment = model('Comment');
        $this->message = model('Cmscomment');
        $this->config = model('Config');
        $this->like = model('Like'); //点赞表
    }

    /*
     * 微淘列表
     *@param views 浏览量
     *@param content 内容
     *
     */
    public function article_list()
    {
        $params = $this->request->request();
        !$params['page'] && $this->error('page不存在');
        !$params['pagesize'] && $this->error('pagesize不存在');
        
        $where = array();
        ($keyword = $params['keyword']) && $where['title'] = ['like', '%' . $keyword . '%'];
        $where['channel_id'] = '15';
        $list = $this->article->get_page_data($where, 'id,image,title,views,content,deletetime,createtime,channel_id', $params['page'], $params['pagesize'], 'createtime desc');
        $list = empty($list) ? [] : $list;
        foreach ($list as $k => $v) {

            $return = getImgs($v['image']);
            $list[$k]['video'] = $return['video'] ? config('item_url') .$return['video'] : '';
            $list[$k]['firstimages'] = $return['image'] ? config('item_url') .$return['image']: '';
            $list[$k]['content'] = strip_tags($v['content']);
            $list[$k]['avatar'] = config('item_url') . config('site.merchant_logo');
            $list[$k]['name'] = config('site.merchant_name');
            $list[$k]['image'] = empty($v['image']) ? '' : config('item_url') . $v['image'];
        }
        $this->success('获取成功', ['list' => $list]);
    }

    /*
     * 微淘详情
     * @param goods_id 商品id
     * @param article_id 文章id
     * @param views 浏览量
     * @param comments 评论量
     * @param goods_price 商品价格
     * @param line_price 划线价格
     */
    public function article_details()
    {
        $params = $this->request->request();
        $uid = $this->auth->id;
        !$uid && $this->error('请登录后操作');
        !$params['article_id'] && $this->error('文章id不存在');

        //增加浏览量
        $this->article->where(['id' => $params['article_id']])->setInc('views');
        //获取文章信息
        $field = 'id,image,title,views,content,createtime,likes,comments,goods_id,channel_id,status';
        $info = $this->article->find_data(['id' => $params['article_id']], $field);
        $return = getImgs($info['image']);
        $info['image'] = config('item_url') . $info['image'];
        $info['firstimages'] = $info['image'] ? config('item_url') .  $return['image']: '';

        //获取商家信息
        $shop_info['avatar'] =config('item_url') . config('site.merchant_logo');
        $shop_info['name'] = config('site.merchant_name');
        //判断用户是否点赞文章
        if ($info != null) {
            $map['user_id'] = $uid;
            $map['pid'] = $params['article_id'];
            $info['status'] = $this->like->where($map)->count() ? 1 : 2;
        }
        //商品信息
        if ($info['goods_id']) {
            $ids = explode(',', $info['goods_id']);
            $where['goods_id'] = ['IN', $ids];
//            $item_info['item_sku'] = model('Litestoregoodsspecrel')->select_spec_names($where)->toArray();
            $field = 'goods_id,goods_name,image,goods_price,line_price';
            $goods_list = $this->item->select_data($where, $field);
            $goods_list = $this->joinArrayImages($goods_list, 'image');
        }
        //判断精选评价是否点赞

        //获取文章精选评价
        $order = 'likes desc';
        $comment_field = 'id,content,user_id,createtime,likes,comments';
        $comment_list = $this->message->select_limit_data(['aid' => $params['article_id']], $comment_field, $order, '3');
        if ($comment_list) {
            foreach ($comment_list as $k1 => $v1) {
                $map['user_id'] = $uid;
                $map['pid'] = $v1['id'];
                $comment_list[$k1]['status'] = $this->like->where($map)->count() ? 1 : 2;
                $comment_list[$k1]['user_name'] = $this->user->where(['id' => $v1['user_id']])->value('username');
                $comment_list[$k1]['head'] = $this->user->getAvatar($v1['user_id']);
            }
        }
        $comment_list = empty($comment_list) ? [] : $comment_list;
        $goods_list = empty($goods_list) ? [] : $goods_list;
        $info = empty($info) ? [] : $info;
        $this->success('获取成功', ['shop_info' => $shop_info, 'info' => $info, 'goods_list' => $goods_list, 'comment_list' => $comment_list]);
    }

    /*
     * 发表留言
     * @param article_id  文章id
     * @param content 内容
     * @param channel_id 栏目id
     */
    public function leaving_message()
    {
        $uid = $this->auth->id;
        $params = $this->request->request();
        !$uid && $this->error('请登录后操作');
        !$params['article_id'] && $this->error('article_id不存在');
        !$params['channel_id'] && $this->error('channel_id不存在');

        $data = [
            'user_id' => $uid,
            'aid' => $params['article_id'],
            'pid' => $params['channel_id'],
            'content' => $params['content'],
            'createtime' => time(),
        ];
        $message_add = $this->message->add_data($data);
        $article_set = $this->article->where(['id' => $params['article_id']])->setInc('comments');
        if ($message_add && $article_set) {

            $this->success('发表成功');
        } else {
            $this->error('发表失败');
        }
    }

    /*
     * 点赞
     * @param article_id 文章ID
     * @param type 1)点赞  2）取消点赞
     * @param status 1)文章点赞 2)用户留言点赞
     */
    public function like()
    {
        $uid = $this->auth->id;
        $params = $this->request->request();
        !$uid && $this->error('请登录后操作');
        !$params['type'] && $this->error('type不存在');
        !$params['status'] && $this->error('status不存在');

        if ($params['status'] == 1) {
            //获取文章信息
            $info = $this->article->find_data(['id' => $params['article_id']], 'id');
            !$info && $this->error('文章不存在,禁止点赞');
            $where['pid'] = $params['article_id'];
            $where['status'] = 1;
        } else {
            //获取评论信息
            $info = $this->message->find_data(['id' => $params['message_id']], 'id');
            !$info && $this->error('评论不存在,禁止评论');
            $where['pid'] = $params['message_id'];
            $where['status'] = 2;
        }
        $where['user_id'] = $uid;
        switch ($params['type']) {
            case 1:
                //判断用户是否重复点赞
                $this->like->where($where)->count() && $this->error('不能重复点赞');
                $data = [
                    'user_id' => $uid,
                    'pid' => $where['pid'],
                    'status' => $where['status'],
                    'add_time' => time(),
                ];
                if ($this->like->add_data($data) != false) {
                    if ($params['status'] == 1) {
                        $set_like = $this->article->where(['id' => $params['article_id']])->setInc('likes');
                    } else {
                        $set_like = $this->message->where(['id' => $params['message_id']])->setInc('likes');
                    }
                }
                break;
            case 2:
                //判断用户是否点赞
                !$this->like->where($where)->count() && $this->error('没有点赞');
                if ($this->like->where($where)->delete()) {
                    if ($params['status'] == 1) {
                        $set_like = $this->article->where(['id' => $params['article_id']])->setDec('likes');
                    } else {
                        $set_like = $this->message->where(['id' => $params['message_id']])->setDec('likes');
                    }
                }
                break;
        }
        if ($set_like) {
            $this->success('操作成功');
        } else {
            $this->error('操作失败');
        }
    }

    /*
     * 查看全部留言
     * @params likes 点赞数
     * @params avatar 头像
     * @params content 留言内容
     */
    public function get_all_message()
    {
        $uid = $this->auth->id;
        $params = $this->request->request();
        !$uid && $this->error('请登录后操作');
        !$params['page'] && $this->error('page不存在');
        !$params['pagesize'] && $this->error('pagesize不存在');
        !$params['article_id'] && $this->error('article_id不存在');

        //获取留言列表
        $field = 'id,user_id,content,createtime,likes';
        $message_list = $this->message->select_page(['aid' => $params['article_id']], $field, 'createtime desc', $params['page'], $params['pagesize']);
        if ($message_list) {
            foreach ($message_list as $k => $v) {
                $map['user_id'] = $uid;
                $map['pid'] = $v['id'];
                $message_list[$k]['status'] = $this->like->where($map)->count() ? 1 : 2;
                $message_list[$k]['avatar'] = $this->user->getAvatar($v['user_id']);
                $message_list[$k]['user_name'] = $this->user->getField(['id' => $v['user_id']], 'username');
            }
        }
        $this->success('获取成功', ['list' => $message_list]);
    }
}