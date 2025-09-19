<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PosSandar extends Model
{
    use SoftDeletes;

    protected $table = 'pos_sandars';

    protected $primaryKey = 'pos_sandar_id';

    protected $fillable = [
        'pos_sandar',
    ];
}
