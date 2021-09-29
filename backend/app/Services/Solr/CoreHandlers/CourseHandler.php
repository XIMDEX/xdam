<?php


namespace App\Services\Solr\CoreHandlers;


class CourseHandler
{

    private $query;

    public function __construct($query)
    {
        $this->query = $query;

    }

    public function queryCoreSpecifics($params) {
        //fq behaviour for cost
        if (isset($params['cost'])) {
            $qCost = (int) $params['cost'][0] > 0 ? '[1 TO *]' : '0';
            $this->query->getFilterQuery('cost')->setQuery("cost:$qCost");
        }
        return $this->query;
    }
}
