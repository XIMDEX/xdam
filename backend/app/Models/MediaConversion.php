<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class MediaConversion extends Model
{
    use HasFactory;

    protected $table = "media_conversions";

    protected $fillable = ['media_id', 'file_type', 'file_name', 'file_compression', 'resolution'];

    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class);
    }
}
