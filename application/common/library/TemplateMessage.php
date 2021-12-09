<?php

namespace app\common\library;

class TemplateMessage
{
    /**
     * 退款审核通知
     * @param string $openId 用户微信openid
     * @param string $refundNo 退款单号
     * @param string $orderNo 订单编号
     * @param double $money 退款金额
     * @param string $status 退款状态
     * @param string $remark 备注
     * @return boolean
     */
    public static function refund($openId, $refundNo, $orderNo, $money, $status, $remark)
    {
        $data = [
            'character_string1' => ['value' => $refundNo],
            'character_string12' => ['value' => $orderNo],
            'amount3' => ['value' => $money],
            'thing9' => ['value' => $status],
            'thing7' => ['value' => $remark],
        ];
        return self::sendMessage($openId, config('templateList')[1], $data);
    }

    /**
     * 提现结果通知
     * @param string $openId 用户微信openid
     * @param double $money 提现金额
     * @param string $type 提现方式
     * @param string $time 申请时间
     * @param string $status 状态
     * @param string $remark 备注
     * @return boolean
     */
    public static function withdraw($openId, $money, $type, $time, $status, $remark)
    {
        $data = [
            'amount1' => ['value' => $money],
            'phrase2' => ['value' => $type],
            'date3' => ['value' => $time],
            'phrase5' => ['value' => $status],
            'thing4' => ['value' => $remark],
        ];
        return self::sendMessage($openId, config('templateList')[2], $data);
    }

    /**
     * 活动通知
     * @param string $openId 用户微信openid
     * @param string $name 活动名称
     * @param string $remark 温馨提示
     * @param string $schedule 活动进度
     * @return boolean
     */
    public static function activity($openId, $name, $remark, $schedule)
    {
        $data = [
            'thing1' => ['value' => $name],
            'thing3' => ['value' => $remark],
            'thing2' => ['value' => $schedule],
        ];
        return self::sendMessage($openId, config('templateList')[3], $data);
    }

    /**
     * 代理商审核通知
     * @param string $openId 用户微信openid
     * @param string $applyTime 申请时间
     * @param string $status 审核状态
     * @param string $reviewTime 审核时间
     * @param string $remark 备注信息
     * @return boolean
     */
    public static function agentApply($openId, $applyTime, $status, $reviewTime, $remark)
    {
        $data = [
            'date1' => ['value' => $applyTime],
            'phrase2' => ['value' => $status],
            'date3' => ['value' => $reviewTime],
            'thing4' => ['value' => $remark],
        ];
        return self::sendMessage($openId, config('templateList')[4], $data);
    }

    /**
     * 订单发货消息推送
     * @param string $openId 用户微信openid
     * @param string $orderNo 订单编号
     * @param string $consignee 收货人
     * @param string $address 收货地址
     * @param string $express 快递公司
     * @param string $expressNo 快递单号
     * @return boolean
     */
    public static function orderDelivery($openId, $orderNo, $consignee, $address, $express, $expressNo)
    {
        $data = [
            'character_string1' => ['value' => $orderNo],
            'name17' => ['value' => $consignee],
            'thing7' => ['value' => $address],
            'thing20' => ['value' => $express],
            'character_string5' => ['value' => $expressNo],
        ];
        return self::sendMessage($openId, config('templateList')[5], $data);
    }

    /**
     * 拼团成功通知
     * @param string $openId 用户微信openid
     * @param string $goodsName 商品名称
     * @param string $time 成团时间
     * @param double $price 拼团价格
     * @param string $remark 备注
     * @return boolean
     */
    public static function groupSuccess($openId, $goodsName, $time, $price, $remark)
    {
        $data = [
            'thing1' => ['value' => $goodsName],
            'time2' => ['value' => $time],
            'amount6' => ['value' => $price],
            'thing4' => ['value' => $remark],
        ];
        return self::sendMessage($openId, config('templateList')[6], $data);
    }

    /**
     * 新品发布通知
     * @param string $openId 用户微信openid
     * @param string $goodsName 商品名称
     * @param string $explanation 商品说明
     * @param double $price 商品价格
     * @param string $remark 商品备注
     * @param string $origin 商品产地
     * @return boolean
     */
    public static function newGoods($openId, $goodsName, $explanation, $price, $remark, $origin)
    {
        $data = [
            'thing2' => ['value' => $goodsName],
            'thing1' => ['value' => $explanation],
            'amount3' => ['value' => $price],
            'thing5' => ['value' => $remark],
            'thing6' => ['value' => $origin],
        ];
        return self::sendMessage($openId, config('templateList')[7], $data);
    }

    /**
     * 发送订阅消息
     * @param string $openId 用户微信openid
     * @param string $templateId 模板消息id
     * @param array $data 消息内容
     * @param null $page 跳转页面
     * @param string $miniProgramState 跳转小程序类型：developer为开发版；trial为体验版；formal为正式版；默认为正式版
     * @return boolean
     */
    protected function sendMessage($openId, $templateId, $data, $page = 'index', $miniProgramState = 'trial')
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/message/subscribe/send?access_token=' . getAccessToken();

        $params = [
            'touser' => $openId,//通知用户
            'template_id' => $templateId,//模板id
            'page' => $page,//跳转页面
            'miniprogram_state' => $miniProgramState,//跳转页面类型
            'data' => $data,//模板数据
        ];
        $result = json_decode(\fast\Http::post($url, json_encode($params)), true);
        dump($result);
        return $result['errcode'] == 0 ? true : false;
    }
}