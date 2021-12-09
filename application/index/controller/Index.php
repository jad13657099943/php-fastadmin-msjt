<?php

namespace app\index\controller;

use app\common\controller\Frontend;
use app\common\model\Version;

class Index extends Frontend
{

    protected $noNeedLogin = ['*'];
    protected $noNeedRight = '*';
    protected $layout = '';

    public function _initialize()
    {
        parent::_initialize();
    }

    public function index()
    {
        return $this->view->fetch();
    }

    public function about()
    {
        return $this->view->fetch();
    }

    public function policy()
    {
        return $this->view->fetch();
    }

    public function news()
    {
        $newslist = [];
        return jsonp(['newslist' => $newslist, 'new' => count($newslist), 'url' => 'https://www.fastadmin.net?ref=news']);
    }


    public function download()
    {
        $code = $this->request->request('code');
        $code = empty($code) ? 0 : $code;
        $this->assign('code', $code);
        return $this->view->fetch();
    }

    /**
     * 获取下载地址
     */
    public function getDownloadUrl()
    {
        $download = [
            'ios' => config('site.ios_download'),
            'android' => Version::getDownloadUrl()
        ];
        $this->success('获取成功', '', $download);
    }
}
