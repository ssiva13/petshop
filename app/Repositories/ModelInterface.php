<?php
/**
 * Date 06/04/2023
 *
 * @author   Simon Siva <simonsiva13@gmail.com>
 */

namespace App\Repositories;

interface ModelInterface
{
    public function getAll();
    public function getByUUID($uuid);
    public function delete($uuid);
    public function create(array $data);
    public function update($uuid, array $data);
}
