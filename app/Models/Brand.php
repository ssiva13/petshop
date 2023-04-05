<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property string $title
 * @property string $slug
 * @property int $created_at
 * @property int $updated_at
 * @property int $deleted_at
 * @method static findOrFail($uuid)
 * @method static find($uuid)
 * @method static create(array $data)
 * @method static where(string $string, $uuid)
 * @method static when(mixed $first_name, \Closure $param)
 * @method static orderBy(mixed $sortBy, mixed $desc)
 */
class Brand extends Model
{
    use SoftDeletes, HasUuids, HasFactory;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'brands';

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
        'uuid', 'title', 'slug', 'created_at', 'updated_at', 'deleted_at',
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
        'title' => 'string', 'slug' => 'string', 'created_at' => 'timestamp',
        'updated_at' => 'timestamp', 'deleted_at' => 'timestamp',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected array $dates = [
        'created_at', 'updated_at', 'deleted_at',
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