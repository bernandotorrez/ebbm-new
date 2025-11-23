<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\PreventUpdateTimestamp;

class TxBast extends Model
{
    use HasFactory, SoftDeletes, PreventUpdateTimestamp;

    protected $table = 'tx_bast';
    protected $primaryKey = 'bast_id';
    public $timestamps = true;
    protected $keyType = 'int';
    public $incrementing = true;

    protected $fillable = [
        'bast_id',
        'sp3k_id',
        'kantor_sar_id',
        'tahun_anggaran',
        'tanggal_bast',
        'sequence',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'sp3k_id' => 'integer',
        'kantor_sar_id' => 'integer',
        'sequence' => 'integer',
        'tanggal_bast' => 'date',
    ];

    // Relationships
    public function sp3k()
    {
        return $this->belongsTo(TxSp3k::class, 'sp3k_id', 'sp3k_id');
    }

    public function kantorSar()
    {
        return $this->belongsTo(KantorSar::class, 'kantor_sar_id', 'kantor_sar_id');
    }

    public function details()
    {
        return $this->hasMany(DxBast::class, 'bast_id', 'bast_id')->orderBy('sort');
    }

    // Boot method untuk auto-increment sequence
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->sequence) {
                // Get the last sequence for this sp3k_id
                $lastBast = static::where('sp3k_id', $model->sp3k_id)
                    ->orderBy('sequence', 'desc')
                    ->first();
                
                $model->sequence = $lastBast ? $lastBast->sequence + 1 : 1;
            }
        });
    }
}
