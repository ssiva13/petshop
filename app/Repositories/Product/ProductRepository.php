<?php
/**
 * Date 06/04/2023
 *
 * @author   Simon Siva <simonsiva13@gmail.com>
 */

namespace App\Repositories\Product;

use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;

class ProductRepository implements ProductInterface
{
    public function getAll(): Collection
    {
        return Product::all();
    }

    public function getByUUID($uuid)
    {
        return Product::find($uuid);
    }

    public function delete($uuid): bool
    {
        if (!$user = Product::find($uuid)) {
            return false;
        }
        return $user->delete();
    }

    public function create(array $data)
    {
        return Product::create($data);
    }

    public function update($uuid, array $data)
    {
        return Product::find($uuid)->update($data);
    }

    public function getPaginated(array $data = [])
    {
        return Product::when($data['title'], function ($query, $value) {
            return $query->where('title', 'LIKE', "%$value%");
        })->when($data['price'], function ($query, $value) {
            return $query->where('price', '<=', $value);
        })->when($data['brand'], function ($query, $value) {
            return $query->where('brand_uuid', $value);
        })->when($data['category'], function ($query, $value) {
            return $query->where('category_uuid', $value);
        })->with([
            'brand',
            'category',
        ])->orderBy($data['sortBy'], $data['desc'])
            ->paginate((int)$data['limit'], page: $data['page']);
    }
}
