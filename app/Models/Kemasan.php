<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\PreventUpdateTimestamp;
use App\Traits\HasIsActive;

class Kemasan extends Model
{
    use HasFactory, SoftDeletes, PreventUpdateTimestamp, HasIsActive;

    protected $table = 'ms_kemasan';
    protected $primaryKey = 'kemasan_id';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = true;

    protected $fillable = [
        'kemasan_liter',
        'kemasan_pack',
        'pack_id',
        'created_by',
        'updated_by',
        'deleted_by',
        'is_active',
    ];

    protected $casts = [
        'kemasan_liter' => 'integer',
        'pack_id' => 'integer',
    ];

    public function pack(): BelongsTo
    {
        return $this->belongsTo(Pack::class, 'pack_id', 'pack_id');
    }

    public function pelumas(): HasMany
    {
        return $this->hasMany(Pelumas::class, 'kemasan_id', 'kemasan_id');
    }
}