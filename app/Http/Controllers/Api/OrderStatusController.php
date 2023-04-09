<?php
/**
 * Date 06/04/2023
 *
 * @author   Simon Siva <simonsiva13@gmail.com>
 */

namespace App\Http\Controllers\Api;

use App\Http\Requests\OrderStatus\OrderStatusRequest;
use App\Http\Requests\OrderStatus\UpdateOrderStatusRequest;
use App\Repositories\OrderStatus\OrderStatusRepository;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class OrderStatusController extends ApiController
{
    protected OrderStatusRepository $orderStatusRepository;

    public function __construct(OrderStatusRepository $orderStatusRepository)
    {
        $this->orderStatusRepository = $orderStatusRepository;
    }

    /**
     * @throws Throwable
     */
    public function store(OrderStatusRequest $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            $orderStatus = $this->orderStatusRepository->create($request->all());
            DB::commit();
            return $this->sendSuccessResponse($orderStatus, Response::HTTP_CREATED);
        } catch (Exception $exception) {
            DB::rollback();
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
        $orderStatuses = $this->orderStatusRepository->getPaginated($data);
        return $this->sendSuccessResponse($orderStatuses, errors: null, extra: null);
    }

    /**
     * @throws Throwable
     */
    public function edit($uuid, UpdateOrderStatusRequest $request): JsonResponse
    {
        if (!$orderStatus = $this->orderStatusRepository->getByUUID($uuid)) {
            return $this->sendErrorResponse('OrderStatus not found!', Response::HTTP_NOT_FOUND);
        }
        DB::beginTransaction();
        try {
            $orderStatus->update($request->except(['uuid']));
            DB::commit();
            return $this->sendSuccessResponse($orderStatus);
        } catch (Exception $exception) {
            DB::rollback();
            return $this->throwError($exception->getMessage(), $exception->getTrace());
        }
    }

    public function fetch($uuid): JsonResponse
    {
        if (!$orderStatus = $this->orderStatusRepository->getByUUID($uuid)) {
            return $this->sendErrorResponse('OrderStatus not found!', Response::HTTP_NOT_FOUND);
        }
        return $this->sendSuccessResponse($orderStatus);
    }

    /**
     * @throws Throwable
     */
    public function delete($uuid): JsonResponse
    {
        DB::beginTransaction();
        try {
            if (!$this->orderStatusRepository->delete($uuid)) {
                return $this->sendErrorResponse('OrderStatus not found!', Response::HTTP_NOT_FOUND);
            }
            DB::commit();
            return $this->sendSuccessResponse([]);
        } catch (Exception $exception) {
            DB::rollback();
            return $this->throwError($exception->getMessage(), $exception->getTrace());
        }
    }


}
