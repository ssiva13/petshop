<?php
/**
 * Date 06/04/2023
 *
 * @author   Simon Siva <simonsiva13@gmail.com>
 */

namespace App\Repositories\Brand;

use App\Models\Brand;
use Illuminate\Database\Eloquent\Collection;

class BrandRepository implements BrandInterface
{
    public function getAll(): Collection
    {
        return Brand::all();
    }

    public function getByUUID($uuid)
    {
        return Brand::find($uuid);
    }

    public function delete($uuid): bool
    {
        if (!$user = Brand::find($uuid)) {
            return false;
        }
        return $user->delete();
    }

    public function create(array $data)
    {
        return Brand::create($data);
    }

    public function update($uuid, array $data)
    {
        return Brand::find($uuid)->update($data);
    }

    public function getPaginated(array $data = [])
    {
        return Brand::orderBy($data['sortBy'], $data['desc'])
            ->paginate((int)$data['limit'], page: $data['page']);
    }
}
