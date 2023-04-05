<?php
/**
 * Date 04/04/2023
 *
 * @author   Simon Siva <simonsiva13@gmail.com>
 */

namespace App\Repositories\User;

use App\Models\JwtToken;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Hash;
use Lcobucci\JWT\UnencryptedToken;

class UserRepository implements UserInterface
{
    public function getAll(): Collection
    {
        return User::all();
    }

    public function getById($id)
    {
        return User::findOrFail($id);
    }

    public function getByUUID($uuid)
    {
        return User::findOrFail($uuid);
    }

    public function delete($uuid)
    {
        if(!$user = User::find($uuid)){
            return false;
        }
        return $user->delete();
    }

    public function create(array $data, $admin = false)
    {
        if ($admin){
            $data['is_admin'] = 1;
        }
        $data['password'] = Hash::make($data['password']);
        return User::create($data);
    }

    public function update($uuid, array $data)
    {
        return User::find($uuid)->update($data);
    }

    public function saveAuthToken(UnencryptedToken $authToken)
    {
        $jwtToken = new JwtToken();
        $jwtToken->unique_id = $authToken->claims()->get('jti');
        $jwtToken->user_id = $authToken->claims()->get('uuid');
        $jwtToken->expires_at = $authToken->claims()->get('exp');
        $jwtToken->created_at = $authToken->claims()->get('iat');
        $jwtToken->token_title = $authToken->headers()->toString().$authToken->claims()->get('email');
        // $jwtToken->last_used_at = $token['iat'];
        // $jwtToken->permissions = $token['uuid'];
        // $jwtToken->refreshed_at = $token['uuid'];
        // $jwtToken->restrictions = $token['uuid'];
        $jwtToken->save();
    }

    public function getPaginated(array $data = [])
    {
        return User::admin(false)
            ->when($data['first_name'], function ($query, $value) {
                return $query->where('first_name', 'LIKE', $value);
            })
            ->when($data['email'], function ($query, $value) {
                return $query->where('email', 'LIKE', $value);
            })
            ->when($data['phone'], function ($query, $value) {
                return $query->where('phone_number', 'LIKE', $value);
            })
            ->when($data['address'], function ($query, $value) {
                return $query->where('address', 'LIKE', $value);
            })
            ->when($data['marketing'], function ($query, $value) {
                return $query->marketing((bool) $value);
            })
            ->when($data['created_at'], function ($query, $value) {
                return $query->where('created_at', $value);
            })
            ->orderBy($data['sortBy'], $data['desc'])->paginate((int)$data['limit'], page: $data['page']);
    }
}
