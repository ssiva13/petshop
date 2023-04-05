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
use Lcobucci\JWT\Token\Parser;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

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
                 if($user->is_admin){
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

    public function profile(Request $request): JsonResponse
    {
        $token = app(Parser::class, ['token' => $request->bearerToken()]);
        $uuid = $token->claims()->get('uuid');
        if(!$uuid){
            return $this->sendErrorResponse('Unauthorized!', HttpResponse::HTTP_FORBIDDEN);
        }
        $user = $this->userRepository->getByUUID($uuid);
        return $this->sendSuccessResponse($user);
    }

    public function logout( Request $request ): JsonResponse
    {
        $auth = Auth::guard();
        $auth->logout();
        return $this->sendSuccessResponse([]);
    }
}
