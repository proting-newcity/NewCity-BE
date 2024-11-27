<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Berita extends Model
{
    use HasFactory;

    // Nama tabel
    protected $table = 'berita';

    // Primary key
    protected $primaryKey = 'id';

    // Kolom yang dapat diisi secara massal
    protected $fillable = ['title', 'content', 'photo', 'tanggal', 'status', 'id_kategori'];

    // Menyembunyikan atribut dalam serialisasi
    protected $hidden = [];

    // Relasi dengan model Kategori
    public function kategori()
    {
        return $this->belongsTo(Kategori::class, 'id_kategori');
    }

    // Relasi dengan model User (editor)
    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }
}
