<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;

trait ApiResponse
{
    protected function success(
        mixed $data = null,
        ?string $message = null,
        int $status = 200
    ): JsonResponse {
        $response = [
            'success' => true,
            'data' => $data,
        ];

        if ($message !== null) {
            $response['message'] = $message;
        }

        return response()->json(
            $response,
            $status
        );
    }

    protected function paginated(
        LengthAwarePaginator $paginator,
        mixed $data = null
    ): JsonResponse {
        return response()->json([
            'success' => true,
            'data' => $data ?? $paginator->items(),
            'pagination' => [
                'current_page' =>
                    $paginator->currentPage(),
                'per_page' =>
                    $paginator->perPage(),
                'total' =>
                    $paginator->total(),
                'last_page' =>
                    $paginator->lastPage(),
            ],
        ]);
    }

    protected function error(
        string $message,
        int $status = 400,
        mixed $errors = null
    ): JsonResponse {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return response()->json(
            $response,
            $status
        );
    }
}