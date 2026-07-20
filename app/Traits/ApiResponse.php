<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

// Unified JSON response shapes for API controllers. New endpoints should use
// these helpers; existing endpoints migrate gradually (their current shapes
// are a contract with the web & mobile clients, so each migration must land
// together with the matching client change).
trait ApiResponse
{
    protected function respondSuccess(mixed $data = null, ?string $message = null, int $status = 200): JsonResponse
    {
        $body = ['status' => 'success'];

        if ($message !== null) {
            $body['message'] = $message;
        }
        if ($data !== null) {
            $body['data'] = $data;
        }

        return response()->json($body, $status);
    }

    protected function respondError(string $message, int $status = 400): JsonResponse
    {
        return response()->json([
            'status'  => 'error',
            'message' => $message,
        ], $status);
    }
}
