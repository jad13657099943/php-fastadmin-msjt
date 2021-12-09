<?php

namespace addons\cms\library;

class Service
{

    /**
     * 内容关键字自动加链接
     */
    public static function autolinks($value)
    {
        $links = [];

        $value = preg_replace_callback('~(<a .*?>.*?</a>|<.*?>)~i', function ($match) use (&$links) {
            return '<' . array_push($links, $match[1]) . '>';
        }, $value);

        $config = get_addon_config('cms');
        $autolinks = $config['autolinks'];
        $value = preg_replace_callback('/(' . implode('|', array_keys($autolinks)) . ')/i', function ($match) use ($autolinks) {
            if (!isset($autolinks[$match[1]])) {
                return $match[0];
            } else {
                return '<a href="' . $autolinks[$match[1]] . '" target="_blank">' . $match[0] . '</a>';
            }
        }, $value);
        return preg_replace_callback('/<(\d+)>/', function ($match) use (&$links) {
            return $links[$match[1] - 1];
        }, $value);
    }

}