<?php

namespace App\Trait;

use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @method JsonResponse json(mixed $data, int $status = 200, array $headers = [], array $context = [])
 */
trait ApiResponseTrait
{
    public function success(mixed $data = null, int $statusCode = 200, array $groups = []): JsonResponse
    {
        return $this->json([
            'status' => 'success',
            'data' => $data,
        ], $statusCode, [], ['groups' => $groups]);
    }

    public function fail(array $validationErrors, int $statusCode = 400): JsonResponse
    {
        return $this->json([
            'status' => 'fail',
            'data' => $validationErrors,
        ], $statusCode);
    }

    public function error(string $message, int $code = 0, mixed $data = null, int $statusCode = 500): JsonResponse
    {
        $response = [
            'status' => 'error',
            'message' => $message,
        ];

        if ($code !== 0) {
            $response['code'] = $code;
        }

        if ($data !== null) {
            $response['data'] = $data;
        }

        return $this->json($response, $statusCode);
    }
}
