<?php
/**
 * Date 06/04/2023
 *
 * @author   Simon Siva <simonsiva13@gmail.com>
 */

namespace App\Repositories\Post;

use App\Models\Post;
use Illuminate\Database\Eloquent\Collection;

class PostRepository implements PostInterface
{
    public function getAll(): Collection
    {
        return Post::all();
    }

    public function getByUUID($uuid)
    {
        return Post::find($uuid);
    }

    public function delete($uuid): bool
    {
        if(!$user = Post::find($uuid)){
            return false;
        }
        return $user->delete();
    }

    public function create(array $data)
    {
        return Post::create($data);
    }

    public function update($uuid, array $data)
    {
        return Post::find($uuid)->update($data);
    }

    public function getPaginated(array $data = [])
    {
        return Post::orderBy($data['sortBy'], $data['desc'])
            ->paginate((int) $data['limit'], page: $data['page']);
    }
}
