<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentRendererKey extends Model
{
    use HasFactory;

    protected $table = "documents_renderer_keys";

    protected $fillable = ["key", "usages"];

    public function increaseUsages()
    {
        $this->usages++;
        $this->update();
    }

    public function downloadAllowed()
    {
        return $this->usages <= 10;
    }

    public function reachedUsagesLimit()
    {
        return $this->usages >= 10;
    }

    public static function generateKey()
    {
        $alphabet = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $alphabetLength = strlen($alphabet);
        $length = 12;
        $key = '';

        for ($i = 0; $i < $length; $i++) {
            $key .= $alphabet[rand(0, $alphabetLength - 1)];
        }

        return $key;
    }
}
