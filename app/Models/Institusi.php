<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Institusi extends Model
{
    use HasFactory;

    // Define the table name
    protected $table = 'institusi';

    // Define the primary key if it's not the default 'id'
    protected $primaryKey = 'id';

    // Disable automatic timestamps if the table does not have created_at/updated_at columns
    public $timestamps = false;

    // Define the attributes that can be mass assigned
    protected $fillable = [
        'name',
    ];

    // Define the inverse relationship with the Pemerintah model
    public function pemerintah()
    {
        return $this->hasMany(Pemerintah::class, 'institusi_id', 'id');
    }
}
