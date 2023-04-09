<?php
/**
 * Date 06/04/2023
 *
 * @author   Simon Siva <simonsiva13@gmail.com>
 */

namespace App\Repositories\Payment;

use App\Models\Payment;
use Illuminate\Database\Eloquent\Collection;

class PaymentRepository implements PaymentInterface
{
    public function getAll(): Collection
    {
        return Payment::all();
    }

    public function getByUUID($uuid)
    {
        return Payment::find($uuid);
    }

    public function delete($uuid): bool
    {
        if (!$user = Payment::find($uuid)) {
            return false;
        }
        return $user->delete();
    }

    public function create(array $data)
    {
        return Payment::create($data);
    }

    public function update($uuid, array $data)
    {
        return Payment::find($uuid)->update($data);
    }

    public function getPaginated(array $data = [])
    {
        return Payment::orderBy($data['sortBy'], $data['desc'])
            ->paginate((int)$data['limit'], page: $data['page']);
    }
}
