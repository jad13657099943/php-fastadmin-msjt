<?php

namespace app\api\controller;

use app\common\controller\Api;
use think\Config;
use think\Db;
use app\common\model\Cmsarchives;

class Article extends Api
{
    protected $noNeedLogin = ['article_list','register_info','news','newsDetails'];
    protected $noNeedRight = ['*'];

    public function _initialize()
    {
        parent::_initialize();
        $this->article = model('Cmsarchives');
        $this->collect = model('Collect');
    }

    /*
     * 牛奶知识
     * @param description 描述
     * @param channel_id 栏目id
     */
    public function article_list()
    {
        $params = $this->request->request();
        !$params['page'] && $this->error('page不存在');
        !$params['pagesize'] && $this->error('pagesize不存在');

        $field = 'id,image,title,content';
        $article_list = $this->article->get_page_data(['channel_id' => 9], $field, $params['page'], $params['pagesize'], 'createtime desc');
        $article_list = empty($article_list) ? [] : $this->article->set_url_img($article_list);
        foreach ($article_list as $k => $v){
            $article_list[$k]['content'] = strip_tags($v['content']);
            $article_list[$k]['image'] =  Config('item_url').$v['image'];
        }
//        $article_list = [];
        $this->success('获取成功', ['list' => $article_list]);
    }

    /*
     * 详情
     *
     */
    public function article_details()
    {
        $params = $this->request->request();
        !$params['article_id'] && $this->error('article_id不存在');
        //判断文章是否收藏
        $uid = $this->auth->id;
        $status = 0;
        if ($uid) {
            $where = ['article_id' => $params['article_id'], 'uid' => $uid];
            if ($this->collect->where($where)->count())
                $status = 1;
        }
        $article_info = $this->article->find_data(['id' => $params['article_id']], 'id,content');
        $this->success('获取成功', ['info' => $article_info, 'status' => $status]);
    }

    /**
     * 注册协议
     */
    public function register_info()
    {
        $info = $this->article->where(['id' => 34])->value('content');
        if ($info)
            $this->success('成功' , ['info' => $info]);
    }

    /**
     * 新手指南列表
     * @return mixed
     */
    public function news()
    {
        $news = $this->article->get_page_data(['channel_id' => 26],'id,title' ,$this->request->request('page'),$this->request->request('pagesize'),'createtime desc');
        $list = $news ? $news : [];
        return $this->success('success',['list' => $list]);
    }

    public function newsDetails()
    {
//        $id = $this->request->request('article_id');
//        $details = Cmsarchives::get(function ($query)use ($id) {
//            $query->where('id',$id)->field('id,title,createtime,content');
//        });
//        return $this->success('success',['info' => $details]);
        $article_to= \app\common\model\Config::where('name','Noticeto_users')->find();
        $article=$article_to->value;
        return $this->success('success',['info' => $article]);
    }

}