<?php 

namespace App\Http\Resources\Solr\Traits;


trait HasSemanticTags {

    protected $semanticTags;

    private function getSemanticTags()
    {
        $semantic_tags = $this->data->description->semantic_tags ?? [];
        return $semantic_tags;
    }

    protected function formatSemanticTags($tags)
    {
        $toSolr = [];
        foreach ($tags as $tag) {
            try {
                $toSolr[] = $tag->label;
            } catch (\Throwable $th) {}
        }
        return $toSolr;
    }

    public function getFormattedSemanticTags() {
        if (is_null($this->semanticTags)) {
            $this->semanticTags = $this->formatSemanticTags($this->getSemanticTags());
        }
        return $this->semanticTags;
    }
}