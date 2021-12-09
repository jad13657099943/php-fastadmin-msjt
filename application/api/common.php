<?php

//常用函数
/*
 * 拼接数组图片
 * */
if(function_exists('joinArrayImages')){
    function joinArrayImages($array_image,$field){
        if($array_image !=null){
            foreach ($array_image as $key=>$value){
                $array_image[$key]['image'] = $value[$field] ? config('item_url').$value[$field] :'';
            }
        }
        return $array_image;
    }
}


