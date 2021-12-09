<?php

namespace cr\Request\V20160607;

/**
 * Request of GetMirrorList
 *
 */
class GetMirrorListRequest extends \RoaAcsRequest
{

    /**
     * @var string
     */
    protected $uriPattern = '/mirrors';

    /**
     * Class constructor.
     */
    public function __construct()
    {
        parent::__construct(
            'cr',
            '2016-06-07',
            'GetMirrorList',
            'cr'
        );
    }
}
