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

    /**
     * Get the role of the user (admin, masyarakat, or pemerintah)
     * @return string|null
     */
    public function getRoles()
    {
        $this->admin;
        $this->masyarakat;
        $this->pemerintah;
    }

    /**
     * Relationship to Admin model
     */
    public function admin()
    {
        return $this->hasOne(Admin::class, 'id', 'id');
    }

    /**
     * Relationship to Masyarakat model
     */
    public function masyarakat()
    {
        return $this->hasOne(Masyarakat::class, 'id', 'id');
    }

    /**
     * Relationship to Pemerintah model
     */
    public function pemerintah()
    {
        return $this->hasOne(Pemerintah::class, 'id', 'id');
    }

    // Relation to RatingReport
    public function likesReport()
    {
        return $this->hasMany(RatingReport::class, 'id_user', 'id');
    }

    // Check Like by report id
    public function hasLikedReport($id_report)
    {
        return $this->likesReport()->where('id_report', $id_report)->exists();
    }

    // Toggle Like Report
    public function toggleLikeReport($id_report, $loaded)
    {
        if ($loaded) {
            $count = RatingReport::all()->where('id_report', $id_report);
            return [
                'count' => count($count),
                'hasLikedReport' => $this->hasLikedReport($id_report),
            ];
        }

        $existingLike = $this->likesReport()->where('id_report', $id_report)->first();
        if ($existingLike) {
            if ($loaded) {
                $count = RatingReport::all()->where('id_report', $id_report);
                return [
                    'count' => count($count),
                    'hasLikedReport' => true,
                ];
            }

            $existingLike->where('id_report', $id_report)->delete();
            $count = RatingReport::all()->where('id_report', $id_report);

            return [
                'count' => count($count),
                'hasLikedReport' => false,
            ];
        } else {
            $this->likesReport()->create([
                'id_report' => $id_report,
            ]);
        }
        $count = RatingReport::all()->where('id_report', $id_report);

        return [
            'count' => count($count),
            'hasLikedReport' => $this->hasLikedReport($id_report)
        ];
    }

    // Relation to RatingBerita
    public function likesBerita()
    {
        return $this->hasMany(RatingBerita::class, 'id_user', 'id');
    }

    // Check Like by Berita id
    public function hasLikedBerita($id_berita)
    {
        return $this->likesBerita()->where('id_berita', $id_berita)->exists();
    }

    // Toggle Like Berita
    public function toggleLikeBerita($id_berita,$loaded)
    {
        if ($loaded) {
            $count = RatingBerita::all()->where('id_berita', $id_berita);
            return [
                'count' => count($count),
                'hasLikedBerita' => $this->hasLikedBerita($id_berita),
            ];
        }

        $existingLike = $this->likesBerita()->where('id_berita', $id_berita)->first();
        if ($existingLike) {
            if ($loaded) {
                $count = RatingBerita::all()->where('id_berita', $id_berita);
                return [
                    'count' => count($count),
                    'hasLikedBerita' => true,
                ];
            }

            $existingLike->where('id_berita', $id_berita)->delete();
            $count = RatingBerita::all()->where('id_berita', $id_berita);

            return [
                'count' => count($count),
                'hasLikedBerita' => false
            ];
        } else {
            $this->likesBerita()->create([
                'id_berita' => $id_berita,
            ]);
        }

        $count = RatingBerita::all()->where('id_berita', $id_berita);
        return [
            'count' => count($count),
            'hasLikedBerita' => $this->hasLikedBerita($id_berita)
        ];
    }

    // Relation to Diskusi
    public function diskusi()
    {
        return $this->hasMany(Diskusi::class, 'id_user', 'id');
    }

    // Check Like by diskusi id
    public function hasDiskusi($id_report)
    {
        return $this->diskusi()->where('id_report', $id_report)->exists();
    }

    // create disscusion
    public function sendDiskusi($id_report, $content)
    {
        $this->diskusi()->create([
            'id_report' => $id_report,
            'content' => $content
        ]);
    }

    // Relation to BookMark
    public function bookmark()
    {
        return $this->hasMany(Bookmark::class, 'id_user', 'id');
    }

    // Check Like by report id
    public function hasBookmark($id_report)
    {
        return $this->bookmark()->where('id_report', $id_report)->exists();
    }

    // toggle bookmark
    public function toggleBookmark($id_report, $loaded)
    {
        if ($loaded) {
            $count = Bookmark::all()->where('id_report', $id_report);
            return [
                'count' => count($count),
                'hasBookmark' => $this->hasBookmark($id_report),
            ];
        }
        
        $existingBookmark = $this->bookmark()->where('id_report', $id_report)->first();
        if ($existingBookmark) {
            if ($loaded) {
                $count = Bookmark::all()->where('id_report', $id_report);
                return [
                    'count' => count($count),
                    'hasBookmark' => true,
                ];
            }
            $existingBookmark->where('id_report', $id_report)->delete();
            $count = Bookmark::all()->where('id_report', $id_report);
            return [
                'count' => count($count),
                'hasBookmark' => false,
            ];
        } else {
            $this->bookmark()->create([
                'id_report' => $id_report,
            ]);
        }

        $count = Bookmark::all()->where('id_report', $id_report);
        return [
            'count' => count($count),
            'hasBookmark' => $this->hasBookmark($id_report)
        ];
    }
}
