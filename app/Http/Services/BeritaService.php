<?php

namespace App\Http\Services;

use App\Models\Berita;
use App\Models\RatingBerita;
use Illuminate\Support\Facades\Auth;
use App\Http\Traits\ImageUploadTrait;

class BeritaService
{
    use ImageUploadTrait;

    /**
     * Get paginated Berita entries with related kategori and user data.
     */
    public function getPaginatedBerita()
    {
        $berita = Berita::with([
            'kategori' => function ($query) {
                $query->select('id', 'name', 'foto');
            },
            'user' => function ($query) {
                $query->select('id', 'name');
            }
        ])->paginate(10);

        // Append like count and user like status.
        $berita->getCollection()->transform(function ($item) {
            $item->like_count = RatingBerita::where('id_berita', $item->id)->count();
            $item->hasLiked = auth('sanctum')->check()
                ? auth('sanctum')->user()->toggleLikeBerita($item->id, true)
                : false;
            return $item;
        });

        return $berita;
    }

    /**
     * Retrieve details for a specific berita.
     */
    public function getBeritaDetails($id)
    {
        return Berita::with([
            'kategori' => function ($query) {
                $query->select('id', 'name', 'foto');
            },
            'user' => function ($query) {
                $query->select('id', 'name');
            }
        ])->find($id);
    }

    /**
     * Get Berita entries filtered by category.
     */
    public function getBeritaByCategory($categoryId)
    {
        return Berita::with([
            'kategori' => function ($query) {
                $query->select('id', 'name', 'foto');
            },
            'user' => function ($query) {
                $query->select('id', 'name');
            }
        ])
            ->where('id_kategori', $categoryId)
            ->paginate(10);
    }

    /**
     * Create a new Berita entry.
     */
    public function createBerita(array $data, $foto)
    {
        $fotoPath = $this->uploadImage($foto, 'berita');

        return Berita::create([
            'title'      => $data['title'],
            'content'    => $data['content'],
            'foto'       => $fotoPath,
            'status'     => $data['status'],
            'id_kategori'=> $data['id_kategori'],
            'id_user'    => Auth::user()->id,
        ]);
    }

    /**
     * Update an existing Berita entry.
     */
    public function updateBerita($id, array $data, $newFoto = null)
    {
        $berita = Berita::find($id);
        if (!$berita) {
            return ['error' => 'Berita not found', 'error_code' => 404];
        }

        $berita->update(array_intersect_key($data, array_flip(['title', 'content', 'status', 'id_kategori'])));

        if ($newFoto) {
            if ($berita->foto) {
                $this->deleteImage($berita->foto);
            }
            $berita->foto = $this->uploadImage($newFoto, 'berita');
        }
        $berita->save();
        return $berita;
    }

    /**
     * Delete a Berita entry.
     */
    public function deleteBerita($id)
    {
        $berita = Berita::find($id);
        if (!$berita) {
            return ['error' => 'Berita not found'];
        }
        if ($berita->foto) {
            $this->deleteImage($berita->foto);
        }
        $berita->delete();
        return ['message' => 'Berita deleted successfully'];
    }

    /**
     * Search Berita entries by title, content, or status.
     */
    public function searchBerita($search)
    {
        return Berita::where('title', 'like', "%{$search}%")
            ->orWhere('content', 'like', "%{$search}%")
            ->orWhere('status', 'like', "%{$search}%")
            ->with([
                'kategori' => function ($query) {
                    $query->select('id', 'name', 'foto');
                },
                'user' => function ($query) {
                    $query->select('id', 'name');
                }
            ])
            ->paginate(10);
    }

    /**
     * Toggle the like status for a given Berita.
     */
    public function toggleLikeBerita($beritaId)
    {
        $berita = Berita::find($beritaId);
        if (!$berita) {
            return false;
        }
        return auth()->user()->toggleLikeBerita($berita->id, false);
    }
}
