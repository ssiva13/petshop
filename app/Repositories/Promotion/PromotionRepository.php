<?php
/**
 * Date 06/04/2023
 *
 * @author   Simon Siva <simonsiva13@gmail.com>
 */

namespace App\Repositories\Promotion;

use App\Models\Promotion;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class PromotionRepository implements PromotionInterface
{
    public function getAll(): Collection
    {
        return Promotion::all();
    }

    public function getByUUID($uuid)
    {
        return Promotion::find($uuid);
    }

    public function delete($uuid): bool
    {
        if (!$user = Promotion::find($uuid)) {
            return false;
        }
        return $user->delete();
    }

    public function create(array $data)
    {
        return Promotion::create($data);
    }

    public function update($uuid, array $data)
    {
        return Promotion::find($uuid)->update($data);
    }

    public function getPaginated(array $data = [])
    {
        $date = ($data['valid']) ? Carbon::now()->toDateString() : null;
        return Promotion::when($date, function ($query) use ($date) {
            return $query->whereRaw('JSON_EXTRACT(`metadata` , "$.valid_from") <= ?', [$date])
                ->whereRaw('JSON_EXTRACT(`metadata` , "$.valid_to") >= ?', [$date]);
        })
            ->orderBy($data['sortBy'], $data['desc'])
            ->paginate((int)$data['limit'], page: $data['page']);
    }
}
