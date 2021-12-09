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
        $this->defaultBehaviour();
        //fq behaviour for cost 
        //!!POTENTIALLY DEPRECATED. Now exists a boolean facet isFree. Test it
        //uncomment next lines to simulate isFree facet behaviour.
        // if (isset($params['cost'])) {
        //     $qCost = (int) $params['cost'][0] > 0 ? '[1 TO *]' : '0';
        //     $this->query->getFilterQuery('cost')->setQuery("cost:$qCost");
        // }
        
        return $this->query;
    }

    public function defaultBehaviour()
    {
        /*DEFAULT BEHAVIOUR: Order by updated_at desc */
        // $this->query->addSort('updated_at', $this->query::SORT_DESC);
    }
}
