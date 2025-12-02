<?php

namespace App\Filament\Resources\PemakaianResource\Pages;

use App\Filament\Resources\PemakaianResource;
use App\Models\Pemakaian;
use App\Models\Alpal;
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

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Convert qty from formatted string to integer
        $data['qty'] = (int) str_replace(['.', ',', ' '], '', $data['qty']);
        
        return $data;
    }

    protected function beforeCreate(): void
    {
        // Get input values - PENTING: konversi qty dari string ke integer
        $alpalId = $this->data['alpal_id'] ?? null;
        $qtyRaw = $this->data['qty'] ?? 0;
        
        // Convert qty from formatted string to integer (karena beforeCreate dipanggil sebelum mutateFormDataBeforeCreate)
        $qty = (int) str_replace(['.', ',', ' '], '', $qtyRaw);

        // Validasi ROB di Alpal
        $alpal = Alpal::find($alpalId);
        
        if (!$alpal) {
            Notification::make()
                ->title('Gagal Membuat Pemakaian!')
                ->body('Alpal tidak ditemukan.')
                ->danger()
                ->duration(5000)
                ->send();
            $this->halt();
        }

        // Validasi ROB tidak boleh negatif setelah dikurangi (maksimal 0)
        $newRob = $alpal->rob - $qty;
        
        if ($newRob < 0) {
            $qtyFormatted = number_format($qty, 0, ',', '.');
            $robFormatted = number_format($alpal->rob, 0, ',', '.');
            
            Notification::make()
                ->title('Gagal Membuat Pemakaian!')
                ->body("Qty pemakaian ({$qtyFormatted}) melebihi ROB alpal ({$robFormatted}). ROB tidak boleh negatif (minimal 0). Silakan kurangi qty.")
                ->danger()
                ->duration(7000)
                ->send();
            $this->halt();
        }

        // Kurangi ROB di Alpal
        $alpal->rob -= $qty;
        $alpal->save();
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
                ->label('Buat')
                ->disabled(function () {
                    return $this->isQtyInvalid();
                }),
            $this->getCreateAnotherFormAction()
                ->label('Buat & Buat lainnya')
                ->disabled(function () {
                    return $this->isQtyInvalid();
                }),
            $this->getCancelFormAction()
                ->label('Batal'),
        ];
    }
    
    protected function isQtyInvalid(): bool
    {
        $alpalId = $this->data['alpal_id'] ?? null;
        $qtyRaw = $this->data['qty'] ?? 0;
        
        // Convert qty from formatted string to integer
        $qty = (int) str_replace(['.', ',', ' '], '', $qtyRaw);
        
        if (!$alpalId || $qty <= 0) {
            return false;
        }
        
        $alpal = Alpal::find($alpalId);
        if (!$alpal) {
            return false;
        }
        
        return $qty > $alpal->rob;
    }
    
    public function getTitle(): string
    {
        return 'Buat Pemakaian';
    }
}
