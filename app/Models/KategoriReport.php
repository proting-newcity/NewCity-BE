<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KategoriReport extends Model
{
    use HasFactory;

    // Specify the table name if it doesn't follow Laravel's convention
    protected $table = 'kategori_report';

    // Specify the primary key column
    protected $primaryKey = 'id';

    // If your primary key is not an auto-incrementing integer, you may need this line
    public $incrementing = true;

    public $timestamps = false;

    // Specify the columns that are mass assignable
    protected $fillable = [
        'name',
    ];

    // Optionally, you can also specify hidden attributes if you don't want them to appear in JSON responses
    // protected $hidden = [];
}
