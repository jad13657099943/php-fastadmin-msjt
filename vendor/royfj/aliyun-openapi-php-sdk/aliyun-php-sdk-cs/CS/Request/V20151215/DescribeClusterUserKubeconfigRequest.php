<?php

namespace CS\Request\V20151215;

/**
 * @deprecated Please use https://github.com/aliyun/openapi-sdk-php
 *
 * Request of DescribeClusterUserKubeconfig
 *
 * @method string getClusterId()
 */
class DescribeClusterUserKubeconfigRequest extends \RoaAcsRequest
{

    /**
     * @var string
     */
    protected $uriPattern = '/k8s/[ClusterId]/user_config';

    /**
     * Class constructor.
     */
    public function __construct()
    {
        parent::__construct(
            'CS',
            '2015-12-15',
            'DescribeClusterUserKubeconfig',
            'cs'
        );
    }

    /**
     * @param string $clusterId
     *
     * @return $this
     */
    public function setClusterId($clusterId)
    {
        $this->requestParameters['ClusterId'] = $clusterId;
        $this->pathParameters['ClusterId'] = $clusterId;

        return $this;
    }
}
