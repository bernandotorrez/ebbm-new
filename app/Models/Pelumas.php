<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\PreventUpdateTimestamp;
use App\Traits\HasIsActive;

class Pelumas extends Model
{
    use HasFactory, SoftDeletes, PreventUpdateTimestamp, HasIsActive;

    protected $table = 'ms_pelumas';
    protected $primaryKey = 'pelumas_id';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = true;

    protected $fillable = [
        'nama_pelumas',
        'pack_id',
        'kemasan_id',
        'isi',
        'harga',
        'tahun',
        'created_by',
        'updated_by',
        'deleted_by',
        'is_active',
    ];

    protected $casts = [
        'pack_id' => 'integer',
        'kemasan_id' => 'integer',
        'isi' => 'integer',
        'harga' => 'decimal:2',
        'tahun' => 'integer',
    ];

    // Relationships
    public function pack(): BelongsTo
    {
        return $this->belongsTo(Pack::class, 'pack_id', 'pack_id');
    }

    public function kemasan(): BelongsTo
    {
        return $this->belongsTo(Kemasan::class, 'kemasan_id', 'kemasan_id');
    }

    public function dxSp3ks(): HasMany
    {
        return $this->hasMany(DxSp3k::class, 'pelumas_id', 'pelumas_id');
    }

    public function dxBasts(): HasMany
    {
        return $this->hasMany(DxBast::class, 'pelumas_id', 'pelumas_id');
    }
}