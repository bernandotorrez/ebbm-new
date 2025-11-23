<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\PreventUpdateTimestamp;

class DxBast extends Model
{
    use HasFactory, SoftDeletes, PreventUpdateTimestamp;

    protected $table = 'dx_bast';
    protected $primaryKey = 'detail_bast_id';
    public $timestamps = true;
    protected $keyType = 'int';
    public $incrementing = true;

    protected $fillable = [
        'detail_bast_id',
        'bast_id',
        'pelumas_id',
        'qty_bast',
        'sisa_qty_sp3k',
        'file_upload_lampiran',
        'sort',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'bast_id' => 'integer',
        'pelumas_id' => 'integer',
        'qty_bast' => 'integer',
        'sisa_qty_sp3k' => 'integer',
        'sort' => 'integer',
    ];

    // Relationships
    public function bast()
    {
        return $this->belongsTo(TxBast::class, 'bast_id', 'bast_id');
    }

    public function pelumas()
    {
        return $this->belongsTo(Pelumas::class, 'pelumas_id', 'pelumas_id');
    }
}
