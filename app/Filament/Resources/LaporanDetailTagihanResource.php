<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LaporanDetailTagihanResource\Pages;
use App\Models\ViewLaporanDetailTagihan;
use App\Enums\LevelUser;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Auth;

class LaporanDetailTagihanResource extends Resource
{
    protected static ?string $model = ViewLaporanDetailTagihan::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Laporan';

    protected static ?string $navigationLabel = 'Laporan Detail Tagihan';

    protected static ?int $navigationSort = 3;

    public static function getModelLabel(): string
    {
        return 'Laporan Detail Tagihan';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Laporan Detail Tagihan';
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\LaporanDetailTagihan::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canAccess(): bool
    {
        $user = Auth::user();
        
        // Hanya level Kanpus yang bisa akses
        return $user && $user->level->value === LevelUser::KANPUS->value;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }
}
