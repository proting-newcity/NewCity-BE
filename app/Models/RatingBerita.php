<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RatingBerita extends Model
{
    use HasFactory;
    
    protected $table = 'rating_berita';

    public $timestamps = false;

    protected $fillable = ['id_user', 'id_berita'];

    public function berita(){
        return $this->belongsTo(Berita::class,  'id_berita');
    }

    public function user(){
        return $this->belongsTo(User::class,'id_user');
    }
}
