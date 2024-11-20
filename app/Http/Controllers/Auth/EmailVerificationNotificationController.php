<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class uUsernameVerificationNotificationController extends Controller
{
    /**
     * Send a new username verification notification.
     */
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        if ($request->user()->hasVerifieduUsername()) {
            return redirect()->intended('/dashboard');
        }

        $request->user()->senduUsernameVerificationNotification();

        return response()->json(['status' => 'verification-link-sent']);
    }
}
