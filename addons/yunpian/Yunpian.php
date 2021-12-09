<?php

namespace addons\yunpian;

use fast\Http;
use think\Addons;

/**
 * 插件
 */
class Yunpian extends Addons
{

    /**
     * 插件安装方法
     * @return bool
     */
    public function install()
    {

        return true;
    }

    /**
     * 插件卸载方法
     * @return bool
     */
    public function uninstall()
    {

        return true;
    }

    /**
     * 插件启用方法
     * @return bool
     */
    public function enable()
    {

        return true;
    }

    /**
     * 插件禁用方法
     * @return bool
     */
    public function disable()
    {

        return true;
    }

    /**
     * 实现钩子方法
     * @param $param
     * @return mixed
     */
    public function smsSend($param)
    {
        // 当前插件的配置信息，配置信息存在当前目录的config.php文件中，见下方
        $config = $this->getConfig();;

        //匹配时间对应的模板
        if (isset($config['template'][$param->event])) {
            $template = $config['template'][$param->event];
        } elseif (isset($config['template']['default'])) {
            $template = $config['template']['default'];
        } else {
            return false;
        }

        $params = [
            'apikey' => $config['key'],
            'mobile' => $param->mobile,
            'text' => str_replace('#code#', $param->code, $template)
        ];


        $result = json_decode(Http::post('https://sms.yunpian.com/v2/sms/single_send.json', $params), true);

        if ($result['code'] === 0)
            return true;
        return false;
    }

}
