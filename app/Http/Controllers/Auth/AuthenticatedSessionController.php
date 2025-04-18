<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Services\AuthService;
use App\Http\Traits\ApiResponseTrait;

class AuthenticatedSessionController extends Controller
{

    use ApiResponseTrait;

    public function __construct(protected AuthService $authService)
    {
    }
    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request)
    {
        $always = $request->boolean('always_signed_in');
        $result = $this->authService->login(
            $request->only(['username', 'password']),
            $always
        );

        if (isset($result['error'])) {
            return $this->error($result['error'], $result['status']);
        }

        return $this->success($result['data'], $result['status']);
    }



    /**
     * Logout and invalidate the token.
     */
    public function destroy(Request $request): Response
    {
        $this->authService->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->noContent();
    }
}
