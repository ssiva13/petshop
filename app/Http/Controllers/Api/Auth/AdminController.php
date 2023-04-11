<?php
/**
 * Date 04/04/2023
 *
 * @author   Simon Siva <simonsiva13@gmail.com>
 */

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Api\ApiController;
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
 *     name="Admin",
 *     description="API endpoints for Admin"
 * )
 *
 */
class AdminController extends ApiController
{
    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * @OA\Post(
     *      path="/admin/create",
     *      operationId="create_admin",
     *      tags={"Admin"},
     *      summary="Create a admin account",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\MediaType(
     *              mediaType="application/x-www-form-urlencoded",
     *              @OA\Schema(
     *                  schema="CreateRequest",
     *                  title="Create Request",
     *                  required={"first_name", "last_name", "email", "password", "password_confirmation", "avatar", "address", "phone_number"},
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
            $user = $this->userRepository->create($request->all(), true);
            $authToken = $this->getAuthToken($user);
            $this->userRepository->saveAuthToken($authToken);
            $user->token = $authToken->toString();
            DB::commit();
            return $this->sendSuccessResponse($user, HttpResponse::HTTP_CREATED);
        } catch (Exception $exception) {
            DB::rollback();
            return $this->throwError($exception->getMessage(), $exception->getTrace(), $exception->getCode());
        }
    }

    /**
     * @OA\Post(
     *      path="/admin/login",
     *      operationId="login_admin",
     *      tags={"Admin"},
     *      summary="Login a Admin Account",
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
     *                      description="Admin email"
     *                  ),
     *                  @OA\Property(
     *                      property="password",
     *                      type="string",
     *                      description="Admin password"
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
                if (!$user->is_admin) {
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
     *      path="/admin/logout",
     *      operationId="logout_admin",
     *      tags={"Admin"},
     *      summary="Logout an Admin account",
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
     * @OA\Get(
     *      path="/admin/user-listing",
     *      operationId="user-listing",
     *      security={{"bearerAuth":{}}},
     *      tags={"Admin"},
     *      summary="List all the users",
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

     *      @OA\Parameter(
     *          name="first_name",
     *          description="first_name",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="email",
     *          description="email",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="phone",
     *          description="phone",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="address",
     *          description="address",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="created_at",
     *          description="created_at",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="marketing",
     *          description="marketing",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="string",
     *              enum={"0", "1"}
     *          )
     *      ),
     *
     *      @OA\Response(response=200, description="OK"),
     *      @OA\Response(response=500, description="Internal server error"),
     *      @OA\Response(response=401, description="Unauthorized"),
     *      @OA\Response(response=403, description="Forbidden"),
     *      @OA\Response(response=422, description="Unprocessable Entity"),
     *      @OA\Response(response=404, description="Not found"),
     * )
     */
    public function allUsers(Request $request): JsonResponse
    {
        $data = [
            'first_name' => $request->get('first_name', null),
            'email' => $request->get('email', null),
            'phone' => $request->get('phone', null),
            'address' => $request->get('address', null),
            'marketing' => $request->get('marketing', null),
            'created_at' => $request->get('created_at', null),
            'page' => $request->get('page', 1),
            'limit' => $request->get('limit', 15),
            'sortBy' => $request->get('sortBy', 'created_at'),
            'desc' => $request->get('desc', true) ? 'desc' : 'asc',
        ];
        $users = $this->userRepository->getPaginated($data);
        return $this->sendSuccessResponse($users, errors: null, extra: null);
    }

    /**
     * @OA\Put(
     *      path="/admin/user-edit/{uuid}",
     *      operationId="admin_edit_user",
     *      tags={"Admin"},
     *      security={{"bearerAuth":{}}},
     *      summary="Edit a user account",
     *      @OA\Parameter(
     *          name="uuid",
     *          description="uuid",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
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
    public function editUser($uuid, UpdateUserRequest $request): JsonResponse
    {
        if (!$user = $this->userRepository->getByUUID($uuid)) {
            return $this->sendErrorResponse('User not found!', HttpResponse::HTTP_NOT_FOUND);
        }
        DB::beginTransaction();
        try {
            $user = $this->userRepository->update($uuid, $request->except(['uuid']));
            DB::commit();
            return $this->sendSuccessResponse($user);
        } catch (Exception $exception) {
            DB::rollback();
            return $this->throwError($exception->getMessage(), $exception->getTrace());
        }
    }

    /**
     * @OA\Delete(
     *      path="/admin/user-delete/{uuid}",
     *      operationId="admin_delete_user",
     *      tags={"Admin"},
     *      security={{"bearerAuth":{}}},
     *      summary="Delete a user account",
     *      @OA\Parameter(
     *          name="uuid",
     *          description="uuid",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="string"
     *          )
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
    public function deleteUser($uuid): JsonResponse
    {
        DB::beginTransaction();
        try {
            if (!$this->userRepository->delete($uuid)) {
                return $this->sendErrorResponse('User not found!', HttpResponse::HTTP_NOT_FOUND);
            }
            DB::commit();
            return $this->sendSuccessResponse([]);
        } catch (Exception $exception) {
            DB::rollback();
            return $this->throwError($exception->getMessage(), $exception->getTrace());
        }
    }

}
