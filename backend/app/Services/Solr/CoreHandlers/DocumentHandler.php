<?php

namespace App\Services\Solr\CoreHandlers;

use App\Services\Solr\CoreHandlers\BaseHandler;

class DocumentHandler extends BaseHandler
{
    public function __construct($query)
    {
        parent::__construct($query);
    }
}