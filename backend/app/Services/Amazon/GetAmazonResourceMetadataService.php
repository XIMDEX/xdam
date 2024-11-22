<?php

namespace App\Services\Amazon;

use App\Models\DamResource;
use Illuminate\Http\Response;

class GetAmazonResourceMetadataService
{
    public function getMetadata(DamResource $damResource, string $fileName) {
        $metadataArray = json_decode($damResource->data->metadata, true); // Assuming metadata is a JSON string

        // Function to find the object by the filename
        $result = null;
        foreach ($metadataArray as $item) {
            if (array_key_exists($fileName, $item)) {
                $result = $item[$fileName];
                break;
            }
        }

        if ($result !== null) {
            return $result;
        } else {
            throw new \Exception('File not found');
        }
    }

}
