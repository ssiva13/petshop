<?php
/**
 * Date 06/04/2023
 *
 * @author   Simon Siva <simonsiva13@gmail.com>
 */

namespace App\Http\Controllers\Api;

use App\Http\Requests\Order\OrderRequest;
use App\Repositories\Order\OrderRepository;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

/**
 * @OA\Tag(
 *     name="Orders",
 *     description="API endpoints for Orders"
 * )
 *
 */
class OrderController extends ApiController
{
    protected OrderRepository $orderRepository;

    public function __construct(OrderRepository $orderRepository)
    {
        $this->orderRepository = $orderRepository;
    }

    /**
     * @OA\Post(
     *      path="/order/create",
     *      operationId="create_order",
     *      tags={"Orders"},
     *      security={{"bearerAuth":{}}},
     *      summary="Create order",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\MediaType(
     *              mediaType="application/x-www-form-urlencoded",
     *              @OA\Schema(
     *                  schema="CreateRequest",
     *                  title="Create Request",
     *                  required={"order_status_uuid", "products", "address" },
     *                  @OA\Property(
     *                      property="order_status_uuid",
     *                      type="string",
     *                      description="Order status UUID"
     *                  ),
     *                  @OA\Property(
     *                      property="payment_uuid",
     *                      type="string",
     *                      description="Payment UUID"
     *                  ),
     *                  @OA\Property(
     *                      property="products",
     *                      type="array",
     *                      @OA\Items(
     *                          type="object",
     *                          @OA\Property(property="uuid", type="string"),
     *                          @OA\Property(property="quantity", type="integer"),
     *                      ),
     *                      description="Array of objects with product uuid and quantity"
     *                  ),
     *                  @OA\Property(
     *                      property="address",
     *                      type="object",
     *                      @OA\Property(property="billing", type="string"),
     *                      @OA\Property(property="shipping", type="string"),
     *                      description="Billing and Shipping address"
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
    public function store(OrderRequest $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            $orderRequest = $this->orderRepository->getOrderRequest($request);
            $orderDetails = $this->orderRepository->processOrder($request->get('products'));
            $data = array_merge($orderDetails, $orderRequest);
            $order = $this->orderRepository->create($data);
            DB::commit();
            return $this->sendSuccessResponse($order, Response::HTTP_CREATED);
        } catch (Exception $exception) {
            DB::rollback();
            return $this->throwError($exception->getMessage(), $exception->getTrace());
        }
    }

    /**
     *
     * @OA\Put(
     *      path="/order/{uuid}",
     *      operationId="edit_order",
     *      tags={"Orders"},
     *      security={{"bearerAuth":{}}},
     *      summary="Edit order",
     *      @OA\Parameter(
     *          name="uuid",
     *          description="uuid",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\MediaType(
     *              mediaType="application/x-www-form-urlencoded",
     *              @OA\Schema(
     *                  schema="EditRequest",
     *                  title="Edit Request",
     *                  required={"order_status_uuid", "products", "address" },
     *                  @OA\Property(
     *                      property="order_status_uuid",
     *                      type="string",
     *                      description="Order status UUID"
     *                  ),
     *                  @OA\Property(
     *                      property="payment_uuid",
     *                      type="string",
     *                      description="Payment UUID"
     *                  ),
     *                  @OA\Property(
     *                      property="products",
     *                      type="array",
     *                      @OA\Items(
     *                          type="object",
     *                          @OA\Property(property="uuid", type="string"),
     *                          @OA\Property(property="quantity", type="integer"),
     *                      ),
     *                      description="Array of objects with product uuid and quantity"
     *                  ),
     *                  @OA\Property(
     *                      property="address",
     *                      type="object",
     *                      @OA\Property(property="billing", type="string"),
     *                      @OA\Property(property="shipping", type="string"),
     *                      description="Billing and Shipping address"
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
    public function edit($uuid, OrderRequest $request): JsonResponse
    {
        if (!$order = $this->orderRepository->getByUUID($uuid)) {
            return $this->sendErrorResponse('Order not found!', Response::HTTP_NOT_FOUND);
        }
        DB::beginTransaction();
        try {
            $orderRequest = $this->orderRepository->getOrderRequest($request);
            $orderDetails = $this->orderRepository->processOrder($request->get('products'));
            $data = array_merge($orderDetails, $orderRequest);

            $order = $this->orderRepository->update(uuid: $order->uuid, data: $data);
            DB::commit();

            return $this->sendSuccessResponse($order);
        } catch (Exception $exception) {
            DB::rollback();
            return $this->throwError($exception->getMessage(), $exception->getTrace());
        }
    }

    /**
     * @OA\Get(
     *      path="/order/{uuid}",
     *      operationId="get_order",
     *      tags={"Orders"},
     *      security={{"bearerAuth":{}}},
     *      summary="Fetch order",
     *      @OA\Parameter(
     *          name="uuid",
     *          description="uuid",
     *          required=true,
     *          in="path",
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
        if (!$order = $this->orderRepository->getByUUID($uuid)) {
            return $this->sendErrorResponse('Order not found!', Response::HTTP_NOT_FOUND);
        }
        return $this->sendSuccessResponse($order);
    }

    /**
     * @OA\Get(
     *      path="/orders",
     *      operationId="orders",
     *      tags={"Orders"},
     *      security={{"bearerAuth":{}}},
     *      summary="List all orders",
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
        $orders = $this->orderRepository->getPaginated($data);
        return $this->sendSuccessResponse($orders, errors: null, extra: null);
    }

    /**
     * @OA\Delete(
     *      path="/order/{uuid}",
     *      operationId="delete_order",
     *      tags={"Orders"},
     *      security={{"bearerAuth":{}}},
     *      summary="Delete order",
     *      @OA\Parameter(
     *          name="uuid",
     *          description="uuid",
     *          required=true,
     *          in="path",
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
            if (!$this->orderRepository->delete($uuid)) {
                return $this->sendErrorResponse('Order not found!', Response::HTTP_NOT_FOUND);
            }
            DB::commit();
            return $this->sendSuccessResponse([]);
        } catch (Exception $exception) {
            DB::rollback();
            return $this->throwError($exception->getMessage(), $exception->getTrace());
        }
    }

    /**
     * @OA\Get(
     *      path="/orders/dashboard",
     *      operationId="orders_dashboard",
     *      tags={"Orders"},
     *      security={{"bearerAuth":{}}},
     *      summary="List orders dashboard",
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
     *          name="dateRange",
     *          description="dateRange",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="object",
     *              @OA\Property(property="from", type="string"),
     *              @OA\Property(property="to", type="string"),
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="fixRange",
     *          description="fixRange",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="string",
     *              enum={"today", "monthly", "yearly"}
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
    public function dashboard(Request $request): JsonResponse
    {
        $data = [
            'dateRange' => $request->get('dateRange'),
            'fixRange' => $request->get('fixRange'),
            'page' => $request->get('page', 1),
            'limit' => $request->get('limit', 15),
            'sortBy' => $request->get('sortBy', 'created_at'),
            'desc' => $request->get('desc', true) ? 'desc' : 'asc',
        ];
        $orders = $this->orderRepository->getOrderSummaries($data);
        return $this->sendSuccessResponse($orders, errors: null, extra: null);
    }

    /**
     * @OA\Get(
     *      path="/orders/shipment-locator",
     *      operationId="shipment",
     *      tags={"Orders"},
     *      security={{"bearerAuth":{}}},
     *      summary="List all shipped orders",
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
     *          name="orderUuid",
     *          description="orderUuid",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="customerUuid",
     *          description="customerUuid",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="dateRange",
     *          description="dateRange",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="object",
     *              @OA\Property(property="from", type="string"),
     *              @OA\Property(property="to", type="string"),
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="fixRange",
     *          description="fixRange",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="string",
     *              enum={"today", "monthly", "yearly"}
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
    public function shipment(Request $request): JsonResponse
    {
        $data = [
            'orderUuid' => $request->get('orderUuid'),
            'customerUuid' => $request->get('customerUuid'),
            'dateRange' => $request->get('dateRange'),
            'fixRange' => $request->get('fixRange'),
            'page' => $request->get('page', 1),
            'limit' => $request->get('limit', 15),
            'sortBy' => $request->get('sortBy', 'created_at'),
            'desc' => $request->get('desc', true) ? 'desc' : 'asc',
        ];
        $orders = $this->orderRepository->getOrderSummaries($data, true);
        return $this->sendSuccessResponse($orders, errors: null, extra: null);
    }

    /**
     * @OA\Get(
     *      path="/order/{uuid}/download",
     *      operationId="download_order",
     *      tags={"Orders"},
     *      security={{"bearerAuth":{}}},
     *      summary="Download order",
     *      @OA\Parameter(
     *          name="uuid",
     *          description="uuid",
     *          required=true,
     *          in="path",
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
    public function download($uuid): StreamedResponse|JsonResponse
    {
        if (!$order = $this->orderRepository->getByUUID($uuid)) {
            return $this->sendErrorResponse('File not found!', Response::HTTP_NOT_FOUND);
        }
        $orderInvoice = $this->orderRepository->generateInvoice($order);

        if (Storage::missing($orderInvoice)) {
            return $this->sendErrorResponse('File not found in storage!', Response::HTTP_NOT_FOUND);
        }
        return Storage::download($orderInvoice, basename($orderInvoice));
    }


    /**
     * @throws Throwable
     */
    public function updateOrderPayment($order_uuid, Request $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            if (!$order = $this->orderRepository->getByUUID($order_uuid)) {
                return $this->sendErrorResponse('Order Not found!', Response::HTTP_NOT_FOUND);
            }
            $response_status = [
                "response_status" => $request->all()
            ];
            $order = $this->orderRepository->updateOrderPayment($order, $response_status);
            if(!$order){
                return $this->sendErrorResponse('Payment Response Not Updated!', Response::HTTP_BAD_GATEWAY);
            }
            DB::commit();
            return $this->sendSuccessResponse($order);
        } catch (Exception $exception) {
            DB::rollBack();
            return $this->throwError($exception->getMessage(), $exception->getTrace());
        }
    }
}
