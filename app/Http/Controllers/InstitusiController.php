<?php

namespace App\Http\Controllers;

use App\Models\Institusi;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class InstitusiController extends Controller
{
    // GET: api/institusi
    public function index()
    {
        $institusis = Institusi::all();
        return response()->json($institusis);
    }

    // GET: api/institusi/{id}
    public function show($id)
    {
        $institusi = Institusi::find($id);

        if (!$institusi) {
            return response()->json(['message' => 'Institusi not found'], Response::HTTP_NOT_FOUND);
        }

        return response()->json($institusi);
    }

    // POST: api/institusi
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $institusi = Institusi::create([
            'name' => $request->name,
        ]);

        return response()->json($institusi, Response::HTTP_CREATED);
    }

    // PUT/PATCH: api/institusi/{id}
    public function update(Request $request, $id)
    {
        $institusi = Institusi::find($id);

        if (!$institusi) {
            return response()->json(['message' => 'Institusi not found'], Response::HTTP_NOT_FOUND);
        }

        // Validate the incoming request
        $request->validate([
            'name' => 'nullable|string|max:255',
        ]);

        // Update the attributes
        if ($request->has('name')) {
            $institusi->name = $request->name;
        }

        $institusi->save();

        return response()->json($institusi);
    }

    // DELETE: api/institusi/{id}
    public function destroy($id)
    {
        $institusi = Institusi::find($id);

        if (!$institusi) {
            return response()->json(['message' => 'Institusi not found'], Response::HTTP_NOT_FOUND);
        }

        $institusi->delete();

        return response()->json(['message' => 'Institusi deleted successfully']);
    }
}
