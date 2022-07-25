<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PendingVideoCompressionTask extends Model
{
    use HasFactory;

    protected $table = "pending_video_compression_tasks";

    protected $fillable = ['media_id', 'resolution', 'src_path', 'dest_path'];
}
