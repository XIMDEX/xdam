<?php


namespace App\Services\ExternalApis\Xowl;

class XtagsCleaner
{
    private  $xtags;
    private  $xtags_interlinked;
    public function __construct($xtags, $xtags_interlinked)
    {
        $this->xtags = $xtags;
        $this->xtags_interlinked = $xtags_interlinked;
    }

    /**
     * Process xtags data.
     *
     * @return array
     */
    public function getProcessedXtags()
    {
        // Delete duplicate xtags
        $this->xtags = $this->deleteDuplicateXtag($this->xtags);
        $this->xtags_interlinked = $this->deleteDuplicateXtag($this->xtags_interlinked);

        // Check for non-linked xtags
        $this->xtags = $this->checkNonLinked($this->xtags_interlinked, $this->xtags);

        // Get info for xtags
        foreach ($this->xtags as &$tag) {
            $tag = $this->getInfoXtags($tag, false);
        }

        // Get info for xtags_interlinked
        foreach ($this->xtags_interlinked as &$tag) {
            $tag = $this->getInfoXtags($tag, true);
        }

        // Return the results
        return ['xtags' => $this->xtags, 'xtags_interlinked' => $this->xtags_interlinked];
    }
    /**
     * Delete duplicate xtag.
     *
     * @param array $xtags The array of xtags.
     *
     * @return array The array of xtags without duplicates.
     */
    private function deleteDuplicateXtag($xtags)
    {
        $result = [];
        $aux    = [];
        foreach ($xtags as $xtag) {
            if (!in_array($xtag->text, $aux)) {
                $result[] = $xtag;
                $aux[] = $xtag->text;
            }
        }
        return $result;
    }
    /**
     * Check non-linked.
     *
     * @param array $linked The linked array.
     * @param array $nonLinked The non-linked array.
     *
     * @return array The result array.
     */
    private function checkNonLinked(array $linked, array $nonLinked)
    {
        $result = [];
        $linkedTexts = array_map(function ($link) {
            return $link->text;
        }, $linked);
        foreach ($nonLinked as $tag) {
            if (!in_array($tag->text, $linkedTexts)) $result[] =  $tag;
        }
        return $result;
    }

    private function getInfoXtags($entity, $withURL)
    {
        $output = [
            'name' => $entity->text,
            'confidence' => $entity->confidence,
            'type' => $entity->type,
            'start' => $entity->start,
            'end' => $entity->end,
        ];

        if ($withURL) $output['uri'] = isset($entity->dbpedia_uri) ? $entity->dbpedia_uri : $entity->uri;

        return $output;
    }
}
