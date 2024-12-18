<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyUsername;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;
    protected $table = 'user';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'username',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'username_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Relation to RatingReport
    public function likesReport()
    {
        return $this->hasMany(RatingReport::class, 'id_user','id');
    }

     // Check Like by report id
    public function hasLikedReport($id_report)
    {
        return $this->likesReport()->where('id_report', $id_report)->exists();
    }

    // Toggle Like Report
    public function toggleLikeReport($id_report){
        $existingLike = $this->likesReport()->where('id_report', $id_report)->first();
        if ($existingLike) {
            $existingLike->where('id_report', $id_report)->delete();

            return [
                'hasLikedReport' => false,
            ];
        } else {
            $this->likesReport()->create([
                'id_report' => $id_report,
            ]);
        }

        return [
            'hasLikedReport' => $this->hasLikedReport($id_report)
        ];
    }

    // Relation to RatingBerita
    public function likesBerita()
    {
        return $this->hasMany(RatingBerita::class, 'id_user','id');
    }

    // Check Like by Berita id
    public function hasLikedBerita($id_berita)
    {
        return $this->likesBerita()->where('id_berita', $id_berita)->exists();
    }

    // Toggle Like Berita
    public function toggleLikeBerita($id_berita){
        $existingLike = $this->likesBerita()->where('id_berita', $id_berita)->first();
        if ($existingLike) {
            $existingLike->where('id_berita', $id_berita)->delete();

            return [
                'toggleLikeBerita' => false
            ];
        } else {
            $this->likesBerita()->create([
                'id_berita' => $id_berita,
            ]);
        }

        return [
            'hasLikedBerita' => $this->hasLikedBerita($id_berita)
        ];
    }
}
