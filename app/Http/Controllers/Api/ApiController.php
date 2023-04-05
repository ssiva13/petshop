<?php
/**
 * Date 04/04/2023
 *
 * @author   Simon Siva <simonsiva13@gmail.com>
 */

namespace App\Http\Controllers\Api;

use App\Handlers\AuthHandler;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Lcobucci\JWT\UnencryptedToken;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class ApiController extends Controller
{
    public function sendSuccessResponse($data, $status = HttpResponse::HTTP_OK, $errors = [], $extra = []): JsonResponse
    {
        $response = [
            'success' => true,
            'data' => $data,
            'error' => null,
            'errors' => $errors,
            'extra' => $extra,
        ];
        return response()->json($response, $status);
    }

    public function sendErrorResponse($message, $status = HttpResponse::HTTP_UNAUTHORIZED, $trace = []): JsonResponse
    {
        $response = [
            'success' => false,
            'data' => [],
            'error' => $message,
            'errors' => [],
            'trace' => $trace,
        ];
        return response()->json($response, $status);
    }

    protected function getAuthToken($user): UnencryptedToken
    {
        $authHandler = new AuthHandler();
        return $authHandler->generateToken($user);
    }

    /**
     * @param $message
     * @param array $errorTrace
     * @param int $status
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function throwError($message, array $errorTrace = [], int $status = HttpResponse::HTTP_INTERNAL_SERVER_ERROR): JsonResponse
    {
        $response = [
            'success' => false,
            'data' => [],
            'error' => $message,
            'errors' => [],
            'trace' => $errorTrace,
        ];

        return response()->json($response, $status ?: 500);
    }
}
