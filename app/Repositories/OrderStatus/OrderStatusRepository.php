<?php
/**
 * Date 06/04/2023
 *
 * @author   Simon Siva <simonsiva13@gmail.com>
 */

namespace App\Repositories\OrderStatus;

use App\Models\OrderStatus;
use Illuminate\Database\Eloquent\Collection;

class OrderStatusRepository implements OrderStatusInterface
{
    public function getAll(): Collection
    {
        return OrderStatus::all();
    }

    public function getByUUID($uuid)
    {
        return OrderStatus::find($uuid);
    }

    public function delete($uuid): bool
    {
        if (!$user = OrderStatus::find($uuid)) {
            return false;
        }
        return $user->delete();
    }

    public function create(array $data)
    {
        return OrderStatus::create($data);
    }

    public function update($uuid, array $data)
    {
        return OrderStatus::find($uuid)->update($data);
    }

    public function getPaginated(array $data = [])
    {
        return OrderStatus::orderBy($data['sortBy'], $data['desc'])
            ->paginate((int)$data['limit'], page: $data['page']);
    }
}
