<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property string $title
 * @property string $description
 * @property float  $price
 * @property int    $created_at
 * @property int    $updated_at
 * @property int    $deleted_at
 * @method static find($uuid)
 * @method static create(array $data)
 * @method static orderBy(mixed $sortBy, mixed $desc)
 * @method static when(mixed $title, \Closure $param)
 */
class Product extends Model
{
    use SoftDeletes, HasUuids, HasFactory;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'products';

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
        'uuid', 'category_uuid', 'brand_uuid', 'title', 'price', 'description',
        'metadata', 'created_at', 'updated_at', 'deleted_at'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'id', 'deleted_at'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'title' => 'string', 'price' => 'double', 'description' => 'string', 'created_at' => 'timestamp',
        'updated_at' => 'timestamp', 'deleted_at' => 'timestamp', 'metadata' => 'array'
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected array $dates = [
        'created_at', 'updated_at', 'deleted_at'
    ];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var boolean
     */
    public $timestamps = true;

    // Scopes...

    // Relations ...
    public function category(): HasOne
    {
        return $this->hasOne(Category::class, 'uuid', 'category_uuid');
    }
    public function brand(): HasOne
    {
        return $this->hasOne(Brand::class, 'uuid', 'brand_uuid');
    }

    // Functions ...

}
