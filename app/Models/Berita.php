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
    protected $fillable = ['title', 'content', 'foto', 'tanggal', 'status', 'id_kategori', 'id_user'];

    // Menyembunyikan atribut dalam serialisasi
    protected $hidden = [];

    // Relasi dengan model Kategori
    public function kategori()
    {
        return $this->belongsTo(KategoriBerita::class, 'id_kategori');
    }

    // Relasi dengan model User (editor)
    public function admin()
    {
        return $this->belongsTo(Admin::class, 'id_user');
    }

    /**
     * Get the RatingBerita handling the berira.
     */
    public function likes()
    {
        return $this->hasMany(RatingBerita::class);
    }
}
