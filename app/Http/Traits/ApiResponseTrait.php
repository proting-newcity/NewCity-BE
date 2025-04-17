<?php

namespace App\Traits;

trait ApiResponseTrait
{
    protected function success($data = [], int $status = 200)
    {
        return response()->json($data, $status);
    }

    protected function error(string $message, int $status = 400, $errors = [])
    {
        $payload = ['message' => $message];
        if ($errors) {
            $payload['errors'] = $errors;
        }
        return response()->json($payload, $status);
    }
}
