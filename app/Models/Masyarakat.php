<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Masyarakat extends Model
{
    use HasFactory;

    // Specify the table name if it's not the plural form of the model
    protected $table = 'masyarakat';

    // The primary key for the model
    protected $primaryKey = 'id';

    public $timestamps = false;

    // Disable auto-incrementing if you're using a custom primary key
    public $incrementing = false;

    // Define fillable attributes
    protected $fillable = [
        'id',        // Foreign key from user table
        'phone',
    ];

    // Define the relationship with the User model
    public function user()
    {
        return $this->belongsTo(User::class, 'id');
    }

    /**
     * Get reports created by this masyarakat.
     */
    public function reports()
    {
        return $this->hasMany(Report::class, 'id_masyarakat');
    }

    /**
     * Get all likes (ratings) associated with the reports created by this masyarakat.
     */
    public function likes()
    {
        return $this->hasManyThrough(
            RatingReport::class,
            Report::class,
            'id_masyarakat',
            'id_report',
            'id',
            'id'
        );
    }

    /**
     * Get all discussions associated with the reports created by this masyarakat.
     */
    public function diskusi()
    {
        return $this->hasManyThrough(
            Diskusi::class,
            Report::class,
            'id_masyarakat',
            'id_report',
            'id',
            'id'
        );
    }

    /**
     * Get all bookmarks associated with the reports created by this masyarakat.
     */
    public function bookmarks()
    {
        return $this->hasManyThrough(
            Bookmark::class,
            Report::class,
            'id_masyarakat',
            'id_report',
            'id',
            'id'
        );
    }
}
