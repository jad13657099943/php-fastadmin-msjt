<?php

namespace addons\cms\controller;

use think\Config;

/**
 * CMS首页控制器
 * Class Index
 * @package addons\cms\controller
 */
class Index extends Base
{
    public function index()
    {
        Config::set('cms.title', Config::get('cms.title') ? Config::get('cms.title') : __('Home'));
        return $this->view->fetch('/index');
    }

    public function get_index_list()
    {
        $this->view->engine->layout(false);
        $this->success("", "", $this->view->fetch('common/index_list'));
    }
}
