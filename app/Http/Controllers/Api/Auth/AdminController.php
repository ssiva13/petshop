<?php
/**
 * Date 04/04/2023
 *
 * @author   Simon Siva <simonsiva13@gmail.com>
 */

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\UserLoginRequest;
use App\Http\Requests\UserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Repositories\User\UserRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class AdminController extends ApiController
{
    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * @throws \Throwable
     */
    public function store(UserRequest $request): JsonResponse
    {
        DB::beginTransaction();
        try{
            $user = $this->userRepository->create( $request->all(), true);
            $authToken = $this->getAuthToken($user);
            $this->userRepository->saveAuthToken($authToken);
            $user->token = $authToken->toString();
            DB::commit();
            return $this->sendSuccessResponse($user, HttpResponse::HTTP_CREATED);
        } catch (\Exception $exception) {
            DB::rollback();
            return $this->throwError($exception->getMessage(), $exception->getTrace(), $exception->getCode());
        }
    }

    public function login(UserLoginRequest $request): JsonResponse
    {
        try{
            if($auth = Auth::attempt($request->validated())) {
                $user = Auth::user();
                if(!$user->is_admin){
                    return $this->sendErrorResponse('Failed to authenticate user!', HttpResponse::HTTP_UNPROCESSABLE_ENTITY);
                }

                $authToken = $this->getAuthToken($user);
                $this->userRepository->saveAuthToken($authToken);
                $response = [
                    'token' => $authToken->toString(),
                    'expiry' => $authToken->claims()->get('exp'),
                ];
                return $this->sendSuccessResponse($response);

            } else{
                return $this->sendErrorResponse('Failed to authenticate user!', HttpResponse::HTTP_FORBIDDEN);
            }
        } catch (\Exception $exception) {
            return $this->throwError($exception->getMessage(), $exception->getTrace(), $exception->getCode());
        }
    }

    public function logout( Request $request ): JsonResponse
    {
        $auth = Auth::guard();
        $auth->logout();
        return $this->sendSuccessResponse([]);
    }

    public function allUsers(Request $request): JsonResponse
    {
        $data = [
            "first_name" =>  $request->get('first_name', null),
            "email" =>  $request->get('email', null),
            "phone" =>  $request->get('phone', null),
            "address" =>  $request->get('address', null),
            "marketing" =>  $request->get('marketing', null),
            "created_at" =>  $request->get('created_at', null),
            "page" =>  $request->get('page', 1),
            "limit" =>  $request->get('limit', 15),
            "sortBy" =>  $request->get('sortBy', 'created_at'),
            "desc" =>  $request->get('desc', true) ? 'desc' : 'asc',
        ];
        $users = $this->userRepository->getPaginated($data);
        return $this->sendSuccessResponse($users, errors: null, extra: null);
    }

    /**
     * @throws \Throwable
     */
    public function editUser($uuid, UpdateUserRequest $request): JsonResponse
    {
        if(!$user = $this->userRepository->getByUUID($uuid)){
            return $this->sendErrorResponse('User not found!', HttpResponse::HTTP_NOT_FOUND);
        }
        DB::beginTransaction();
        try{
            $user->update( $request->except(['uuid']) );
            DB::commit();
            return $this->sendSuccessResponse($user);
        } catch (\Exception $exception) {
            DB::rollback();
            return $this->throwError($exception->getMessage(), $exception->getTrace());
        }
    }

}
