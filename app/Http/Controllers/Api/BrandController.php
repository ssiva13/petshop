<?php
/**
 * Date 06/04/2023
 *
 * @author   Simon Siva <simonsiva13@gmail.com>
 */

namespace App\Http\Controllers\Api;

use App\Http\Requests\Brand\BrandRequest;
use App\Http\Requests\Brand\UpdateBrandRequest;
use App\Repositories\Brand\BrandRepository;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class BrandController extends ApiController
{
    protected BrandRepository $brandRepository;

    public function __construct(BrandRepository $brandRepository)
    {
        $this->brandRepository = $brandRepository;
    }

    /**
     * @throws Throwable
     */
    public function store(BrandRequest $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            $brand = $this->brandRepository->create($request->all());
            DB::commit();
            return $this->sendSuccessResponse($brand, Response::HTTP_CREATED);
        } catch (Exception $exception) {
            DB::rollBack();
            return $this->throwError($exception->getMessage(), $exception->getTrace(), $exception->getCode());
        }
    }

    public function all(Request $request): JsonResponse
    {
        $data = [
            'page' => $request->get('page', 1),
            'limit' => $request->get('limit', 15),
            'sortBy' => $request->get('sortBy', 'created_at'),
            'desc' => $request->get('desc', true) ? 'desc' : 'asc',
        ];
        $brands = $this->brandRepository->getPaginated($data);
        return $this->sendSuccessResponse($brands, errors: null, extra: null);
    }

    /**
     * @throws Throwable
     */
    public function edit($uuid, UpdateBrandRequest $request): JsonResponse
    {
        if (!$brand = $this->brandRepository->getByUUID($uuid)) {
            return $this->sendErrorResponse('Brand not found!', Response::HTTP_NOT_FOUND);
        }
        DB::beginTransaction();
        try {
            $brand->update($request->except(['uuid']));
            DB::commit();
            return $this->sendSuccessResponse($brand);
        } catch (Exception $exception) {
            DB::rollBack();
            return $this->throwError($exception->getMessage(), $exception->getTrace());
        }
    }

    public function fetch($uuid): JsonResponse
    {
        if (!$brand = $this->brandRepository->getByUUID($uuid)) {
            return $this->sendErrorResponse('Brand not found!', Response::HTTP_NOT_FOUND);
        }
        return $this->sendSuccessResponse($brand);
    }

    /**
     * @throws Throwable
     */
    public function delete($uuid): JsonResponse
    {
        DB::beginTransaction();
        try {
            if (!$this->brandRepository->delete($uuid)) {
                return $this->sendErrorResponse('Brand not found!', Response::HTTP_NOT_FOUND);
            }
            DB::commit();
            return $this->sendSuccessResponse([]);
        } catch (Exception $exception) {
            DB::rollBack();
            return $this->throwError($exception->getMessage(), $exception->getTrace());
        }
    }


}
