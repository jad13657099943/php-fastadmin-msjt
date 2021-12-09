<?php

namespace cr\Request\V20160607;

/**
 * Request of CreateUserInfo
 *
 */
class CreateUserInfoRequest extends \RoaAcsRequest
{

    /**
     * @var string
     */
    protected $uriPattern = '/users';

    /**
     * @var string
     */
    protected $method = 'PUT';

    /**
     * Class constructor.
     */
    public function __construct()
    {
        parent::__construct(
            'cr',
            '2016-06-07',
            'CreateUserInfo',
            'cr'
        );
    }
}
