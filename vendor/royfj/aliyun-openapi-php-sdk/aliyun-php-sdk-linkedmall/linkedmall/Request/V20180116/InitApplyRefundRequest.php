<?php

namespace linkedmall\Request\V20180116;

/**
 * @deprecated Please use https://github.com/aliyun/openapi-sdk-php
 *
 * Request of InitApplyRefund
 *
 * @method string getGoodsStatus()
 * @method string getSubLmOrderId()
 * @method string getBizUid()
 * @method string getBizClaimType()
 * @method string getBizId()
 */
class InitApplyRefundRequest extends \RpcAcsRequest
{

    /**
     * @var string
     */
    protected $method = 'POST';

    /**
     * Class constructor.
     */
    public function __construct()
    {
        parent::__construct(
            'linkedmall',
            '2018-01-16',
            'InitApplyRefund',
            'linkedmall'
        );
    }

    /**
     * @param string $goodsStatus
     *
     * @return $this
     */
    public function setGoodsStatus($goodsStatus)
    {
        $this->requestParameters['GoodsStatus'] = $goodsStatus;
        $this->queryParameters['GoodsStatus'] = $goodsStatus;

        return $this;
    }

    /**
     * @param string $subLmOrderId
     *
     * @return $this
     */
    public function setSubLmOrderId($subLmOrderId)
    {
        $this->requestParameters['SubLmOrderId'] = $subLmOrderId;
        $this->queryParameters['SubLmOrderId'] = $subLmOrderId;

        return $this;
    }

    /**
     * @param string $bizUid
     *
     * @return $this
     */
    public function setBizUid($bizUid)
    {
        $this->requestParameters['BizUid'] = $bizUid;
        $this->queryParameters['BizUid'] = $bizUid;

        return $this;
    }

    /**
     * @param string $bizClaimType
     *
     * @return $this
     */
    public function setBizClaimType($bizClaimType)
    {
        $this->requestParameters['BizClaimType'] = $bizClaimType;
        $this->queryParameters['BizClaimType'] = $bizClaimType;

        return $this;
    }

    /**
     * @param string $bizId
     *
     * @return $this
     */
    public function setBizId($bizId)
    {
        $this->requestParameters['BizId'] = $bizId;
        $this->queryParameters['BizId'] = $bizId;

        return $this;
    }
}
