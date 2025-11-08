<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\PreventUpdateTimestamp;

class Pelumas extends Model
{
    use HasFactory, SoftDeletes, PreventUpdateTimestamp;

    protected $table = 'ms_pelumas';
    protected $primaryKey = 'pelumas_id';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = true;

    protected $fillable = [
        'nama_pelumas',
        'pack_id',
        'kemasan_id',
        'isi',
        'harga',
        'tahun',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'pack_id' => 'integer',
        'kemasan_id' => 'integer',
        'isi' => 'integer',
        'harga' => 'decimal:2',
        'tahun' => 'integer',
    ];

    // Relationships
    public function pack()
    {
        return $this->belongsTo(Pack::class, 'pack_id', 'pack_id');
    }

    public function kemasan()
    {
        return $this->belongsTo(Kemasan::class, 'kemasan_id', 'kemasan_id');
    }
}