<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Bekal extends Model
{
    use SoftDeletes;

    protected $table = 'bekals';

    protected $primaryKey = 'bekal_id';

    protected $fillable = [
        'bekal',
        'satuan',
        'golongan_bbm_id',
        'satuan_id'
    ];

    public function golonganBbm()
    {
        return $this->belongsTo(GolonganBbm::class, 'golongan_bbm_id');
    }


    public function satuan()
    {
        return $this->belongsTo(Satuan::class, 'satuan_id');
    }
}
