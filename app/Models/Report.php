<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use HasFactory;

    // Define the table name
    protected $table = 'report';

    // Specify the primary key (if it's not `id`)
    protected $primaryKey = 'id';

    // Set the primary key type
    public $incrementing = true;
    protected $keyType = 'int';

    // Disable timestamps if they are not in the table
    public $timestamps = true;

    // Define fillable fields for mass assignment
    protected $fillable = [
        'judul',
        'deskripsi',
        'lokasi',
        'status',
        'foto',
        'id_masyarakat',
        'id_pemerintah',
        'id_kategori',
    ];

    // Cast JSON attributes to array
    protected $casts = [
        'status' => 'array',
    ];

    // Define relationships

    /**
     * Get the category associated with the report.
     */
    public function category()
    {
        return $this->belongsTo(KategoriReport::class, 'id_kategori');
    }

    /**
     * Get the masyarakat who reported this.
     */
    public function masyarakat()
    {
        return $this->belongsTo(Masyarakat::class, 'id_masyarakat');
    }

    /**
     * Get the pemerintah handling the report.
     */
    public function pemerintah()
    {
        return $this->belongsTo(Pemerintah::class, 'id_pemerintah');
    }

    /**
     * Get the RatingReport handling the report.
     */
    public function likes()
    {
        return $this->hasMany(RatingReport::class);
    }
}
