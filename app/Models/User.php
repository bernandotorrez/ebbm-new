<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail, HasIsActive;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use App\Traits\PreventUpdateTimestamp;
use App\Traits\HasIsActive;

class User extends Authenticatable implements FilamentUser
{
    use HasFactory, Notifiable, SoftDeletes, PreventUpdateTimestamp, HasIsActive;

    protected $table = 'ms_user';
    protected $primaryKey = 'user_id';
    public $timestamps = true;
    protected $keyType = 'bigint';
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'kantor_sar_id',
        'alpal_id',
        'name',
        'username',
        'email',
        'password',
        'remember_token',
        'level'
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'level' => \App\Enums\LevelUser::class,
        ];
    }

    public function getAuthIdentifierName()
    {
        return 'username'; // atau 'email' jika masih ingin support keduanya
    }

    public function kantorSar()
    {
        return $this->belongsTo(KantorSar::class, 'kantor_sar_id', 'kantor_sar_id');
    }

    public function alpal()
    {
        return $this->belongsTo(Alpal::class, 'alpal_id', 'alpal_id');
    }

    public function canAccessPanel(Panel $panel): bool
    {
        // All authenticated users with valid roles can access the panel
        return $this->level !== null;
    }
}