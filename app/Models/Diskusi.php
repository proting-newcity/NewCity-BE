<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Diskusi extends Model
{
    use HasFactory;

    protected $table = 'diskusi';

    public $timestamps = false;

    protected $fillable = ['content', 'id_report', 'id_user'];

    public function report(){
        return $this->belongsTo(Report::class,  'id_report');
    }

    public function user(){
        return $this->belongsTo(User::class,'id_user');
    }
}
