<?php
/**
 * Date 06/04/2023
 *
 * @author   Simon Siva <simonsiva13@gmail.com>
 */

namespace App\Repositories\File;

interface FileInterface
{
    public function getByUUID($uuid);
    public function create(array $data);
}
