<?php

namespace App\Models;

use DateInterval;
use DateTimeImmutable;
use Faker\Provider\cs_CZ\DateTime;
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
        $now = new DateTimeImmutable();
        $expirationDate = new DateTimeImmutable($this->expiration_date);
        return $now <= $expirationDate;
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
        $now = new DateTimeImmutable();
        $expirationDate = new DateTimeImmutable($this->expiration_date);
        return $now > $expirationDate;
    }

    public function reachedUsagesLimit()
    {
        return $this->reachedUsagesLimit_v2();
    }

    public function storeKeyExpirationDate()
    {
        $now = new DateTimeImmutable();
        $now = $now->add(DateInterval::createFromDateString('2 minutes'));
        $this->expiration_date = $now;
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
