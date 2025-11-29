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
        'tanggal_bast',
        'bast_ke',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'sp3k_id' => 'integer',
        'bast_ke' => 'integer',
        'tanggal_bast' => 'date',
    ];

    // Relationships
    public function sp3k()
    {
        return $this->belongsTo(TxSp3k::class, 'sp3k_id', 'sp3k_id');
    }

    public function details()
    {
        return $this->hasMany(DxBast::class, 'bast_id', 'bast_id');
    }

    // Boot method untuk auto-increment bast_ke
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->bast_ke) {
                // Get the last bast_ke for this sp3k_id
                $lastBast = static::where('sp3k_id', $model->sp3k_id)
                    ->orderBy('bast_ke', 'desc')
                    ->first();
                
                $model->bast_ke = $lastBast ? $lastBast->bast_ke + 1 : 1;
            }
        });
    }
}
