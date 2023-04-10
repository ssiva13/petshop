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
use OpenApi\Annotations as OA;
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
     * @OA\Post(
     *      path="/product/create",
     *      operationId="create_product",
     *      tags={"Products"},
     *      security={{"bearerAuth":{}}},
     *      summary="Create a product",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\MediaType(
     *              mediaType="application/x-www-form-urlencoded",
     *              @OA\Schema(
     *                  schema="CreateRequest",
     *                  title="Create Request",
     *                  required={"category_uuid", "brand_uuid", "title", "price", "description", "metadata", },
     *                  @OA\Property(
     *                      property="category_uuid",
     *                      type="string",
     *                      description="Category UUID"
     *                  ),
     *                  @OA\Property(
     *                      property="brand_uuid",
     *                      type="string",
     *                      description="Brand UUID"
     *                  ),
     *                  @OA\Property(
     *                      property="title",
     *                      type="string",
     *                      description="Product title"
     *                  ),
     *                  @OA\Property(
     *                      property="price",
     *                      type="number",
     *                      description="Product price"
     *                  ),
     *                  @OA\Property(
     *                      property="description",
     *                      type="string",
     *                      description="Product description"
     *                  ),
     *                  @OA\Property(
     *                      property="metadata",
     *                      type="object",
     *                      @OA\Property(property="image", type="string"),
     *                      @OA\Property(property="brand", type="string"),
     *                      description="Product metadata"
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
     *
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

    /**
     * @OA\Get(
     *      path="/products",
     *      operationId="products",
     *      tags={"Products"},
     *      summary="List all products",
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
     *
     * @OA\Put(
     *      path="/product/{uuid}",
     *      operationId="edit_product",
     *      tags={"Products"},
     *      security={{"bearerAuth":{}}},
     *      summary="Edit a product",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\MediaType(
     *              mediaType="application/x-www-form-urlencoded",
     *              @OA\Schema(
     *                  schema="EditRequest",
     *                  title="Edit Request",
     *                  required={"category_uuid", "brand_uuid", "title", "price", "description", "metadata" },
     *                  @OA\Property(
     *                      property="category_uuid",
     *                      type="string",
     *                      description="Category UUID"
     *                  ),
     *                  @OA\Property(
     *                      property="brand_uuid",
     *                      type="string",
     *                      description="Brand UUID"
     *                  ),
     *                  @OA\Property(
     *                      property="title",
     *                      type="string",
     *                      description="Product title"
     *                  ),
     *                  @OA\Property(
     *                      property="price",
     *                      type="number",
     *                      description="Product price"
     *                  ),
     *                  @OA\Property(
     *                      property="description",
     *                      type="string",
     *                      description="Product description"
     *                  ),
     *                  @OA\Property(
     *                      property="metadata",
     *                      type="object",
     *                      @OA\Property(property="image", type="string"),
     *                      @OA\Property(property="brand", type="string"),
     *                      description="Product metadata"
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

    /**
     * @OA\Get(
     *      path="/product/{uuid}",
     *      operationId="get_product",
     *      tags={"Products"},
     *      summary="Fetch a product",
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
        if (!$product = $this->productRepository->getByUUID($uuid)) {
            return $this->sendErrorResponse('Product not found!', Response::HTTP_NOT_FOUND);
        }
        return $this->sendSuccessResponse($product);
    }

    /**
     * @OA\Delete(
     *      path="/product/{uuid}",
     *      operationId="delete_product",
     *      tags={"Products"},
     *      security={{"bearerAuth":{}}},
     *      summary="Delete a product",
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
