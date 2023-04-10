<?php
/**
 * Date 06/04/2023
 *
 * @author   Simon Siva <simonsiva13@gmail.com>
 */

namespace App\Http\Controllers\Api;

use App\Http\Requests\Promotion\PromotionRequest;
use App\Repositories\Promotion\PromotionRepository;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class PromotionController extends ApiController
{
    protected PromotionRepository $promotionRepository;

    public function __construct(PromotionRepository $promotionRepository)
    {
        $this->promotionRepository = $promotionRepository;
    }

    /**
     * @throws Throwable
     */
    public function store(PromotionRequest $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            $post = $this->promotionRepository->create($request->all());
            DB::commit();
            return $this->sendSuccessResponse($post, Response::HTTP_CREATED);
        } catch (Exception $exception) {
            DB::rollback();
            return $this->throwError($exception->getMessage(), $exception->getTrace(), $exception->getCode());
        }
    }

    /**
     * @OA\Get(
     *      path="/main/promotions",
     *      operationId="promotions",
     *      tags={"Main Page"},
     *      summary="List all promotions",
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
     *          name="valid",
     *          description="valid",
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
     *)
     */
    public function all(Request $request): JsonResponse
    {
        $data = [
            'valid' => $request->get('valid', true),
            'page' => $request->get('page', 1),
            'limit' => $request->get('limit', 15),
            'sortBy' => $request->get('sortBy', 'created_at'),
            'desc' => $request->get('desc', true) ? 'desc' : 'asc',
        ];
        $posts = $this->promotionRepository->getPaginated($data);
        return $this->sendSuccessResponse($posts, errors: null, extra: null);
    }

    /**
     * @throws Throwable
     */
    public function edit($uuid, PromotionRequest $request): JsonResponse
    {
        if (!$post = $this->promotionRepository->getByUUID($uuid)) {
            return $this->sendErrorResponse('Promotion not found!', Response::HTTP_NOT_FOUND);
        }
        DB::beginTransaction();
        try {
            $post->update($request->except(['uuid']));
            DB::commit();
            return $this->sendSuccessResponse($post);
        } catch (Exception $exception) {
            DB::rollback();
            return $this->throwError($exception->getMessage(), $exception->getTrace());
        }
    }

    public function fetch($uuid): JsonResponse
    {
        if (!$post = $this->promotionRepository->getByUUID($uuid)) {
            return $this->sendErrorResponse('Promotion not found!', Response::HTTP_NOT_FOUND);
        }
        return $this->sendSuccessResponse($post);
    }

    /**
     * @throws Throwable
     */
    public function delete($uuid): JsonResponse
    {
        DB::beginTransaction();
        try {
            if (!$this->promotionRepository->delete($uuid)) {
                return $this->sendErrorResponse('Promotion not found!', Response::HTTP_NOT_FOUND);
            }
            DB::commit();
            return $this->sendSuccessResponse([]);
        } catch (Exception $exception) {
            DB::rollback();
            return $this->throwError($exception->getMessage(), $exception->getTrace());
        }
    }


}
