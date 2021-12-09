<?php

namespace cr\Request\V20160607;

/**
 * Request of SearchRepo
 *
 * @method string getOrigin()
 * @method string getPageSize()
 * @method string getPage()
 * @method string getKeyword()
 */
class SearchRepoRequest extends \RoaAcsRequest
{

    /**
     * @var string
     */
    protected $uriPattern = '/search';

    /**
     * Class constructor.
     */
    public function __construct()
    {
        parent::__construct(
            'cr',
            '2016-06-07',
            'SearchRepo',
            'cr'
        );
    }

    /**
     * @param string $origin
     *
     * @return $this
     */
    public function setOrigin($origin)
    {
        $this->requestParameters['Origin'] = $origin;
        $this->queryParameters['Origin'] = $origin;

        return $this;
    }

    /**
     * @param string $pageSize
     *
     * @return $this
     */
    public function setPageSize($pageSize)
    {
        $this->requestParameters['PageSize'] = $pageSize;
        $this->queryParameters['PageSize'] = $pageSize;

        return $this;
    }

    /**
     * @param string $page
     *
     * @return $this
     */
    public function setPage($page)
    {
        $this->requestParameters['Page'] = $page;
        $this->queryParameters['Page'] = $page;

        return $this;
    }

    /**
     * @param string $keyword
     *
     * @return $this
     */
    public function setKeyword($keyword)
    {
        $this->requestParameters['Keyword'] = $keyword;
        $this->queryParameters['Keyword'] = $keyword;

        return $this;
    }
}
