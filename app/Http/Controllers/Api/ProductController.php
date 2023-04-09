<?php
/**
 * Date 06/04/2023
 *
 * @author   Simon Siva <simonsiva13@gmail.com>
 */

namespace App\Http\Controllers\Api;

use App\Http\Requests\Product\ProductRequest;
use App\Repositories\Product\ProductRepository;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class ProductController extends ApiController
{
    protected ProductRepository $productRepository;

    public function __construct(ProductRepository $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    /**
     * @throws Throwable
     */
    public function store(ProductRequest $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            $product = $this->productRepository->create($request->all());
            DB::commit();
            return $this->sendSuccessResponse($product, Response::HTTP_CREATED);
        } catch (Exception $exception) {
            DB::rollback();
            return $this->throwError($exception->getMessage(), $exception->getTrace(), $exception->getCode());
        }
    }

    public function all(Request $request): JsonResponse
    {
        $data = [
            'category' => $request->get('category', ''),
            'price' => $request->get('price'),
            'brand' => $request->get('brand', ''),
            'title' => $request->get('title', ''),
            'page' => $request->get('page', 1),
            'limit' => $request->get('limit', 15),
            'sortBy' => $request->get('sortBy', 'created_at'),
            'desc' => $request->get('desc', true) ? 'desc' : 'asc',
        ];
        $products = $this->productRepository->getPaginated($data);
        return $this->sendSuccessResponse($products, errors: null, extra: null);
    }

    /**
     * @throws Throwable
     */
    public function edit($uuid, ProductRequest $request): JsonResponse
    {
        if (!$product = $this->productRepository->getByUUID($uuid)) {
            return $this->sendErrorResponse('Product not found!', Response::HTTP_NOT_FOUND);
        }
        DB::beginTransaction();
        try {
            $product->update($request->except(['uuid']));
            DB::commit();
            return $this->sendSuccessResponse($product);
        } catch (Exception $exception) {
            DB::rollback();
            return $this->throwError($exception->getMessage(), $exception->getTrace());
        }
    }

    public function fetch($uuid): JsonResponse
    {
        if (!$product = $this->productRepository->getByUUID($uuid)) {
            return $this->sendErrorResponse('Product not found!', Response::HTTP_NOT_FOUND);
        }
        return $this->sendSuccessResponse($product);
    }

    /**
     * @throws Throwable
     */
    public function delete($uuid): JsonResponse
    {
        DB::beginTransaction();
        try {
            if (!$this->productRepository->delete($uuid)) {
                return $this->sendErrorResponse('Product not found!', Response::HTTP_NOT_FOUND);
            }
            DB::commit();
            return $this->sendSuccessResponse([]);
        } catch (Exception $exception) {
            DB::rollback();
            return $this->throwError($exception->getMessage(), $exception->getTrace());
        }
    }


}
