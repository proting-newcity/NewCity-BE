<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

use App\Models\Pemerintah;
use App\Models\Masyarakat;
use App\Models\user;
use App\Http\Services\AdminService;

class AdminController extends Controller
{
    protected $adminService;
    private const ERROR_UNAUTHORIZED = 'You are not authorized!';
    private const RULE_REQUIRED_STRING = 'required|string';

    public function __construct(AdminService $adminService)
    {
        $this->adminService = $adminService;
    }

    /**
     * Register a new Pemerintah account.
     */
    public function storePemerintah(Request $request)
    {
        if (!$this->checkRole("admin")) {
            return response()->json(['error' => self::ERROR_UNAUTHORIZED], 401);
        }

        try {
            $request->validate([
                'name'         => self::RULE_REQUIRED_STRING,
                'username'     => 'required|string|max:255|unique:user',
                'phone'        => self::RULE_REQUIRED_STRING,
                'password'     => ['required', Rules\Password::defaults()],
                'institusi_id' => 'nullable|exists:institusi,id',
                'foto'         => 'nullable|image|mimes:jpeg,png,jpg,gif',
                'status'       => 'required|boolean',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        }

        $this->adminService->storePemerintah($request->all(), $request->file('foto'));

        return response()->noContent();
    }

    /**
     * Update an existing Pemerintah account.
     */
    public function updatePemerintah(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name'         => 'nullable|string|max:255',
            'username'     => "nullable|string|max:255|unique:user,username,$id",
            'phone'        => 'nullable|string|max:255',
            'password'     => ['nullable', Rules\Password::defaults()],
            'institusi_id' => 'nullable|exists:institusi,id',
            'foto'         => 'nullable|image|mimes:jpeg,png,jpg,gif',
            'status'       => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $result = $this->adminService->updatePemerintah($id, $request->all(), $request->file('foto'));
        $httpCode = isset($result['error']) ? $result['error_code'] : 200;
        return response()->json($result, $httpCode);
    }


    /**
     * Display a paginated list of Pemerintah.
     */
    public function indexPemerintah()
    {
        if (!$this->checkRole("admin")) {
            return response()->json(['error' => self::ERROR_UNAUTHORIZED], 401);
        }
        $data = $this->adminService->getPemerintahPaginated();
        return response()->json($data, 200);
    }

    /**
     * Show details for a given Pemerintah.
     */
    public function showPemerintah($id)
    {
        if (!$this->checkRole("admin")) {
            return response()->json(['error' => self::ERROR_UNAUTHORIZED], 401);
        }

        $data = $this->adminService->getPemerintahDetails($id);
        if (isset($data['error'])) {
            return response()->json($data, 404);
        }
        return response()->json($data, 200);
    }

    /**
     * Search Pemerintah users.
     */
    public function searchPemerintah(Request $request)
    {
        $search = $request->input('search');
        $data = $this->adminService->searchPemerintah($search);
        return response()->json($data, 200);
    }

    /**
     * Delete a Pemerintah account and its related user.
     */
    public function destroyPemerintah($id)
    {
        if (!$this->checkRole("admin")) {
            return response()->json(['error' => self::ERROR_UNAUTHORIZED], 401);
        }
        $result = $this->adminService->deletePemerintah($id);
        $code = isset($result['error']) ? 404 : 200;
        return response()->json($result, $code);
    }

    /**
     * Search a Masyarakat by phone.
     */
    public function searchMasyarakatByPhone(Request $request)
    {
        $search = $request->input('search');
        $result = $this->adminService->findMasyarakatByPhone($search);
        $code = isset($result['error']) ? 404 : 200;
        return response()->json($result, $code);
    }

    /**
     * Update password for a user.
     */
    public function ubahPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'new_password' => ['required', Rules\Password::defaults()],
            'username'     => self::RULE_REQUIRED_STRING,
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $result = $this->adminService->ubahPassword($request->input('username'), $request->input('new_password'));
        $code = isset($result['error']) ? 404 : 200;
        return response()->json($result, $code);
    }
}
