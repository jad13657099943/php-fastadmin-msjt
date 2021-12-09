<?php

return array (
  0 => 
  array (
    'name' => 'key',
    'title' => '云片网Key',
    'type' => 'string',
    'content' => 
    array (
    ),
    'value' => '4f10a84c2ad89bacd76291a8eaf78b44',
    'rule' => 'required',
    'msg' => '',
    'tip' => '',
    'ok' => '',
    'extend' => '',
  ),
  1 => 
  array (
    'name' => 'template',
    'title' => '签名模板',
    'type' => 'array',
    'content' => 
    array (
    ),
    'value' => 
    array (
      'default' => '【国赣臻品】您的验证码是#code#',
    ),
    'rule' => 'required',
    'msg' => '1',
    'tip' => '',
    'ok' => '1',
    'extend' => '',
  ),
  2 => 
  array (
    'name' => '__tips__',
    'title' => '温馨提示',
    'type' => 'string',
    'content' => 
    array (
    ),
    'value' => '模板如:【FastAdmin】你的验证码为:#code#  ，要使用 <b>#code#</b> 作为验证码的占位符<br/>
模板键对应短信事件，值对应模板。<b>default</b> 作为没有正确匹配事件时使用的模板，可以防止短信发送失败',
    'rule' => '',
    'msg' => '',
    'tip' => '',
    'ok' => '',
    'extend' => '',
  ),
);
