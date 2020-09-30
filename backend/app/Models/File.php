<?php

namespace App\Models;

use App\Http\Traits\UsesUuid;
use App\Services\Dam\DamService;
use App\Services\Dam\DamServiceInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use TSterker\Solarium\SolariumManager;


class File extends Model
{
    use HasFactory;
    use UsesUuid;

    public function deletedFile()
    {
        return $this->hasOne(FileDeleted::class);
    }

    public function preview()
    {
        return $this->hasOne(FilePreview::class);
    }

    public function getThumbnail() {
        $previews = $this->preview()->first();
        if (null != $previews) {
            return $previews;
        } else {
            return false;
        }
    }

    public static function boot()
    {
        parent::boot();
        self::deleting(function($model) {
            // get all associated previews and delete them
            $previews = $model->preview()->get();
            foreach($previews as $preview){
                $path = $preview->local_path;
                @unlink($path);
            }
            // delete the document from dam's core
            $damService = resolve(DamService::class);
            $damService->deleteDocumentById($model->id);
        });
    }
}
