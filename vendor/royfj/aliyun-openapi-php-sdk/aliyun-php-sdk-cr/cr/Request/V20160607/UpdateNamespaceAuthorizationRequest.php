<?php

namespace cr\Request\V20160607;

/**
 * Request of UpdateNamespaceAuthorization
 *
 * @method string getAuthorizeId()
 * @method string getNamespace()
 */
class UpdateNamespaceAuthorizationRequest extends \RoaAcsRequest
{

    /**
     * @var string
     */
    protected $uriPattern = '/namespace/[Namespace]/authorizations/[AuthorizeId]';

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
            'cr',
            '2016-06-07',
            'UpdateNamespaceAuthorization',
            'cr'
        );
    }

    /**
     * @param string $authorizeId
     *
     * @return $this
     */
    public function setAuthorizeId($authorizeId)
    {
        $this->requestParameters['AuthorizeId'] = $authorizeId;
        $this->pathParameters['AuthorizeId'] = $authorizeId;

        return $this;
    }

    /**
     * @param string $namespace
     *
     * @return $this
     */
    public function setNamespace($namespace)
    {
        $this->requestParameters['Namespace'] = $namespace;
        $this->pathParameters['Namespace'] = $namespace;

        return $this;
    }
}
