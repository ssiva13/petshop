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
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Throwable;

class UserController extends ApiController
{
    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
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

    public function logout(Request $request): JsonResponse
    {
        $auth = Auth::guard();
        $auth->logout();
        return $this->sendSuccessResponse([]);
    }

    /**
     * @throws Throwable
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
     * @throws Throwable
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
     * @param Request $request
     * @return JsonResponse
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
