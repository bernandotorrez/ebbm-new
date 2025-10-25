<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Bekal extends Model
{
    use SoftDeletes;

    protected $table = 'ms_bekal';
    protected $primaryKey = 'bekal_id';
    public $timestamps = true;
    protected $keyType = 'bigint';
    public $incrementing = false;

    protected $fillable = [
        'bekal_id',
        'golongan_bbm_id',
        'satuan_id',
        'bekal',
    ];

    public function golonganBbm()
    {
        return $this->belongsTo(GolonganBbm::class, 'golongan_bbm_id', 'golongan_bbm_id');
    }


    public function satuan()
    {
        return $this->belongsTo(Satuan::class, 'satuan_id', 'satuan_id');
    }
}
