<?php


namespace App\Traits;


use Illuminate\Support\Str;

trait UsesUuid
{
    protected static function bootUsesUuid() {
        static::creating(function ($model) {
            $model->{$model->getKeyName()} = Str::uuid();
        });
    }

    public function getIncrementing()
    {
        return false;
    }

    public function getKeyType()
    {
        return 'string';
    }
}
