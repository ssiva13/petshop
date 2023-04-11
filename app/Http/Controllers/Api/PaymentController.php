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
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * @OA\Tag(
 *     name="Payments",
 *     description="API endpoints for Payments"
 * )
 */
class PaymentController extends ApiController
{
    protected PaymentRepository $paymentRepository;

    public function __construct(PaymentRepository $paymentRepository)
    {
        $this->paymentRepository = $paymentRepository;
    }

    /**
     * @OA\Post(
     *      path="/payment/create",
     *      operationId="create_payment",
     *      tags={"Payments"},
     *      summary="Create a payment",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\MediaType(
     *              mediaType="application/x-www-form-urlencoded",
     *              @OA\Schema(
     *                  schema="CreateRequest",
     *                  title="Create Request",
     *                  required={"details", "type"},
     *                  @OA\Property(
     *                      property="type",
     *                      type="string",
     *                      description="Payment type"
     *                  ),
     *                  @OA\Property(
     *                      property="details",
     *                      type="object",
     *                      description="Review documentation for the payment type JSON format"
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
    public function store(PaymentRequest $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            $payment = $this->paymentRepository->create($request->all());
            DB::commit();
            return $this->sendSuccessResponse($payment, Response::HTTP_CREATED);
        } catch (Exception $exception) {
            DB::rollback();
            return $this->throwError($exception->getMessage(), $exception->getTrace(), $exception->getCode());
        }
    }

    /**
     * @OA\Get(
     *      path="/payments",
     *      operationId="payments",
     *      tags={"Payments"},
     *      summary="List all payments",
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
        $payments = $this->paymentRepository->getPaginated($data);
        return $this->sendSuccessResponse($payments, errors: null, extra: null);
    }

    /**
     * @OA\Put(
     *      path="/payment/{uuid}",
     *      operationId="edit_payment",
     *      tags={"Payments"},
     *      summary="Edit a payment",
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
     *                  required={"details", "type"},
     *                  @OA\Property(
     *                      property="type",
     *                      type="string",
     *                      description="Payment type"
     *                  ),
     *                  @OA\Property(
     *                      property="details",
     *                      type="object",
     *                      description="Review documentation for the payment type JSON format"
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
    public function edit($uuid, PaymentRequest $request): JsonResponse
    {
        if (!$payment = $this->paymentRepository->getByUUID($uuid)) {
            return $this->sendErrorResponse('Payment not found!', Response::HTTP_NOT_FOUND);
        }
        DB::beginTransaction();
        try {
            $payment->update($request->except(['uuid']));
            DB::commit();
            return $this->sendSuccessResponse($payment);
        } catch (Exception $exception) {
            DB::rollback();
            return $this->throwError($exception->getMessage(), $exception->getTrace());
        }
    }

    /**
     * @OA\Get(
     *      path="/payment/{uuid}",
     *      operationId="get_payment",
     *      tags={"Payments"},
     *      summary="Fetch a payment",
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
        if (!$payment = $this->paymentRepository->getByUUID($uuid)) {
            return $this->sendErrorResponse('Payment not found!', Response::HTTP_NOT_FOUND);
        }
        return $this->sendSuccessResponse($payment);
    }

    /**
     * @OA\Delete(
     *      path="/payment/{uuid}",
     *      operationId="delete_payment",
     *      tags={"Payments"},
     *      summary="Delete a payment",
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
