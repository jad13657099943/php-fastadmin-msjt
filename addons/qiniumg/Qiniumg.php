<?php
/**
 * Created by FlyAdmin www.flyadmin.net
 * User: 君君要上天
 * Date: 2018/12/18
 * Time: 13:33
 */

namespace addons\Qiniumg;


use app\common\library\Menu;
use think\Addons;

class Qiniumg extends Addons
{

    public function install()
    {
        $menu = [
            [
                'name'    => 'qiniumg',
                'title'   => '七牛oss管理',
                'ismenu'  => 1,
                'icon'    => 'fa fa-cloud',
                'remark'  => '请在插件配置中配置七牛云accesskey等信息',
                'sublist' => [
                    ['name' => 'qiniumg/index', 'title' => '查看', 'ismenu' => 0],
                    ['name' => 'qiniumg/del', 'title' => '删除', 'ismenu' => 0],
                    ['name' => 'qiniumg/upload', 'title' => '上传', 'ismenu' => 0],
                    ['name' => 'qiniumg/changetype', 'title' => '切换存储类型', 'ismenu' => 0],
                    ['name' => 'qiniumg/rename', 'title' => '重命名', 'ismenu' => 0],
                ]
            ]
        ];
        Menu::create($menu);
        return true;
    }

    public function uninstall()
    {
        Menu::delete('qiniumg');
        return true;
    }

    /**
     * 插件启用方法
     */
    public function enable()
    {
        Menu::enable('qiniumg');
    }

    /**
     * 插件禁用方法
     */
    public function disable()
    {
        Menu::disable('qiniumg');
    }
}