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

class ApiController extends Controller
{
    public function sendResponse($data, $message, $status = 200): JsonResponse
    {
        $response = [
            'success' => true,
            'data' => $data,
            'message' => $message,
        ];
        return response()->json($response, $status);
    }

    protected function getAuthToken($user): UnencryptedToken
    {
        $authHandler = new AuthHandler();
        return $authHandler->generateToken($user);
    }

    protected function sendError($message, $errorData = [], $status = 400): JsonResponse
    {
        $response = [
            'message' => $message,
        ];
        if (!empty($errorData)) {
            $response['data'] = $errorData;
        }
        return response()->json($response, $status);
    }
}
