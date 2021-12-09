<?php

namespace app\api\controller;

use app\common\controller\Api;
use think\Db;

class Message extends Api
{
    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];

    public function _initialize()
    {
        parent::_initialize();
        $this->message = model('Message');
        $this->user = model('User');
        $this->order = model('Litestoreorder');
        $this->integralorder = model('Integralorder');
    }

    /**
     * 提交意见反馈
     */
    public function setFeedback()
    {
        $data = $this->request->request();
        $uid = $this->auth->id;
        empty($data['content'])&&$this->error('content不能为空');

        //获取用户信息
        $member_info = $this->user->getUserInfo(['id' => $uid], 'id,username,mobile');
        $add_r = [
            'nickname' => $member_info['username'],
            'add_time' => time(),
            'user_id' => $uid,
            'content' => $data['content'],
            'mobile' => $member_info['mobile'],
        ];

        if ($this->message->insert($add_r)) {
            $this->success('反馈成功');
        } else {
            $this->error('反馈失败');
        }
    }

    /**
     * 消息中心
     * @param type 1）订单消息 2）系统消息
     */
    public function message_center()
    {
        $params = $this->request->request();
        !$params['type'] && $this->error('type不存在');
        !$params['page'] && $this->error('page不存在');
        !$params['pagesize'] && $this->error('pagesize不存在');
        $uid = $this->auth->id;
        $uid = 16;

        switch ($params['type']){
            case 1:
                $where['user_id'] = $uid;
                $field = 'id,is_look,createtime,type,image,order_id,title,order_no';
                $list = model('Jpushlog')->getPageDate($where,$field,'createtime desc',$params['page'],$params['pagesize']);
                break;
            case 2:
                $where['channel_id'] = 15;
                $field = 'id,title,createtime,read_uid,image,content';
                $list = model('Cmsarchives')->get_page_data($where,$field,$params['page'],$params['pagesize']);
                foreach ($list as $k => $v) {
                    $ids = explode(',', $v['read_uid']);
                    if ($ids) {
                        $list[$k]['content'] = strip_tags($v['content']);
                        $list[$k]['image'] = config('item_url') . $v['image'];
                        $list[$k]['is_look'] = in_array($uid, $ids) ? 2 : 1 ;
                    }
                }
                break;
        }
        $this->success('成功',['list' => $list]);
    }

    /**
     * 系统消息详情
     * @param status 0）未读 1）已读
     * @param article_id 文章id
     * @param uid 用户id
     * read_uid 已读用户id
     */
    public function system_details()
    {
        $params = $this->request->request();
        $uid = $this->auth->id;
        !$uid && $this->error('请登录后操作');
        !$params['article_id'] && $this->error('article_id不存在');

        $field = 'id,title,createtime,read_uid,image,content';
        $article_info = model('Cmsarchives')->find_data(['id' => $params['article_id']],$field)->toArray();
        $article_info['image'] = config('item_url') . $article_info['image'];

        if ($uid){
            //判断用户是否已读
            $ids = explode(',' , $article_info['read_uid']);
            if (in_array($uid , $ids)) {
                $article_info['status'] = 1; // 0）未读 1）已读
            }
            if (empty($article_info['read_uid'])){
                model('Cmsarchives')->edit_data(['id' => $params['article_id']],['read_uid' => $uid]);
            }else{
                $str = '';
                $str = $uid.",".$article_info['read_uid'];
                model('Cmsarchives')->edit_data(['id' => $params['article_id']],['read_uid' => $str]);
            }
        }
        $this->success('成功' ,$article_info);
    }

    /**
     * 消息中心
     */
    public function messageCenter()
    {
        $params = $this->request->request();
        $uid = $this->auth->id;

        $model = controller('Mycenter');
        $cout_list = $model->messages_count($uid);
        //获取消息 = 订单消息 + 系统消息
        $order_count = $cout_list['order_count']; //订单消息
        $system_message_count = $cout_list['no_read'] == 0 ? 0 : $cout_list['no_read']; //系统消息
        $messages_count = $order_count + $system_message_count;

        $this->success('success',
            [
                'order_count' => $order_count, //订单数量
                'system_message_count' => $system_message_count, //系统消息
                'messages_count' => $messages_count,//消息数量
            ]);

    }
}
