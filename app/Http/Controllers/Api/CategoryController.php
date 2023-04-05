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
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class CategoryController extends ApiController
{
    protected CategoryRepository $categoryRepository;

    public function __construct(CategoryRepository $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * @throws \Throwable
     */
    public function store(CategoryRequest $request): JsonResponse
    {
        DB::beginTransaction();
        try{
            $category = $this->categoryRepository->create( $request->all());
            DB::commit();
            return $this->sendSuccessResponse($category, Response::HTTP_CREATED);
        } catch (\Exception $exception) {
            DB::rollback();
            return $this->throwError($exception->getMessage(), $exception->getTrace(), $exception->getCode());
        }
    }

    /**
     * @throws \Throwable
     */
    public function edit($uuid, UpdateCategoryRequest $request): JsonResponse
    {
        if(!$category = $this->categoryRepository->getByUUID($uuid)){
            return $this->sendErrorResponse('Category not found!', Response::HTTP_NOT_FOUND);
        }
        DB::beginTransaction();
        try{
            $category->update( $request->except(['uuid']) );
            DB::commit();
            return $this->sendSuccessResponse($category);
        } catch (\Exception $exception) {
            DB::rollback();
            return $this->throwError($exception->getMessage(), $exception->getTrace());
        }
    }

    public function fetch($uuid): JsonResponse
    {
        if(!$category = $this->categoryRepository->getByUUID($uuid)){
            return $this->sendErrorResponse('Category not found!', Response::HTTP_NOT_FOUND);
        }
        return $this->sendSuccessResponse($category);
    }

    public function all(Request $request): JsonResponse
    {
        $data = [
            "page" =>  $request->get('page', 1),
            "limit" =>  $request->get('limit', 15),
            "sortBy" =>  $request->get('sortBy', 'created_at'),
            "desc" =>  $request->get('desc', true) ? 'desc' : 'asc',
        ];
        $categories = $this->categoryRepository->getPaginated($data);
        return $this->sendSuccessResponse($categories, errors: null, extra: null);
    }

    /**
     * @throws \Throwable
     */
    public function delete($uuid): JsonResponse
    {
        DB::beginTransaction();
        try{
            if(!$this->categoryRepository->delete($uuid)){
                return $this->sendErrorResponse('Category not found!', Response::HTTP_NOT_FOUND);
            }
            DB::commit();
            return $this->sendSuccessResponse([]);
        } catch (\Exception $exception) {
            DB::rollback();
            return $this->throwError($exception->getMessage(), $exception->getTrace());
        }
    }


}
