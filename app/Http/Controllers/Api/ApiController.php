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
use Illuminate\Http\Request;
use Lcobucci\JWT\Token\Parser;
use Lcobucci\JWT\UnencryptedToken;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

/**
 * @OA\Info(
 *      version="1.0.0",
 *      title="Pet Shop API - Swagger Documentation",
 *      @OA\Contact(
 *          email="simonsiva13@gmail.com"
 *      ),
 *      @OA\License(
 *          name="Apache 2.0",
 *          url="https://www.apache.org/licenses/LICENSE-2.0.html"
 *      ),
 * )
 * @OA\SecurityScheme(
 *     type="http",
 *     securityScheme="bearerAuth",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 * @OA\Server(
 *      url=L5_SWAGGER_CONST_HOST,
 *      description="Pet Shop API Server"
 * )
 */

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
     * @return JsonResponse
     */
    protected function throwError(
        $message,
        array $errorTrace = [],
        int $status = HttpResponse::HTTP_INTERNAL_SERVER_ERROR
    ): JsonResponse {
        $response = [
            'success' => false,
            'data' => [],
            'error' => $message,
            'errors' => [],
            'trace' => $errorTrace,
        ];

        return response()->json($response, 500);
    }

    protected function getUserUuid($token)
    {
        $token = app(Parser::class, ['token' => $token]);
        return $token->claims()->get('user_uuid');
    }

}
