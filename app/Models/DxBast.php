<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\PreventUpdateTimestamp;
use App\Traits\HasIsActive;

class DxBast extends Model
{
    use HasFactory, SoftDeletes, PreventUpdateTimestamp, HasIsActive;

    protected $table = 'dx_bast';
    protected $primaryKey = 'detail_bast_id';
    public $timestamps = true;
    protected $keyType = 'int';
    public $incrementing = true;

    protected $fillable = [
        'detail_bast_id',
        'bast_id',
        'pelumas_id',
        'qty_mulai',
        'qty_diterima',
        'qty_masuk',
        'qty_terutang',
        'jumlah_harga_mulai',
        'jumlah_harga_diterima',
        'jumlah_harga_masuk',
        'jumlah_harga_terutang',
        'created_by',
        'updated_by',
        'deleted_by',
        'is_active',
    ];

    protected $casts = [
        'bast_id' => 'integer',
        'pelumas_id' => 'integer',
        'qty_mulai' => 'integer',
        'qty_diterima' => 'integer',
        'qty_masuk' => 'integer',
        'qty_terutang' => 'integer',
        'jumlah_harga_mulai' => 'decimal:2',
        'jumlah_harga_diterima' => 'decimal:2',
        'jumlah_harga_masuk' => 'decimal:2',
        'jumlah_harga_terutang' => 'decimal:2',
    ];

    // Relationships
    public function bast()
    {
        return $this->belongsTo(TxBast::class, 'bast_id', 'bast_id');
    }

    public function pelumas()
    {
        return $this->belongsTo(Pelumas::class, 'pelumas_id', 'pelumas_id');
    }
}
