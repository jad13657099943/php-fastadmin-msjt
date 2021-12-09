<?php

namespace cr\Request\V20160607;

/**
 * Request of CancelRepoBuild
 *
 * @method string getRepoNamespace()
 * @method string getRepoName()
 * @method string getBuildId()
 */
class CancelRepoBuildRequest extends \RoaAcsRequest
{

    /**
     * @var string
     */
    protected $uriPattern = '/repos/[RepoNamespace]/[RepoName]/build/[BuildId]/cancel';

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
            'CancelRepoBuild',
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
     * @param string $buildId
     *
     * @return $this
     */
    public function setBuildId($buildId)
    {
        $this->requestParameters['BuildId'] = $buildId;
        $this->pathParameters['BuildId'] = $buildId;

        return $this;
    }
}
