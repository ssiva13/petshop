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
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class OrderController extends ApiController
{
    protected OrderRepository $orderRepository;

    public function __construct(OrderRepository $orderRepository)
    {
        $this->orderRepository = $orderRepository;
    }

    /**
     * @throws Throwable
     */
    public function store(OrderRequest $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            $orderRequest = $this->orderRepository->getOrderRequest($request);
            $orderDetails = $this->orderRepository->processOrder($request->only('products'));
            $data = array_merge($orderDetails, $orderRequest);
            $order = $this->orderRepository->create($data);
            DB::commit();
            return $this->sendSuccessResponse($order, Response::HTTP_CREATED);
        } catch (Exception $exception) {
            DB::rollback();
            return $this->throwError($exception->getMessage(), $exception->getTrace(), $exception->getCode());
        }
    }

    /**
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
            $orderDetails = $this->orderRepository->processOrder($request->only('products'));
            $data = array_merge($orderDetails, $orderRequest);

            $order = $this->orderRepository->update(uuid: $order->uuid, data: $data);
            DB::commit();

            return $this->sendSuccessResponse($order);
        } catch (Exception $exception) {
            DB::rollback();
            return $this->throwError($exception->getMessage(), $exception->getTrace());
        }
    }

    public function fetch($uuid): JsonResponse
    {
        if (!$order = $this->orderRepository->getByUUID($uuid)) {
            return $this->sendErrorResponse('Order not found!', Response::HTTP_NOT_FOUND);
        }
        return $this->sendSuccessResponse($order);
    }

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

}
