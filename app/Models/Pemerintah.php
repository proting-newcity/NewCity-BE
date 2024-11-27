<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pemerintah extends Model
{
    use HasFactory;

    // Define the table name if it's not the plural form of the model name
    protected $table = 'pemerintah';

    // Define the primary key if it's not the default 'id'
    protected $primaryKey = 'id';

    // Disable automatic timestamps if the table does not have created_at/updated_at columns
    public $timestamps = false;

    // Define the attributes that can be mass assigned
    protected $fillable = [
        'id',
        'status',
        'phone',
        'institusi_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id');
    }

    // Define the relationship with the Institusi model
    public function institusi()
    {
        return $this->belongsTo(Institusi::class, 'institusi_id', 'id');
    }
}
