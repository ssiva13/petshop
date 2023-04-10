<?php
/**
 * Date 06/04/2023
 *
 * @author   Simon Siva <simonsiva13@gmail.com>
 */

namespace App\Http\Controllers\Api;

use App\Http\Requests\File\FileRequest;
use App\Repositories\File\FileRepository;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\{DB, Storage};
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\{Response, StreamedResponse};
use Throwable;

/**
 * @OA\Tag(
 *     name="Files",
 *     description="API endpoints for Files"
 * )
 */
class FileController extends ApiController
{
    protected FileRepository $fileRepository;

    public function __construct(FileRepository $fileRepository)
    {
        $this->fileRepository = $fileRepository;
    }

    /**
     * @OA\Post(
     *      path="/file/upload",
     *      operationId="upload_file",
     *      tags={"Files"},
     *      security={{"bearerAuth":{}}},
     *      summary="Upload a File",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\MediaType(
     *              mediaType="multipart/form-data",
     *              @OA\Schema(
     *                  schema="CreateRequest",
     *                  title="Create Request",
     *                  required={"file"},
     *                  type="object",
     *                  @OA\Property(
     *                      property="file",
     *                      type="string",
     *                      format="binary",
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
    public function store(FileRequest $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            $file = $this->fileRepository->create($request->all());
            DB::commit();
            return $this->sendSuccessResponse($file, Response::HTTP_CREATED);
        } catch (Exception $exception) {
            DB::rollBack();
            return $this->throwError($exception->getMessage(), $exception->getTrace(), $exception->getCode());
        }
    }

    /**
     * @OA\Get(
     *      path="/file/{uuid}",
     *      operationId="download_file",
     *      tags={"Files"},
     *      summary="Download a File",
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
    public function download($uuid): JsonResponse|StreamedResponse
    {
        if (!$file = $this->fileRepository->getByUUID($uuid)) {
            return $this->sendErrorResponse('File not found!', Response::HTTP_NOT_FOUND);
        }
        if (Storage::missing($file->path)) {
            return $this->sendErrorResponse('File not found in storage!', Response::HTTP_NOT_FOUND);
        }
        return Storage::download($file->path, $file->name);
    }

}
