<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pagu extends Model
{
    use SoftDeletes;

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
        'tanggal'
    ];

    public function golonganBbm()
    {
        return $this->belongsTo(GolonganBbm::class, 'golongan_bbm_id', 'golongan_bbm_id');
    }
}
