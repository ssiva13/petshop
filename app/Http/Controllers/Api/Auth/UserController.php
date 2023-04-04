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
use App\Repositories\User\UserRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UserController extends ApiController
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
            $user = $this->userRepository->create( $request->all());
            $authToken = $this->getAuthToken($user);
            $this->userRepository->saveAuthToken($authToken);

            $success = [
                'user' => $user,
                'token' => $authToken->toString(),
                'expiry' => $authToken->claims()->get('exp'),
            ];

            DB::commit();
            return $this->sendResponse($success, 'User registered successfully', 201);
        } catch (\Exception $exception) {
            DB::rollback();
            return $this->sendError('error', $exception->getMessage(), 500);
        }
    }

    public function login(UserLoginRequest $request): JsonResponse
    {
        try{
             if($auth = Auth::attempt($request->validated())) {
                 $user = Auth::user();
                 $authToken = $this->getAuthToken($user);
                 $this->userRepository->saveAuthToken($authToken);
                 $success = [
                     'token' => $authToken->toString(),
                     'expiry' => $authToken->claims()->get('exp'),
                 ];
                 return $this->sendResponse($success, 'Successful', 200);
             } else{
                 return $this->sendError('error', 'Wrong Password!', 403);
             }
        } catch (\Exception $exception) {
            return $this->sendError('error', $exception->getMessage(), 500);
        }
    }

    public function profile(Request $request): JsonResponse
    {
        $uuid = $request->get('uuid');
        $user = $this->userRepository->getByUUID($uuid);

        return $this->sendResponse($user, 'User Profile');
    }

}
