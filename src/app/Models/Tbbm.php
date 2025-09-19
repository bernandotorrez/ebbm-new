<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tbbm extends Model
{
    use SoftDeletes;

    protected $table = 'tbbms';

    protected $primaryKey = 'tbbm_id';

    protected $fillable = [
        'kota_id',
        'plant',
        'depot',
        'pbbkb',
        'ship_to',
    ];

    public function kota()
    {
        return $this->belongsTo(Kota::class, 'kota_id');
    }
}
