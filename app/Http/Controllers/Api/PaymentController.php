<?php
/**
 * Date 06/04/2023
 *
 * @author   Simon Siva <simonsiva13@gmail.com>
 */

namespace App\Http\Controllers\Api;

use App\Http\Requests\Payment\PaymentRequest;
use App\Repositories\Payment\PaymentRepository;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class PaymentController extends ApiController
{
    protected PaymentRepository $paymentRepository;

    public function __construct(PaymentRepository $paymentRepository)
    {
        $this->paymentRepository = $paymentRepository;
    }

    /**
     * @throws Throwable
     */
    public function store(PaymentRequest $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            $brand = $this->paymentRepository->create($request->all());
            DB::commit();
            return $this->sendSuccessResponse($brand, Response::HTTP_CREATED);
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
        $brands = $this->paymentRepository->getPaginated($data);
        return $this->sendSuccessResponse($brands, errors: null, extra: null);
    }

    /**
     * @throws Throwable
     */
    public function edit($uuid, PaymentRequest $request): JsonResponse
    {
        if (!$brand = $this->paymentRepository->getByUUID($uuid)) {
            return $this->sendErrorResponse('Payment not found!', Response::HTTP_NOT_FOUND);
        }
        DB::beginTransaction();
        try {
            $brand->update($request->except(['uuid']));
            DB::commit();
            return $this->sendSuccessResponse($brand);
        } catch (Exception $exception) {
            DB::rollback();
            return $this->throwError($exception->getMessage(), $exception->getTrace());
        }
    }

    public function fetch($uuid): JsonResponse
    {
        if (!$brand = $this->paymentRepository->getByUUID($uuid)) {
            return $this->sendErrorResponse('Payment not found!', Response::HTTP_NOT_FOUND);
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
            if (!$this->paymentRepository->delete($uuid)) {
                return $this->sendErrorResponse('Payment not found!', Response::HTTP_NOT_FOUND);
            }
            DB::commit();
            return $this->sendSuccessResponse([]);
        } catch (Exception $exception) {
            DB::rollback();
            return $this->throwError($exception->getMessage(), $exception->getTrace());
        }
    }


}
