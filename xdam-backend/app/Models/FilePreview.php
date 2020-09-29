<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FilePreview extends Model
{
    use HasFactory;

    protected $table = 'files_previews';

    public function file()
    {
        return $this->belongsTo(File::class);
    }
}
