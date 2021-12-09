<?php

namespace cr\Request\V20160607;

/**
 * Request of DeleteRepoAuthorization
 *
 * @method string getRepoNamespace()
 * @method string getRepoName()
 * @method string getAuthorizeId()
 */
class DeleteRepoAuthorizationRequest extends \RoaAcsRequest
{

    /**
     * @var string
     */
    protected $uriPattern = '/repos/[RepoNamespace]/[RepoName]/authorizations/[AuthorizeId]';

    /**
     * @var string
     */
    protected $method = 'DELETE';

    /**
     * Class constructor.
     */
    public function __construct()
    {
        parent::__construct(
            'cr',
            '2016-06-07',
            'DeleteRepoAuthorization',
            'cr'
        );
    }

    /**
     * @param string $repoNamespace
     *
     * @return $this
     */
    public function setRepoNamespace($repoNamespace)
    {
        $this->requestParameters['RepoNamespace'] = $repoNamespace;
        $this->pathParameters['RepoNamespace'] = $repoNamespace;

        return $this;
    }

    /**
     * @param string $repoName
     *
     * @return $this
     */
    public function setRepoName($repoName)
    {
        $this->requestParameters['RepoName'] = $repoName;
        $this->pathParameters['RepoName'] = $repoName;

        return $this;
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
}
