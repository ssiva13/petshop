<?php
/**
 * Date 04/04/2023
 *
 * @author   Simon Siva <simonsiva13@gmail.com>
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $created_at
 * @property int $expires_at
 * @property int $last_used_at
 * @property int $refreshed_at
 * @property int $updated_at
 * @property string $token_title
 * @property string $unique_id
 * @property $user_id
 */
class JwtToken extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'jwt_tokens';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
        'created_at', 'expires_at', 'last_used_at', 'permissions', 'refreshed_at', 'restrictions', 'token_title',
        'unique_id', 'updated_at', 'user_id',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'created_at' => 'timestamp', 'expires_at' => 'timestamp', 'last_used_at' => 'timestamp',
        'refreshed_at' => 'timestamp', 'token_title' => 'string',
        'unique_id' => 'string', 'updated_at' => 'timestamp',
        'permissions' => 'array', 'restrictions' => 'array',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected array $dates = [
        'created_at', 'expires_at', 'last_used_at', 'refreshed_at', 'updated_at',
    ];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var boolean
     */
    public $timestamps = true;

    // Scopes...
    // Functions ...
    // Relations ...
}
