<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * @property int $deleted_at
 * @property int $last_login_at
 * @property int $updated_at
 * @property int $created_at
 * @property int $email_verified_at
 * @property string $remember_token
 * @property string $phone_number
 * @property string $address
 * @property string $password
 * @property string $email
 * @property string $last_name
 * @property string $first_name
 * @property bool $is_marketing
 * @property bool $is_admin
 * @method static create(array $array)
 * @method static findOrFail($id)
 * @method static find($uuid)
 * @method static admin(false $false)
 */
class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use HasUuids;
    use Notifiable;
    use SoftDeletes;

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true;
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'users';
    // protected $primaryKey = 'id';
    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'uuid';
    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
        'avatar',
        'deleted_at',
        'last_login_at',
        'updated_at',
        'created_at',
        'remember_token',
        'is_marketing',
        'phone_number',
        'address',
        'password',
        'email_verified_at',
        'email',
        'is_admin',
        'last_name',
        'first_name',
        'uuid',
    ];
    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'is_admin',
        'id',
        'deleted_at'
    ];
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'deleted_at' => 'timestamp',
        'last_login_at' => 'timestamp',
        'updated_at' => 'timestamp',
        'created_at' => 'timestamp',
        'remember_token' => 'string',
        'is_marketing' => 'boolean',
        'phone_number' => 'string',
        'address' => 'string',
        'password' => 'string',
        'email_verified_at' => 'timestamp',
        'email' => 'string',
        'is_admin' => 'boolean',
        'last_name' => 'string',
        'first_name' => 'string',
    ];
    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected array $dates = [
        'deleted_at',
        'last_login_at',
        'updated_at',
        'created_at',
        'email_verified_at',
    ];

    /* ----------------------------
     * Model Relationships
     */

    public function jwtTokens(): HasMany
    {
        return $this->hasMany(JwtToken::class);
    }

    public function jwtToken(): HasOne
    {
        return $this->hasOne(JwtToken::class)->latest();
    }

    /*
     * Model Relationships
     * ----------------------------
     */
    public function orders(): HasMany
    {
        return $this->hasMany(User::class, 'user_uuid', 'uuid');
    }

    public function file(): HasOne
    {
        return $this->hasOne(File::class, 'avatar', 'uuid');
    }


    /* ----------------------------
     * Model Scopes
     */
    public function scopeAdmin($query, $admin = true)
    {
        return $query->where('is_admin', $admin);
    }

    public function scopeMarketing($query, $admin = true)
    {
        return $query->where('is_marketing', $admin);
    }
    /*
     * Model Scopes
     * ----------------------------
     */
}
