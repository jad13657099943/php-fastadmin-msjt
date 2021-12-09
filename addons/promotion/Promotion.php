<?php

namespace addons\promotion;

use app\common\library\Menu;
use think\Addons;

/**
 * 插件
 */
class Promotion extends Addons
{

    /**
     * 插件安装方法
     * @return bool
     */
    public function install()
    {
        $menu = [
            [
               'name'    => 'promotion',
               'title'   => '营销系统',
               'icon'    => 'fa fa-shopping-basket',
               'sublist' => [
                                [
                                    'name'    => 'buyrule',
                                    'title'   => '抢购规则',
                                    'icon'    => 'fa fa-image',
                                    'sublist' => [
                                        ['name' => 'buyrule/index', 'title' => '查看'],
                                        ['name' => 'buyrule/add', 'title' => '添加'],
                                        ['name' => 'buyrule/edit', 'title' => '修改'],
                                        ['name' => 'buyrule/del', 'title' => '删除']
                                    ]
                                ],
                                [
                                    'name'    => 'buygoods',
                                    'title'   => '抢购商品设置',
                                    'icon'    => 'fa fa-gift',
                                    'sublist' => [
                                        ['name' => 'buygoods/index', 'title' => '查看'],
                                        ['name' => 'buygoods/add', 'title' => '添加'],
                                        ['name' => 'buygoods/edit', 'title' => '修改'],
                                        ['name' => 'buygoods/del', 'title' => '删除'],
                                    ]
                                ],
                                [
                                    'name'    => 'buyorder',
                                    'title'   => '抢购订单',
                                    'icon'    => 'fa fa-tasks',
                                    'sublist' => [
                                        ['name' => 'buyorder/index', 'title' => '查看'],
                                        ['name' => 'buyorder/del', 'title' => '删除'],
                                        ['name' => 'buyorder/detail', 'title' => '订单详情'],
                                    ]
                                ],
                             ]
            ]
        ];
        Menu::create($menu);
        return true;
    }

    /**
     * 插件卸载方法
     * @return bool
     */
    public function uninstall()
    {
        Menu::delete("promotion");
        return true;
    }

    /**
     * 插件启用方法
     * @return bool
     */
    public function enable()
    {
        Menu::enable("promotion");
        return true;
    }

    /**
     * 插件禁用方法
     * @return bool
     */
    public function disable()
    {
        Menu::disable("promotion");
        return true;
    }

    /**
     * 实现钩子方法
     * @return mixed
     */
    public function testhook($param)
    {
        // 调用钩子时候的参数信息
        print_r($param);
        // 当前插件的配置信息，配置信息存在当前目录的config.php文件中，见下方
        print_r($this->getConfig());
        // 可以返回模板，模板文件默认读取的为插件目录中的文件。模板名不能为空！
        //return $this->fetch('view/info');
    }

}
