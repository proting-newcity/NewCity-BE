<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kategori extends Model
{
    use HasFactory;

    // Nama tabel
    protected $table = 'kategori_berita';

    // Primary key
    protected $primaryKey = 'id';

    // Kolom yang dapat diisi secara massal
    protected $fillable = ['name', 'photo'];

    // Menyembunyikan atribut dalam serialisasi
    protected $hidden = [];

    // Relasi dengan model Berita
    public function berita()
    {
        return $this->hasMany(Berita::class, 'id_kategori');
    }
}
