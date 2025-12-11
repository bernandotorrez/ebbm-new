<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\PreventUpdateTimestamp;
use App\Traits\HasIsActive;

class Pack extends Model
{
    use SoftDeletes, PreventUpdateTimestamp, HasIsActive;

    protected $table = 'ms_pack';
    protected $primaryKey = 'pack_id';
    public $timestamps = true;
    public $incrementing = true;

    protected $fillable = [
        'pack_id',
        'nama_pack',
        'created_by',
        'updated_by',
        'deleted_by',
        'is_active',
    ];

    public function pelumas()
    {
        return $this->hasMany(Pelumas::class, 'pack_id', 'pack_id');
    }

    public function kemasans()
    {
        return $this->hasMany(Kemasan::class, 'pack_id', 'pack_id');
    }
}