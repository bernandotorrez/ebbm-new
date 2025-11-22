<?php

namespace App\Filament\Resources\PemakaianResource\Pages;

use App\Filament\Resources\PemakaianResource;
use App\Models\Pemakaian;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreatePemakaian extends CreateRecord
{
    protected static string $resource = PemakaianResource::class;

    public function mount(): void
    {
        parent::mount();
        
        $user = auth()->user();
        
        // Cek apakah user adalah Admin atau Kanpus
        if ($user && in_array($user->level->value, [\App\Enums\LevelUser::ADMIN->value, \App\Enums\LevelUser::KANPUS->value])) {
            Notification::make()
                ->title('Akses Ditolak')
                ->body('Admin dan Kanpus tidak memiliki akses untuk membuat Pemakaian.')
                ->danger()
                ->send();
            
            $this->redirect(PemakaianResource::getUrl('index'));
        }
    }

    protected function getRedirectUrl(): string
    {
        // Redirect to the list page after creation
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Berhasil')
            ->body('Data pemakaian berhasil ditambahkan.');
    }

    public function getFormActions(): array
    {
        return [
            $this->getCreateFormAction()
                ->label('Buat'),
            $this->getCreateAnotherFormAction()
                ->label('Buat & Buat lainnya'),
            $this->getCancelFormAction()
                ->label('Batal'),
        ];
    }
    
    public function getTitle(): string
    {
        return 'Buat Pemakaian';
    }
}
