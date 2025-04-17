<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Admin extends Model
{
    use HasFactory;

    // Define the table name if it's not the default plural form of the model name
    protected $table = 'admin';

    // Define the primary key (if it's not the default 'id')
    protected $primaryKey = 'id';

    // Disable automatic timestamps if the table doesn't have created_at/updated_at columns
    public $timestamps = false;

    public $incrementing = false;

    // Define the attributes that can be mass assigned
    protected $fillable = [
        'id',
        'nip',
    ];

    // Define the relationship with the User model
    public function user()
    {
        return $this->belongsTo(User::class, 'id'); // Assuming the admin is related to the user via the id
    }
}
