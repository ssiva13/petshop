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
        return User::delete($uuid);
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
        return User::where('uuid', $uuid)->update($data);
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
}
