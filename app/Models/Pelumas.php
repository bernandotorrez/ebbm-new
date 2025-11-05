<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pelumas extends Model
{
    use HasFactory, SoftDeletes;

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
    ];

    protected $casts = [
        'pack_id' => 'integer',
        'kemasan_id' => 'integer',
        'isi' => 'integer',
        'harga' => 'decimal:2',
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