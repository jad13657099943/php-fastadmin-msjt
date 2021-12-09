<?php

namespace afs\Request\V20180112;

/**
 * Request of DescribePersonMachineList
 *
 * @method string getSourceIp()
 */
class DescribePersonMachineListRequest extends \RpcAcsRequest
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
            'afs',
            '2018-01-12',
            'DescribePersonMachineList',
            'afs'
        );
    }

    /**
     * @param string $sourceIp
     *
     * @return $this
     */
    public function setSourceIp($sourceIp)
    {
        $this->requestParameters['SourceIp'] = $sourceIp;
        $this->queryParameters['SourceIp'] = $sourceIp;

        return $this;
    }
}
