<?php
/**
 * Date 06/04/2023
 *
 * @author   Simon Siva <simonsiva13@gmail.com>
 */

namespace App\Http\Controllers\Api;

use App\Http\Requests\Post\PostRequest;
use App\Repositories\Post\PostRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class PostController extends ApiController
{
    protected PostRepository $postRepository;

    public function __construct(PostRepository $postRepository)
    {
        $this->postRepository = $postRepository;
    }

    /**
     * @throws \Throwable
     */
    public function store(PostRequest $request): JsonResponse
    {
        DB::beginTransaction();
        try{
            $post = $this->postRepository->create( $request->all());
            DB::commit();
            return $this->sendSuccessResponse($post, Response::HTTP_CREATED);
        } catch (\Exception $exception) {
            DB::rollback();
            return $this->throwError($exception->getMessage(), $exception->getTrace(), $exception->getCode());
        }
    }

    /**
     * @throws \Throwable
     */
    public function edit($uuid, PostRequest $request): JsonResponse
    {
        if(!$post = $this->postRepository->getByUUID($uuid)){
            return $this->sendErrorResponse('Post not found!', Response::HTTP_NOT_FOUND);
        }
        DB::beginTransaction();
        try{
            $post->update( $request->except(['uuid']) );
            DB::commit();
            return $this->sendSuccessResponse($post);
        } catch (\Exception $exception) {
            DB::rollback();
            return $this->throwError($exception->getMessage(), $exception->getTrace());
        }
    }

    public function fetch($uuid): JsonResponse
    {
        if(!$post = $this->postRepository->getByUUID($uuid)){
            return $this->sendErrorResponse('Post not found!', Response::HTTP_NOT_FOUND);
        }
        return $this->sendSuccessResponse($post);
    }

    public function all(Request $request): JsonResponse
    {
        $data = [
            "page" =>  $request->get('page', 1),
            "limit" =>  $request->get('limit', 15),
            "sortBy" =>  $request->get('sortBy', 'created_at'),
            "desc" =>  $request->get('desc', true) ? 'desc' : 'asc',
        ];
        $posts = $this->postRepository->getPaginated($data);
        return $this->sendSuccessResponse($posts, errors: null, extra: null);
    }

    /**
     * @throws \Throwable
     */
    public function delete($uuid): JsonResponse
    {
        DB::beginTransaction();
        try{
            if(!$this->postRepository->delete($uuid)){
                return $this->sendErrorResponse('Post not found!', Response::HTTP_NOT_FOUND);
            }
            DB::commit();
            return $this->sendSuccessResponse([]);
        } catch (\Exception $exception) {
            DB::rollback();
            return $this->throwError($exception->getMessage(), $exception->getTrace());
        }
    }


}
