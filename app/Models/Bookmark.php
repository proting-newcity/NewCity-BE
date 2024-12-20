<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Bookmark extends Model
{
    use HasFactory;

    protected $table = 'bookmark';

    public $timestamps = false;

    protected $fillable = ['id_report', 'id_user'];

    public function report(){
        return $this->belongsTo(Report::class,  'id_report');
    }

    public function user(){
        return $this->belongsTo(User::class,'id_user');
    }
}
