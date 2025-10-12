<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pemakaian extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'tx_pemakaian';
    protected $primaryKey = 'pemakaian_id';
    public $timestamps = true;
    protected $keyType = 'bigint';
    public $incrementing = false;

    protected $fillable = [
        'pemakaian_id',
        'kantor_sar_id',
        'alpal_id',
        'bekal_id',
        'tanggal_pakai',
        'qty',
        'keterangan',
    ];

    protected $casts = [
        'tanggal_pakai' => 'date',
    ];

    public static function rules($id = null)
    {
        return [
            'kantor_sar_id' => ['required', 'exists:ms_kantor_sar,kantor_sar_id'],
            'alpal_id' => ['required', 'exists:tx_alpal,alpal_id'],
            'bekal_id' => ['required', 'exists:ms_bekal,bekal_id'],
            'tanggal_pakai' => ['required', 'date'],
            'qty' => ['required', 'integer', 'min:1'],
            'keterangan' => ['required', 'string', 'max:1000'],
        ];
    }

    public function kantorSar(): BelongsTo
    {
        return $this->belongsTo(KantorSar::class, 'kantor_sar_id', 'kantor_sar_id');
    }

    public function alpal(): BelongsTo
    {
        return $this->belongsTo(Alpal::class, 'alpal_id', 'alpal_id');
    }

    public function bekal(): BelongsTo
    {
        return $this->belongsTo(Bekal::class, 'bekal_id', 'bekal_id');
    }
}
