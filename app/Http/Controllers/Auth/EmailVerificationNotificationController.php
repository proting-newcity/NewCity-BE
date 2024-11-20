<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class UsernameVerificationNotificationController extends Controller
{
    /**
     * Send a new username verification notification.
     */
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        if ($request->user()->hasVerifiedUsername()) {
            return redirect()->intended('/dashboard');
        }

        $request->user()->sendUsernameVerificationNotification();

        return response()->json(['status' => 'verification-link-sent']);
    }
}
