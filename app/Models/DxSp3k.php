<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\PreventUpdateTimestamp;

class DxSp3k extends Model
{
    use HasFactory, SoftDeletes, PreventUpdateTimestamp;

    protected $table = 'dx_sp3k';
    protected $primaryKey = 'detail_sp3k_id';
    public $timestamps = true;
    protected $keyType = 'int';
    public $incrementing = true;

    protected $fillable = [
        'detail_sp3k_id',
        'sp3k_id',
        'pelumas_id',
        'qty',
        'harga',
        'sort',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'sp3k_id' => 'integer',
        'pelumas_id' => 'integer',
        'qty' => 'integer',
        'harga' => 'decimal:2',
        'sort' => 'integer',
    ];

    // Relationships
    public function sp3k()
    {
        return $this->belongsTo(TxSp3k::class, 'sp3k_id', 'sp3k_id');
    }

    public function pelumas()
    {
        return $this->belongsTo(Pelumas::class, 'pelumas_id', 'pelumas_id');
    }
}