<?php
/**
 * Date 06/04/2023
 *
 * @author   Simon Siva <simonsiva13@gmail.com>
 */

namespace App\Http\Controllers\Api;

use App\Http\Requests\Category\CategoryRequest;
use App\Http\Requests\Category\UpdateCategoryRequest;
use App\Repositories\Category\CategoryRepository;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * @OA\Tag(
 *     name="Categories",
 *     description="API endpoints for Categories"
 * )
 */
class CategoryController extends ApiController
{
    protected CategoryRepository $categoryRepository;

    public function __construct(CategoryRepository $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * @OA\Post(
     *      path="/category/create",
     *      operationId="create_category",
     *      tags={"Categories"},
     *      security={{"bearerAuth":{}}},
     *      summary="Create a category",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\MediaType(
     *              mediaType="application/x-www-form-urlencoded",
     *              @OA\Schema(
     *                  schema="CreateRequest",
     *                  title="Create Request",
     *                  required={"title"},
     *                  @OA\Property(
     *                      property="title",
     *                      type="string",
     *                      description="Category title"
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
    public function store(CategoryRequest $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            $category = $this->categoryRepository->create($request->all());
            DB::commit();
            return $this->sendSuccessResponse($category, Response::HTTP_CREATED);
        } catch (Exception $exception) {
            DB::rollback();
            return $this->throwError($exception->getMessage(), $exception->getTrace(), $exception->getCode());
        }
    }

    /**
     * @OA\Get(
     *      path="/categories",
     *      operationId="categories",
     *      tags={"Categories"},
     *      summary="List all categories",
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
     *)
     */
    public function all(Request $request): JsonResponse
    {
        $data = [
            'page' => $request->get('page', 1),
            'limit' => $request->get('limit', 15),
            'sortBy' => $request->get('sortBy', 'created_at'),
            'desc' => $request->get('desc', true) ? 'desc' : 'asc',
        ];
        $categories = $this->categoryRepository->getPaginated($data);
        return $this->sendSuccessResponse($categories, errors: null, extra: null);
    }

    /**
     * @OA\Put(
     *      path="/category/{uuid}",
     *      operationId="edit_category",
     *      tags={"Categories"},
     *      security={{"bearerAuth":{}}},
     *      summary="Edit a category",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\MediaType(
     *              mediaType="application/x-www-form-urlencoded",
     *              @OA\Schema(
     *                  schema="EditRequest",
     *                  title="Edit Request",
     *                  required={"title"},
     *                  @OA\Property(
     *                      property="title",
     *                      type="string",
     *                      description="Category title"
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
    public function edit($uuid, UpdateCategoryRequest $request): JsonResponse
    {
        if (!$category = $this->categoryRepository->getByUUID($uuid)) {
            return $this->sendErrorResponse('Category not found!', Response::HTTP_NOT_FOUND);
        }
        DB::beginTransaction();
        try {
            $category->update($request->except(['uuid']));
            DB::commit();
            return $this->sendSuccessResponse($category);
        } catch (Exception $exception) {
            DB::rollback();
            return $this->throwError($exception->getMessage(), $exception->getTrace());
        }
    }

    /**
     * @OA\Get(
     *      path="/category/{uuid}",
     *      operationId="get_category",
     *      tags={"Categories"},
     *      summary="Fetch a category",
     *      @OA\Parameter(
     *          name="uuid",
     *          description="uuid",
     *          required=true,
     *          in="query",
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
     */
    public function fetch($uuid): JsonResponse
    {
        if (!$category = $this->categoryRepository->getByUUID($uuid)) {
            return $this->sendErrorResponse('Category not found!', Response::HTTP_NOT_FOUND);
        }
        return $this->sendSuccessResponse($category);
    }

    /**
     * @OA\Delete(
     *      path="/category/{uuid}",
     *      operationId="delete_category",
     *      tags={"Categories"},
     *      security={{"bearerAuth":{}}},
     *      summary="Delete a category",
     *      @OA\Parameter(
     *          name="uuid",
     *          description="uuid",
     *          required=true,
     *          in="query",
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
    public function delete($uuid): JsonResponse
    {
        DB::beginTransaction();
        try {
            if (!$this->categoryRepository->delete($uuid)) {
                return $this->sendErrorResponse('Category not found!', Response::HTTP_NOT_FOUND);
            }
            DB::commit();
            return $this->sendSuccessResponse([]);
        } catch (Exception $exception) {
            DB::rollback();
            return $this->throwError($exception->getMessage(), $exception->getTrace());
        }
    }


}
