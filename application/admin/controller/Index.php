<?php

namespace app\admin\controller;

use app\admin\model\AdminLog;
use app\common\controller\Backend;
use think\Config;
use think\Hook;
use think\Validate;

/**
 * 后台首页
 * @internal
 */
class Index extends Backend
{

    protected $noNeedLogin = ['login'];
    protected $noNeedRight = ['index', 'logout'];
    protected $layout = '';

    public function _initialize()
    {
        parent::_initialize();
    }

    /**
     * 后台首页
     */
    public function index()
    {
        $litestoreorder_model = model('Litestoreorder');
        $row = $litestoreorder_model->statisticsCount(20,$this->auth->school_id);//拼团订单数量
        $rows = $litestoreorder_model->statisticsCount(10 ,$this->auth->school_id);//商品订单数量
        $taocan = $litestoreorder_model->goodsCount(['type'=>['in','1,2'],'pay_status' => 20] ,$this->auth->school_id);//商品订单数量
        //左侧菜单

        list($menulist, $navlist, $fixedmenu, $referermenu) = $this->auth->getSidebar([
            'dashboard' => 'hot',
            'addon' => ['new', 'red', 'badge'],
            'auth/rule' => __('Menu'),
            'general' => ['new', 'purple'],
            'litestore/litestoreorderrefund' => [model('Litestoreorderrefund')->where('school_id' ,$this->auth->school_id)->count(),'red', 'badge'],
            'litestore/litestoreclusterorder' => [$row['total_number'],'blue', 'badge'],
            'litestore/litestoreorder' => [$rows['total_number'] ,'green', 'badge'],
            'litestore/comboorder' => [$taocan ,'purple', 'badge'],
        ], $this->view->site['fixedpage']);
        $action = $this->request->request('action');
        if ($this->request->isPost()) {
            if ($action == 'refreshmenu') {
                $this->success('', null, ['menulist' => $menulist, 'navlist' => $navlist]);
            }
        }
        $this->view->assign('menulist', $menulist);
        $this->view->assign('navlist', $navlist);
        $this->view->assign('fixedmenu', $fixedmenu);
        $this->view->assign('referermenu', $referermenu);
        $this->view->assign('title', __('Home'));
        return $this->view->fetch();
    }

    /**
     * 管理员登录
     */
    public function login()
    {
        $url = $this->request->get('url', 'index/index');
        if ($this->auth->isLogin()) {
            $this->success(__("You've logged in, do not login again"), $url);
        }
        if ($this->request->isPost()) {
            $username = $this->request->post('username');
            $password = $this->request->post('password');
            $keeplogin = $this->request->post('keeplogin');
            $token = $this->request->post('__token__');
            $rule = [
                'username' => 'require|length:3,30',
                'password' => 'require|length:3,30',
                '__token__' => 'token',
            ];
            $data = [
                'username' => $username,
                'password' => $password,
                '__token__' => $token,
            ];
            if (Config::get('fastadmin.login_captcha')) {
                $rule['captcha'] = 'require|captcha';
                $data['captcha'] = $this->request->post('captcha');
            }
            $validate = new Validate($rule, [], ['username' => __('Username'), 'password' => __('Password'), 'captcha' => __('Captcha')]);
            $result = $validate->check($data);
            if (!$result) {
                $this->error($validate->getError(), $url, ['token' => $this->request->token()]);
            }
            AdminLog::setTitle(__('Login'));
            $result = $this->auth->login($username, $password, $keeplogin ? 86400 : 0);
            if ($result === true) {
                Hook::listen("admin_login_after", $this->request);
                $this->success(__('Login successful'), $url, ['url' => $url, 'id' => $this->auth->id, 'username' => $username, 'avatar' => $this->auth->avatar]);
            } else {
                $msg = $this->auth->getError();
                $msg = $msg ? $msg : __('Username or password is incorrect');
                $this->error($msg, $url, ['token' => $this->request->token()]);
            }
        }

        // 根据客户端的cookie,判断是否可以自动登录
        if ($this->auth->autologin()) {
            $this->redirect($url);
        }
//        $background = Config::get('fastadmin.login_background');
//        $background = stripos($background, 'http') === 0 ? $background : config('site.cdnurl') . $background;
//        $logo = config('item_url') . config('site.plat_logo');
        $this->view->assign('logo', config('url_domain_root') . '/uploads/logo.png');
        $this->view->assign('back', config('url_domain_root') . '/uploads/bg.png');
        $this->view->assign('title', __('Login'));
        Hook::listen("admin_login_init", $this->request);
        return $this->view->fetch();
    }

    /**
     * 注销登录
     */
    public function logout()
    {
        $this->auth->logout();
        Hook::listen("admin_logout_after", $this->request);
        $this->success(__('Logout successful'), 'index/login');
    }

}
