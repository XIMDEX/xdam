<?php

namespace App\Services\Solr\CoreHandlers;

use App\Services\Solr\CoreHandlers\BaseHandler;

class CourseHandler extends BaseHandler
{
    public function __construct($query)
    {
        parent::__construct($query);
    }

    public function queryCoreSpecifics($params)
    {
        return parent::queryCoreSpecifics($params);

        //fq behaviour for cost
        //!!POTENTIALLY DEPRECATED. Now exists a boolean facet isFree. Test it
        //uncomment next lines to simulate isFree facet behaviour.
        // if (isset($params['cost'])) {
        //     $qCost = (int) $params['cost'][0] > 0 ? '[1 TO *]' : '0';
        //     $this->query->getFilterQuery('cost')->setQuery("cost:$qCost");
        // }
    }

    public function defaultBehaviour()
    {
        parent::defaultBehaviour();

        /*DEFAULT BEHAVIOUR: Order by updated_at desc */
        // $this->query->addSort('updated_at', $this->query::SORT_DESC);
    }
}
