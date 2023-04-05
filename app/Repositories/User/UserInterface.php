<?php
/**
 * Date 04/04/2023
 *
 * @author   Simon Siva <simonsiva13@gmail.com>
 */

namespace App\Repositories\User;

interface UserInterface
{
    public function getAll();
    public function getPaginated();
    public function getByUUID($uuid);
    public function getById($id);
    public function delete($uuid);
    public function create(array $data, $admin = false);
    public function update($uuid, array $data);
}
