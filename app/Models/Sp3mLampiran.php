<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\PreventUpdateTimestamp;

class Sp3mLampiran extends Model
{
    use SoftDeletes, PreventUpdateTimestamp;

    protected $table = 'tx_sp3m_lampiran';
    protected $primaryKey = 'lampiran_id';
    public $timestamps = true;
    protected $keyType = 'int';
    public $incrementing = true;

    protected $fillable = [
        'sp3m_id',
        'nama_file',
        'file_path',
        'keterangan',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public function sp3m()
    {
        return $this->belongsTo(Sp3m::class, 'sp3m_id', 'sp3m_id');
    }
}
