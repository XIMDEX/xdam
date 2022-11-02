<?php

namespace App\Models;

use DateInterval;
use DateTimeImmutable;
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

    private function downloadAllowed_v1()
    {
        return $this->usages <= 10;
    }

    private function downloadAllowed_v2()
    {
        return time() <= $this->expiration_date;
    }

    public function downloadAllowed()
    {
        return $this->downloadAllowed_v2();
    }

    private function reachedUsagesLimit_v1()
    {
        return $this->usages >= 10;
    }

    private function reachedUsagesLimit_v2()
    {
        return time() > $this->expiration_date;
    }

    public function reachedUsagesLimit()
    {
        return $this->reachedUsagesLimit_v2();
    }

    public function storeKeyExpirationDate()
    {
        $t = time() + 2 * 60;
        $this->expiration_date = $t;
        $this->update();
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
