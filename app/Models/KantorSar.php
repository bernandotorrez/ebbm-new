<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class KantorSar extends Model
{
    use SoftDeletes;

    protected $table = 'kantor_sars';
    protected $primaryKey = 'kantor_sar_id';

    protected $fillable = [
        'kantor_sar',
    ];
}
