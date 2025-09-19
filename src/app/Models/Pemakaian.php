<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pemakaian extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
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
            'kantor_sar_id' => ['required', 'exists:kantor_sars,kantor_sar_id'],
            'alpal_id' => ['required', 'exists:alpals,alpal_id'],
            'bekal_id' => ['required', 'exists:bekals,bekal_id'],
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
