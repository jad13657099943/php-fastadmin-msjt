<?php

namespace cr\Request\V20160607;

/**
 * Request of GetImageManifest
 *
 * @method string getRepoNamespace()
 * @method string getRepoName()
 * @method string getTag()
 * @method string getSchemaVersion()
 */
class GetImageManifestRequest extends \RoaAcsRequest
{

    /**
     * @var string
     */
    protected $uriPattern = '/repos/[RepoNamespace]/[RepoName]/tags/[Tag]/manifest';

    /**
     * Class constructor.
     */
    public function __construct()
    {
        parent::__construct(
            'cr',
            '2016-06-07',
            'GetImageManifest',
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
     * @param string $tag
     *
     * @return $this
     */
    public function setTag($tag)
    {
        $this->requestParameters['Tag'] = $tag;
        $this->pathParameters['Tag'] = $tag;

        return $this;
    }

    /**
     * @param string $schemaVersion
     *
     * @return $this
     */
    public function setSchemaVersion($schemaVersion)
    {
        $this->requestParameters['SchemaVersion'] = $schemaVersion;
        $this->queryParameters['SchemaVersion'] = $schemaVersion;

        return $this;
    }
}
