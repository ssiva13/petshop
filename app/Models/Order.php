<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property float $delivery_fee
 * @property float $amount
 * @property int   $created_at
 * @property int   $updated_at
 * @property int   $shipped_at
 * @property int   $deleted_at
 * @property OrderStatus $orderStatus
 * @property Payment $payment
 * @method static findOrFail($uuid)
 * @method static find($uuid)
 * @method static create(array $data)
 * @method static where(string $string, $uuid)
 * @method static when(mixed $first_name, \Closure $param)
 * @method static orderBy(mixed $sortBy, mixed $desc)
 */
class Order extends Model
{
    use SoftDeletes, HasUuids, HasFactory;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'orders';

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
        'uuid', 'user_uuid', 'order_status_uuid', 'payment_uuid', 'products', 'address', 'delivery_fee',
        'amount', 'created_at', 'updated_at', 'shipped_at', 'deleted_at'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'id', 'deleted_at',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'delivery_fee' => 'double', 'amount' => 'double',
        'created_at' => 'timestamp', 'updated_at' => 'timestamp',
        'shipped_at' => 'timestamp', 'deleted_at' => 'timestamp',
        'products' => 'array', 'address' => 'array'
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected array $dates = [
        'created_at', 'updated_at', 'shipped_at', 'deleted_at'
    ];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var boolean
     */
    public $timestamps = true;

    // Scopes...

    // Relations ...
    public function orderStatus(): HasOne
    {
        return $this->hasOne(OrderStatus::class, 'uuid', 'order_status_uuid');
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class, 'uuid', 'payment_uuid');
    }

    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'uuid', 'user_uuid');
    }

    // Functions ...

}
