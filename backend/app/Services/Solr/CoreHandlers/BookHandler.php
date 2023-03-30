<?php

namespace App\Services\Solr\CoreHandlers;

use App\Services\Solr\CoreHandlers\BaseHandler;

class BookHandler extends BaseHandler
{
    public function __construct($query)
    {
        parent::__construct($query);
    }
    
    public function defaultBehaviour()
    {
        parent::defaultBehaviour();

        /*DEFAULT BEHAVIOUR: Order by updated_at desc */
        $this->query->addSort('updated_at', $this->query::SORT_DESC);
    }
}
