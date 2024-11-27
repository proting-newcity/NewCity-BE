<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KategoriBerita extends Model
{
    use HasFactory;

    // Nama tabel
    protected $table = 'kategori_berita';

    // Primary key
    protected $primaryKey = 'id';

    // Kolom yang dapat diisi secara massal
    protected $fillable = ['name', 'foto'];

    // Menyembunyikan atribut dalam serialisasi
    protected $hidden = [];
    
    public $timestamps = false;

    // Relasi dengan model Berita
    public function berita()
    {
        return $this->hasMany(Berita::class, 'id_kategori');
    }
}
