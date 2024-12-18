<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RatingReport extends Model
{
    use HasFactory;

    protected $table = 'rating_report';

    public $timestamps = false;

    protected $fillable = ['id_user', 'id_report'];

    public function report(){
        return $this->belongsTo(Report::class,  'id_report');
    }

    public function user(){
        return $this->belongsTo(User::class,'id_user');
    }
}
