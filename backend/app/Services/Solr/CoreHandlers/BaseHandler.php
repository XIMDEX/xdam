<?php

namespace App\Services\Solr\CoreHandlers;

class BaseHandler
{

    protected $query;

    public function __construct($query)
    {
        $this->query = $query;

    }

    public function queryCoreSpecifics($params) {
        $this->defaultBehaviour();

        return $this->query;
    }

    public function defaultBehaviour()
    {
    }
}
