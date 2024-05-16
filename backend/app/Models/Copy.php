<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Copy extends Model
{
    protected $table = 'copies'; 

    protected $primaryKey = 'id'; // Set the primary key field
    protected $keyType = 'string'; // Indicate that the primary key is a string (UUID)
    public $incrementing = false; // Ensure Eloquent doesn't expect an auto-incrementing integer key

    protected $fillable = [
        'id', 'parent_id', 'hash_new', 'hash_old', 'status'
    ]; // Allow mass assignment for these fields

    /**
     * Indicates if the model should use timestamp fields.
     *
     * @var bool
     */
    public $timestamps = true;
}