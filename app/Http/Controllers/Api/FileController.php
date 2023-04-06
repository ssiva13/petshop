<?php
/**
 * Date 06/04/2023
 *
 * @author   Simon Siva <simonsiva13@gmail.com>
 */

namespace App\Http\Controllers\Api;

use App\Http\Requests\File\FileRequest;
use App\Repositories\File\FileRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FileController extends ApiController
{
    protected FileRepository $fileRepository;

    public function __construct(FileRepository $fileRepository)
    {
        $this->fileRepository = $fileRepository;
    }

    /**
     * @throws \Throwable
     */
    public function store(FileRequest $request): JsonResponse
    {
        DB::beginTransaction();
        try{
            $file = $this->fileRepository->create( $request->all());
            DB::commit();
            return $this->sendSuccessResponse($file, Response::HTTP_CREATED);
        } catch (\Exception $exception) {
            DB::rollback();
            return $this->throwError($exception->getMessage(), $exception->getTrace(), $exception->getCode());
        }
    }

    public function download($uuid): JsonResponse|StreamedResponse
    {
        if(!$file = $this->fileRepository->getByUUID($uuid)){
            return $this->sendErrorResponse('File not found!', Response::HTTP_NOT_FOUND);
        }
        if(Storage::missing($file->path)){
            return $this->sendErrorResponse('File not found in storage!', Response::HTTP_NOT_FOUND);
        }
        return Storage::download($file->path, $file->name);
    }

}
