<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Services\RegistrationService;
use App\Http\Traits\ApiResponseTrait;
use Illuminate\Http\Response;

class RegisteredUserController extends Controller
{
    use ApiResponseTrait;

    public function __construct(protected RegistrationService $registrationService)
    {
    }
    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(RegisterRequest $request): Response
    {

        $this->registrationService->register(
            $request->validated(),
            $request->file('foto')
        );

        return $this->success([], Response::HTTP_NO_CONTENT);
    }

}
