<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Copy extends Model
{
    use SoftDeletes;
    protected $table = 'copies'; 

    protected $primaryKey = 'id'; // Set the primary key field
    protected $keyType = 'string'; // Indicate that the primary key is a string (UUID)
    public $incrementing = false; // Ensure Eloquent doesn't expect an auto-incrementing integer key

    protected $fillable = [
        'id', 'parent_id', 'hash_new', 'hash_old', 'status','message'
    ]; // Allow mass assignment for these fields

    protected $hidden = [
        'created_at', 'updated_at'
    ]; // Hide these fields from the API response

    /**
     * Indicates if the model should use timestamp fields.
     *
     * @var bool
     */
    public $timestamps = true;
}