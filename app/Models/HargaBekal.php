<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HargaBekal extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'ms_harga_bekal';
    
    protected $primaryKey = 'harga_bekal_id';
    
    public $incrementing = true;
    
    protected $keyType = 'bigint';
    
    protected $fillable = [
        'kota_id',
        'bekal_id',
        'harga',
    ];

    protected $casts = [
        'harga_bekal_id' => 'integer',
        'harga' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Relationships
    public function kota()
    {
        return $this->belongsTo(Kota::class, 'kota_id', 'kota_id');
    }

    public function bekal()
    {
        return $this->belongsTo(Bekal::class, 'bekal_id', 'bekal_id');
    }
}