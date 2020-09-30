<?php

namespace App\Models;

use App\Enums\CrawlerJobStatus;
use BenSampo\Enum\Exceptions\InvalidEnumKeyException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CrawlerJob extends Model
{
    use HasFactory;


    public function setStatusAttribute(int $value){
        try {
            if ( CrawlerJobStatus::fromValue( $value ) ) {
                return $this->attributes['status'] = $value;
            }
        } catch ( InvalidEnumKeyException $e ) {
            throw $e;
        }
    }

    public function isFinished() {
        if ($this->attributes['status'] == CrawlerJobStatus::finished){
            return true;
        }
        return false;
    }

    public function isProcessing() {
        if ($this->attributes['status'] == CrawlerJobStatus::processing){
            return true;
        }
        return false;
    }

    public function isCreated() {
        if ($this->attributes['status'] == CrawlerJobStatus::created){
            return true;
        }
        return false;
    }


}
