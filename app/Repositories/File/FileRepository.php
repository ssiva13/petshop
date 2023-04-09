<?php
/**
 * Date 06/04/2023
 *
 * @author   Simon Siva <simonsiva13@gmail.com>
 */

namespace App\Repositories\File;

use App\Models\File;
use Illuminate\Support\Facades\Storage;

class FileRepository implements FileInterface
{
    public function getByUUID($uuid)
    {
        return File::find($uuid);
    }

    public function create(array $data)
    {
        if($filename = Storage::disk()->putFile($data['path'], $data['file'])){
            $data['path'] = $filename;
            $data['size'] = $this->bytesToHuman($data['size']);
            return File::create($data);
        }
        return false;
    }

    public function bytesToHuman($bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }

}
