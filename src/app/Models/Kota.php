<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Kota extends Model
{
    use SoftDeletes;

    protected $table = 'kotas';
    protected $primaryKey = 'kota_id';

    protected $fillable = [
        'kota',
    ];
}
