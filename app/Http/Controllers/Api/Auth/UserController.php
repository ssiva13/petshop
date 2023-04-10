<?php
/**
 * Date 04/04/2023
 *
 * @author   Simon Siva <simonsiva13@gmail.com>
 */

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Requests\Auth\UpdateUserRequest;
use App\Http\Requests\Auth\UserLoginRequest;
use App\Http\Requests\Auth\UserRequest;
use App\Repositories\User\UserRepository;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Throwable;

/**
 * @OA\Tag(
 *     name="User",
 *     description="API endpoints for User"
 * )
 *
 */
class UserController extends ApiController
{
    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * @OA\Post(
     *      path="/user/create",
     *      operationId="create_user",
     *      tags={"User"},
     *      summary="Create a user",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\MediaType(
     *              mediaType="application/x-www-form-urlencoded",
     *              @OA\Schema(
     *                  schema="CreateRequest",
     *                  title="Create Request",
     *                  required={"first_name", "last_name", "email", "password", "password_confirmation", "address", "phone_number"},
     *                  @OA\Property(
     *                      property="first_name",
     *                      type="string",
     *                      description="User firstname"
     *                  ),
     *                  @OA\Property(
     *                      property="last_name",
     *                      type="string",
     *                      description="User lastname"
     *                  ),
     *                  @OA\Property(
     *                      property="email",
     *                      type="string",
     *                      description="User email"
     *                  ),
     *                  @OA\Property(
     *                      property="password",
     *                      type="string",
     *                      description="User password"
     *                  ),
     *                  @OA\Property(
     *                      property="password_confirmation",
     *                      type="string",
     *                      description="User password"
     *                  ),
     *                  @OA\Property(
     *                      property="avatar",
     *                      type="string",
     *                      description="User avatar- Image UUID"
     *                  ),
     *                  @OA\Property(
     *                      property="address",
     *                      type="string",
     *                      description="User main address"
     *                  ),
     *                  @OA\Property(
     *                      property="phone_number",
     *                      type="string",
     *                      description="User phone number"
     *                  ),
     *                  @OA\Property(
     *                      property="is_marketing",
     *                      type="string",
     *                      description="User marketing preferences",
     *                  ),
     *              ),
     *          ),
     *      ),
     *      @OA\Response(response=200, description="OK"),
     *      @OA\Response(response=500, description="Internal server error"),
     *      @OA\Response(response=401, description="Unauthorized"),
     *      @OA\Response(response=403, description="Forbidden"),
     *      @OA\Response(response=422, description="Unprocessable Entity"),
     *      @OA\Response(response=404, description="Not found"),
     * )
     *
     * @throws Throwable
     */
    public function store(UserRequest $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            $user = $this->userRepository->create($request->all());
            $authToken = $this->getAuthToken($user);
            $this->userRepository->saveAuthToken($authToken);
            $user->token = $authToken->toString();
            DB::commit();
            return $this->sendSuccessResponse($user, HttpResponse::HTTP_CREATED);
        } catch (Exception $exception) {
            DB::rollBack();
            return $this->throwError($exception->getMessage(), $exception->getTrace(), $exception->getCode());
        }
    }

    /**
     * @OA\Post(
     *      path="/user/login",
     *      operationId="login_user",
     *      tags={"User"},
     *      summary="Login a user Account",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\MediaType(
     *              mediaType="application/x-www-form-urlencoded",
     *              @OA\Schema(
     *                  schema="LoginRequest",
     *                  title="Login Request",
     *                  required={"email", "password" },
     *                  @OA\Property(
     *                      property="email",
     *                      type="string",
     *                      description="User email"
     *                  ),
     *                  @OA\Property(
     *                      property="password",
     *                      type="string",
     *                      description="User password"
     *                  ),
     *              ),
     *          ),
     *      ),
     *      @OA\Response(response=200, description="OK"),
     *      @OA\Response(response=500, description="Internal server error"),
     *      @OA\Response(response=401, description="Unauthorized"),
     *      @OA\Response(response=403, description="Forbidden"),
     *      @OA\Response(response=422, description="Unprocessable Entity"),
     *      @OA\Response(response=404, description="Not found"),
     * )
     *
     * @throws Throwable
     */
    public function login(UserLoginRequest $request): JsonResponse
    {
        try {
            if ($auth = Auth::attempt($request->validated())) {
                $user = Auth::user();
                if ($user->is_admin) {
                    return $this->sendErrorResponse(
                        'Failed to authenticate user!',
                        HttpResponse::HTTP_UNPROCESSABLE_ENTITY
                    );
                }
                $authToken = $this->getAuthToken($user);
                $this->userRepository->saveAuthToken($authToken);
                $response = [
                    'token' => $authToken->toString(),
                    'expiry' => $authToken->claims()->get('exp'),
                ];
                return $this->sendSuccessResponse($response);
            } else {
                return $this->sendErrorResponse('Failed to authenticate user!', HttpResponse::HTTP_FORBIDDEN);
            }
        } catch (Exception $exception) {
            return $this->throwError($exception->getMessage(), $exception->getTrace(), $exception->getCode());
        }
    }

    /**
     * @OA\Get(
     *      path="/user",
     *      operationId="user.profile",
     *      tags={"User"},
     *      security={{"bearerAuth":{}}},
     *      summary="Fetch a user profile and view account",
     *      @OA\Response(response=200, description="OK"),
     *      @OA\Response(response=500, description="Internal server error"),
     *      @OA\Response(response=401, description="Unauthorized"),
     *      @OA\Response(response=403, description="Forbidden"),
     *      @OA\Response(response=422, description="Unprocessable Entity"),
     *      @OA\Response(response=404, description="Not found"),
     * )
     */
    public function profile(Request $request): JsonResponse
    {
        $uuid = $this->getUserUuid($request->bearerToken());
        if (!$uuid) {
            return $this->sendErrorResponse('Unauthorized!', HttpResponse::HTTP_FORBIDDEN);
        }
        if (!$user = $this->userRepository->getByUUID($uuid)) {
            return $this->sendErrorResponse('User not found!', HttpResponse::HTTP_NOT_FOUND);
        }
        if ($user->uuid === $uuid) {
            return $this->sendSuccessResponse($user);
        }
        return $this->sendErrorResponse('Unauthorized!', HttpResponse::HTTP_FORBIDDEN);
    }

    /**
     * @OA\Put(
     *      path="/user/edit",
     *      operationId="edit_user",
     *      security={{"bearerAuth":{}}},
     *      tags={"User"},
     *      summary="Edit a user",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\MediaType(
     *              mediaType="application/x-www-form-urlencoded",
     *              @OA\Schema(
     *                  schema="EditRequest",
     *                  title="Edit Request",
     *                  required={"first_name", "last_name", "email", "password", "password_confirmation", "address", "phone_number", },
     *                  @OA\Property(
     *                      property="first_name",
     *                      type="string",
     *                      description="User firstname"
     *                  ),
     *                  @OA\Property(
     *                      property="last_name",
     *                      type="string",
     *                      description="User lastname"
     *                  ),
     *                  @OA\Property(
     *                      property="email",
     *                      type="string",
     *                      description="User email"
     *                  ),
     *                  @OA\Property(
     *                      property="password",
     *                      type="string",
     *                      description="User password"
     *                  ),
     *                  @OA\Property(
     *                      property="password_confirmation",
     *                      type="string",
     *                      description="User password"
     *                  ),
     *                  @OA\Property(
     *                      property="avatar",
     *                      type="string",
     *                      description="User avatar- Image UUID"
     *                  ),
     *                  @OA\Property(
     *                      property="address",
     *                      type="string",
     *                      description="User main address"
     *                  ),
     *                  @OA\Property(
     *                      property="phone_number",
     *                      type="string",
     *                      description="User phone number"
     *                  ),
     *                  @OA\Property(
     *                      property="is_marketing",
     *                      type="string",
     *                      description="User marketing preferences",
     *                  ),
     *              ),
     *          ),
     *      ),
     *      @OA\Response(response=200, description="OK"),
     *      @OA\Response(response=500, description="Internal server error"),
     *      @OA\Response(response=401, description="Unauthorized"),
     *      @OA\Response(response=403, description="Forbidden"),
     *      @OA\Response(response=422, description="Unprocessable Entity"),
     *      @OA\Response(response=404, description="Not found"),
     * )
     * @throws Throwable
     */
    public function edit(UpdateUserRequest $request): JsonResponse
    {
        $uuid = $this->getUserUuid($request->bearerToken());
        if (!$this->userRepository->getByUUID($uuid)) {
            return $this->sendErrorResponse('User not found!', HttpResponse::HTTP_NOT_FOUND);
        }
        DB::beginTransaction();
        try {
            $user = $this->userRepository->update($uuid, $request->except(['uuid']));
            DB::commit();
            return $this->sendSuccessResponse($user);
        } catch (Exception $exception) {
            DB::rollBack();
            return $this->throwError($exception->getMessage(), $exception->getTrace());
        }
    }

    /**
     * @OA\Get(
     *      path="/user/logout",
     *      operationId="logout_user",
     *      tags={"User"},
     *      summary="Logout a user",
     *      @OA\Response(response=200, description="OK"),
     *      @OA\Response(response=500, description="Internal server error"),
     *      @OA\Response(response=401, description="Unauthorized"),
     *      @OA\Response(response=403, description="Forbidden"),
     *      @OA\Response(response=422, description="Unprocessable Entity"),
     *      @OA\Response(response=404, description="Not found"),
     * )
     *
     * @throws Throwable
     */
    public function logout(Request $request): JsonResponse
    {
        $auth = Auth::guard();
        $auth->logout();
        return $this->sendSuccessResponse([]);
    }

    /**
     * @OA\Post(
     *      path="/user/forgot-password",
     *      operationId="forgot-password",
     *      tags={"User"},
     *      security={{"bearerAuth":{}}},
     *      summary="Creates a token to reset a user password",
     *
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\MediaType(
     *              mediaType="application/x-www-form-urlencoded",
     *              @OA\Schema(
     *                  schema="CreateRequest",
     *                  title="Create Request",
     *                  required={"email"},
     *                  @OA\Property(
     *                      property="email",
     *                      type="string",
     *                      description="User email"
     *                  ),
     *              ),
     *          ),
     *      ),
     *      @OA\Response(response=200, description="OK"),
     *      @OA\Response(response=500, description="Internal server error"),
     *      @OA\Response(response=401, description="Unauthorized"),
     *      @OA\Response(response=403, description="Forbidden"),
     *      @OA\Response(response=422, description="Unprocessable Entity"),
     *      @OA\Response(response=404, description="Not found"),
     * )
     */
    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        $uuid = $this->getUserUuid($request->bearerToken());
        if (!$user = $this->userRepository->getByUuidAndEmail($uuid, $request->get('email'))) {
            return $this->sendErrorResponse('User not found!', HttpResponse::HTTP_NOT_FOUND);
        }
        DB::beginTransaction();
        try {
            $token = $this->userRepository->getResetToken( user: $user );
            DB::commit();
            return $this->sendSuccessResponse(["reset_token" => $token]);
        } catch (Exception $exception) {
            DB::rollBack();
            return $this->throwError($exception->getMessage(), $exception->getTrace());
        }
    }

    /**
     * @OA\Post(
     *      path="/user/reset-password-token",
     *      operationId="reset-password-token",
     *      tags={"User"},
     *      security={{"bearerAuth":{}}},
     *      summary="Reset a user password with the a token",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\MediaType(
     *              mediaType="application/x-www-form-urlencoded",
     *              @OA\Schema(
     *                  schema="CreateRequest",
     *                  title="Create Request",
     *                  required={"email", "token", "password", "password_confirmation"},
     *                  @OA\Property(
     *                      property="token",
     *                      type="string",
     *                      description="User reset token"
     *                  ),
     *                  @OA\Property(
     *                      property="email",
     *                      type="string",
     *                      description="User email"
     *                  ),
     *                  @OA\Property(
     *                      property="password",
     *                      type="string",
     *                      description="User password"
     *                  ),
     *                  @OA\Property(
     *                      property="password_confirmation",
     *                      type="string",
     *                      description="User password"
     *                  ),
     *              ),
     *          ),
     *      ),
     *      @OA\Response(response=200, description="OK"),
     *      @OA\Response(response=500, description="Internal server error"),
     *      @OA\Response(response=401, description="Unauthorized"),
     *      @OA\Response(response=403, description="Forbidden"),
     *      @OA\Response(response=422, description="Unprocessable Entity"),
     *      @OA\Response(response=404, description="Not found"),
     * )
     */
    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $uuid = $this->getUserUuid($request->bearerToken());
        if (!$user = $this->userRepository->getByUuidAndEmail($uuid, $request->get('email'))) {
            return $this->sendErrorResponse('User not found!', HttpResponse::HTTP_NOT_FOUND);
        }
        DB::beginTransaction();
        try {
            $reset = $this->userRepository->resetPassword( $user, $request );
            if(!$reset){
                return $this->sendErrorResponse('Invalid or expired token!', HttpResponse::HTTP_UNPROCESSABLE_ENTITY);
            }
            DB::commit();
            return $this->sendSuccessResponse([
                "message" => "Password has been successfully updated"
            ]);
        } catch (Exception $exception) {
            DB::rollBack();
            return $this->throwError($exception->getMessage(), $exception->getTrace());
        }
    }

    /**
     * @OA\Get(
     *      path="/user/orders",
     *      operationId="user_orders",
     *      tags={"User"},
     *      security={{"bearerAuth":{}}},
     *      summary="List all orders for the user",
     *      @OA\Parameter(
     *          name="page",
     *          description="page",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="limit",
     *          description="limit",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="sortBy",
     *          description="sortBy",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="desc",
     *          description="desc",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="boolean"
     *          )
     *      ),
     *      @OA\Response(response=200, description="OK"),
     *      @OA\Response(response=500, description="Internal server error"),
     *      @OA\Response(response=401, description="Unauthorized"),
     *      @OA\Response(response=403, description="Forbidden"),
     *      @OA\Response(response=422, description="Unprocessable Entity"),
     *      @OA\Response(response=404, description="Not found"),
     * )
     */
    public function orders(Request $request): JsonResponse
    {
        $uuid = $this->getUserUuid($request->bearerToken());
        if (!$user = $this->userRepository->getByUUID($uuid)) {
            return $this->sendErrorResponse('User not found!', HttpResponse::HTTP_NOT_FOUND);
        }
        try {
            $data = [
                'page' => $request->get('page', 1),
                'limit' => $request->get('limit', 15),
                'sortBy' => $request->get('sortBy', 'created_at'),
                'desc' => $request->get('desc', true) ? 'desc' : 'asc',
            ];
            $orders = $this->userRepository->getUserOrders( $user, $data );
            return $this->sendSuccessResponse($orders);

        } catch (Exception $exception) {
            return $this->throwError($exception->getMessage(), $exception->getTrace());
        }
    }

    /**
     * @OA\Delete(
     *      path="/user",
     *      operationId="delete_user",
     *      tags={"User"},
     *      security={{"bearerAuth":{}}},
     *      summary="Delete a user account",
     *      @OA\Response(response=200, description="OK"),
     *      @OA\Response(response=500, description="Internal server error"),
     *      @OA\Response(response=401, description="Unauthorized"),
     *      @OA\Response(response=403, description="Forbidden"),
     *      @OA\Response(response=422, description="Unprocessable Entity"),
     *      @OA\Response(response=404, description="Not found"),
     * )

     *
     * @throws Throwable
     */
    public function delete(Request $request): JsonResponse
    {
        $uuid = $this->getUserUuid($request->bearerToken());
        if (!$user = $this->userRepository->getByUUID($uuid)) {
            return $this->sendErrorResponse('User not found!', HttpResponse::HTTP_NOT_FOUND);
        }
        DB::beginTransaction();
        try {
            if (!$this->userRepository->delete($uuid)) {
                return $this->sendErrorResponse('User not found!', HttpResponse::HTTP_NOT_FOUND);
            }
            DB::commit();
            return $this->sendSuccessResponse([]);
        } catch (Exception $exception) {
            DB::rollBack();
            return $this->throwError($exception->getMessage(), $exception->getTrace());
        }
    }


}
