<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\PreventUpdateTimestamp;

class TxSp3k extends Model
{
    use HasFactory, SoftDeletes, PreventUpdateTimestamp;

    protected $table = 'tx_sp3k';
    protected $primaryKey = 'sp3k_id';
    public $timestamps = true;
    protected $keyType = 'int';
    public $incrementing = true;

    protected $fillable = [
        'sp3k_id',
        'kantor_sar_id',
        'nomor_sp3k',
        'tahun_anggaran',
        'tanggal_sp3k',
        'tw',
        'jumlah_qty',
        'jumlah_harga',
        'jumlah_liter',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'kantor_sar_id' => 'integer',
        'jumlah_qty' => 'integer',
        'jumlah_harga' => 'decimal:2',
        'jumlah_liter' => 'integer',
    ];

    // Relationships
    public function kantorSar()
    {
        return $this->belongsTo(KantorSar::class, 'kantor_sar_id', 'kantor_sar_id');
    }

    public function details()
    {
        return $this->hasMany(DxSp3k::class, 'sp3k_id', 'sp3k_id')->orderBy('sort');
    }
}
