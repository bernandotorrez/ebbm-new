<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\PreventUpdateTimestamp;

class HargaBekal extends Model
{
    use HasFactory, SoftDeletes, PreventUpdateTimestamp;

    protected $table = 'ms_harga_bekal';
    
    protected $primaryKey = 'harga_bekal_id';
    
    public $incrementing = true;
    
    protected $keyType = 'bigint';
    
    protected $fillable = [
        'wilayah_id',
        'bekal_id',
        'harga',
        'tanggal_update',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'harga_bekal_id' => 'integer',
        'harga' => 'decimal:2',
        'tanggal_update' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Relationships
    public function wilayah()
    {
        return $this->belongsTo(Wilayah::class, 'wilayah_id', 'wilayah_id');
    }

    public function bekal()
    {
        return $this->belongsTo(Bekal::class, 'bekal_id', 'bekal_id');
    }
}