<?php


namespace App\Services\Solr\CoreHandlers;


class ActivityHandler
{

    private $query;

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
