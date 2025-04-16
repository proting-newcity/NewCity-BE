<?php

namespace App\Http\Services;

use App\Models\Institusi;

class InstitusiService
{
    /**
     * Get all institusi records.
     */
    public function getAll()
    {
        return Institusi::all();
    }

    /**
     * Find an institusi record by its ID.
     */
    public function findById($id)
    {
        return Institusi::find($id);
    }

    /**
     * Create a new institusi record.
     */
    public function create(array $data)
    {
        return Institusi::create($data);
    }

    /**
     * Update an existing institusi record.
     */
    public function update($id, array $data)
    {
        $institusi = $this->findById($id);
        if ($institusi) {
            $institusi->update($data);
        }
        return $institusi;
    }

    /**
     * Delete an institusi record.
     */
    public function delete($id)
    {
        $institusi = $this->findById($id);
        if ($institusi) {
            $institusi->delete();
        }
        return $institusi;
    }
}
