<?php
/**
 * Date 06/04/2023
 *
 * @author   Simon Siva <simonsiva13@gmail.com>
 */

namespace App\Repositories\Category;

use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;

class CategoryRepository implements CategoryInterface
{
    public function getAll(): Collection
    {
        return Category::all();
    }

    public function getByUUID($uuid)
    {
        return Category::find($uuid);
    }

    public function delete($uuid): bool
    {
        if(!$user = Category::find($uuid)){
            return false;
        }
        return $user->delete();
    }

    public function create(array $data)
    {
        return Category::create($data);
    }

    public function update($uuid, array $data)
    {
        return Category::find($uuid)->update($data);
    }

    public function getPaginated(array $data = [])
    {
        return Category::orderBy($data['sortBy'], $data['desc'])
            ->paginate((int)$data['limit'], page: $data['page']);
    }
}
