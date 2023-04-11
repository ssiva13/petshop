<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property string $type
 * @property string $response_status
 * @property int $created_at
 * @property int $updated_at
 * @property int $deleted_at
 * @method static find($uuid)
 * @method static create(array $data)
 * @method static orderBy(mixed $sortBy, mixed $desc)
 */
class Payment extends Model
{
    use HasFactory;
    use HasUuids;
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
    protected $table = 'payments';
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
        'uuid',
        'type',
        'details',
        'response_status',
        'created_at',
        'updated_at',
        'deleted_at'
    ];
    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'id',
        'deleted_at'
    ];
    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'type' => 'string',
        'created_at' => 'timestamp',
        'updated_at' => 'timestamp',
        'deleted_at' => 'timestamp',
        'response_status' => 'array',
        'details' => 'array',
    ];
    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected array $dates = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    // Scopes...

    // Relations ...

    public function order(): HasOne
    {
        return $this->hasOne(Order::class, 'payment_uuid', 'uuid');
    }

    public function paymentType(): HasOne
    {
        return $this->hasOne(PaymentType::class, 'slug', 'type');
    }

    // Functions ...
}
