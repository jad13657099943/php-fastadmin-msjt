<?php

// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
use think\Route;

Route::group('user', function () {
    //微信授权登录
    Route::post('register', 'api/msjt.users/wxRegister');
    //设置手机
    Route::post('mobile', 'api/msjt.users/mobile');
    //用户信息
    Route::get('info', 'api/msjt.users/info');
    //编辑用户信息
    Route::post('set', 'api/msjt.users/set');
    //等级制度
    Route::get('grade', 'api/msjt.grade/grade');
    //我的订单
    Route::get('order', 'api/msjt.users/order');
    //订单详情
    Route::get('detail', 'api/msjt.users/detail');
    //取消订单
    Route::post('del', 'api/msjt.users/del');
    //确认收货
    Route::post('take', 'api/msjt.users/take');
    //我的课程
    Route::get('curriculum', 'api/msjt.users/curriculum');
    //删除课程订单
    Route::post('delCurriculum', 'api/msjt.users/delCurriculum');
    //取消课程订单
    Route::post('cancelCurriculum', 'api/msjt.users/cancelCurriculum');
});

Route::group('message', function () {
    //提交反馈
    Route::post('set', 'api/msjt.message/set');
});

Route::group('region', function () {
    //添加地址
    Route::post('set', 'api/msjt.region/set');
    //设置默认地址
    Route::post('default', 'api/msjt.region/setDefault');
    //删除地址
    Route::post('del', 'api/msjt.region/del');
    //编辑地址
    Route::post('update', 'api/msjt.region/update');
    //地址列表
    Route::get('list', 'api/msjt.region/lists');
    //地址下拉
    Route::get('site', 'api/msjt.site/site');
});

Route::group('type', function () {
    //商品分类
    Route::get('goods', 'api/msjt.type/goodsType');
    //课程分类
    Route::get('curriculum', 'api/msjt.type/curriculumType');
});

Route::group('goods', function () {
    //商品列表
    Route::get('list', 'api/msjt.goods/lists');
    //商品详情
    Route::get('info', 'api/msjt.goods/info');
});

Route::group('curriculum', function () {
    //课程列表
    Route::get('list', 'api/msjt.curriculum/list');
    //课程详情
    Route::get('detail', 'api/msjt.curriculum/detail');
    //课程下单
    Route::post('order', 'api/msjt.curriculum/setOder');
    //课程支付
    Route::post('pay', 'api/msjt.curriculum/pay');
    //回调
    Route::rule('notifyurl', 'api/msjt.curriculum/notifyurl');
    //课程报名
    Route::post('sign', 'api/msjt.curriculum/sign');
    //课程学习
    Route::post('study', 'api/msjt.curriculum/study');
});

Route::group('search', function () {
    //搜素记录
    Route::get('list', 'api/msjt.search/list');
    //猜你喜欢
    Route::get('hot', 'api/msjt.search/hot');
    //删除搜索记录
    Route::post('del', 'api/msjt.search/del');
});

Route::group('order', function () {
    //下单
    Route::post('set', 'api/msjt.order/set');
    //支付
    Route::post('pay', 'api/msjt.order/pay');
    //回调
    Route::rule('notifyurl', 'api/msjt.order/notifyurl');
    //运费
    Route::get('freight', 'api/msjt.order/freight');
});

Route::group('car', function () {
    //购物车列表
    Route::get('lists', 'api/msjt.car/lists');
    //添加购物车
    Route::post('add', 'api/msjt.car/add');
    //编辑购物车
    Route::post('edit', 'api/msjt.car/edit');
    //删除购物车
    Route::post('del', 'api/msjt.car/del');
});


Route::group('collection', function () {
    //收藏列表
    Route::get('lists', 'api/msjt.collection/lists');
    //添加收藏
    Route::post('add', 'api/msjt.collection/add');
    //删除收藏
    Route::post('del', 'api/msjt.collection/del');
});

Route::group('sale', function () {
    //提交售后
    Route::post('add', 'api/msjt.sale/add');
    //售后列表
    Route::get('lists', 'api/msjt.sale/lists');
    //售后详情
    Route::get('detail', 'api/msjt.sale/detail');
    //取消售后
    Route::post('status', 'api/msjt.sale/status');
});

Route::group('agency', function () {
    //人员统计
    Route::get('total', 'api/msjt.agency/total');
    //分销订单
    Route::get('order', 'api/msjt.agency/order');
    //佣金明细
    Route::get('balance', 'api/msjt.agency/balance');
    //推广中心
    Route::get('centre', 'api/msjt.agency/centre');
    //提现
    Route::post('withdraw', 'api/msjt.agency/withdraw');
});
/**
 * 测试
 */
Route::group('test', function () {
    //
    Route::post('test', 'api/msjt.test/test');
});

Route::group('adcate', function () {
    //广告
    Route::get('list', 'api/msjt.adcate/list');
});

Route::group('apply', function () {
    //申请分销
    Route::post('add', 'api/msjt.apply/add');
});
/**
 * 退款
 */
Route::group('refund', function () {
    Route::rule('notify', 'api/msjt.refund/refundNotify');
});
/**
 * 七牛云
 */
Route::group('qny', function () {
    Route::post('upload', 'api/msjt.upload/upload');
});
//基础配置
Route::group('config', function () {
    Route::get('config', 'api/msjt.config/config');
});
