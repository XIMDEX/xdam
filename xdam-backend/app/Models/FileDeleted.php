<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FileDeleted extends Model
{
    use HasFactory;

    protected $table = 'files_deleted';


    public function file()
    {
        return $this->belongsTo(File::class);
    }
}
