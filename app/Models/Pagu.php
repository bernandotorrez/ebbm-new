<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\PreventUpdateTimestamp;

class Pagu extends Model
{
    use SoftDeletes, PreventUpdateTimestamp;

    protected $table = 'tx_pagu';
    protected $primaryKey = 'pagu_id';
    public $timestamps = true;
    protected $keyType = 'bigint';
    public $incrementing = false;

    protected $fillable = [
        'pagu_id',
        'golongan_bbm_id',
        'nilai_pagu',
        'tahun_anggaran',
        'dasar',
        'tanggal',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public function golonganBbm()
    {
        return $this->belongsTo(GolonganBbm::class, 'golongan_bbm_id', 'golongan_bbm_id');
    }
}