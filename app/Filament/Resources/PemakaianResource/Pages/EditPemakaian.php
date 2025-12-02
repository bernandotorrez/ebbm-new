<?php

namespace App\Filament\Resources\PemakaianResource\Pages;

use App\Filament\Resources\PemakaianResource;
use App\Models\Alpal;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditPemakaian extends EditRecord
{
    protected static string $resource = PemakaianResource::class;

    protected function getFormActions(): array
    {
        return [
            $this->getSaveFormAction()
                ->label('Simpan')
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
        $newQtyRaw = $this->data['qty'] ?? 0;
        
        // Convert qty from formatted string to integer
        $newQty = (int) str_replace(['.', ',', ' '], '', $newQtyRaw);
        
        $oldQty = $this->record->qty ?? 0;
        $oldAlpalId = $this->record->alpal_id;
        
        if (!$alpalId || $newQty <= 0) {
            return false;
        }
        
        // Jika alpal berubah
        if ($oldAlpalId != $alpalId) {
            $newAlpal = Alpal::find($alpalId);
            if (!$newAlpal) {
                return false;
            }
            
            return $newQty > $newAlpal->rob;
        } else {
            // Alpal sama, cek berdasarkan ROB + qty lama
            $alpal = Alpal::find($alpalId);
            if (!$alpal) {
                return false;
            }
            
            $availableQty = $alpal->rob + $oldQty;
            return $newQty > $availableQty;
        }
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Convert qty from formatted string to integer
        $data['qty'] = (int) str_replace(['.', ',', ' '], '', $data['qty']);
        
        return $data;
    }

    protected function beforeSave(): void
    {
        // Get input values - PENTING: konversi qty dari string ke integer
        $alpalId = $this->data['alpal_id'] ?? null;
        $newQtyRaw = $this->data['qty'] ?? 0;
        
        // Convert qty from formatted string to integer (karena beforeSave dipanggil sebelum mutateFormDataBeforeSave)
        $newQty = (int) str_replace(['.', ',', ' '], '', $newQtyRaw);
        
        $oldQty = $this->record->qty ?? 0;
        $oldAlpalId = $this->record->alpal_id;

        // Hitung selisih qty
        $qtyDiff = $newQty - $oldQty;

        // Jika alpal berubah
        if ($oldAlpalId != $alpalId) {
            // Kembalikan ROB ke alpal lama
            $oldAlpal = Alpal::find($oldAlpalId);
            if ($oldAlpal) {
                $oldAlpal->rob += $oldQty;
                $oldAlpal->save();
            }

            // Kurangi ROB dari alpal baru
            $newAlpal = Alpal::find($alpalId);
            if (!$newAlpal) {
                Notification::make()
                    ->title('Gagal Mengubah Pemakaian!')
                    ->body('Alpal baru tidak ditemukan.')
                    ->danger()
                    ->duration(5000)
                    ->send();
                $this->halt();
            }

            // Validasi ROB tidak boleh negatif (maksimal 0)
            $newRob = $newAlpal->rob - $newQty;
            
            if ($newRob < 0) {
                $newQtyFormatted = number_format($newQty, 0, ',', '.');
                $robFormatted = number_format($newAlpal->rob, 0, ',', '.');
                
                Notification::make()
                    ->title('Gagal Mengubah Pemakaian!')
                    ->body("Qty pemakaian ({$newQtyFormatted}) melebihi ROB alpal baru ({$robFormatted}). ROB tidak boleh negatif (minimal 0). Silakan kurangi qty.")
                    ->danger()
                    ->duration(7000)
                    ->send();
                $this->halt();
            }

            $newAlpal->rob = $newRob;
            $newAlpal->save();
        } else {
            // Alpal sama, hanya update berdasarkan selisih qty
            $alpal = Alpal::find($alpalId);
            
            if (!$alpal) {
                Notification::make()
                    ->title('Gagal Mengubah Pemakaian!')
                    ->body('Alpal tidak ditemukan.')
                    ->danger()
                    ->duration(5000)
                    ->send();
                $this->halt();
            }

            // Hitung ROB baru setelah perubahan
            $newRob = $alpal->rob - $qtyDiff;

            // Validasi ROB tidak boleh negatif
            if ($newRob < 0) {
                $newQtyFormatted = number_format($newQty, 0, ',', '.');
                $robFormatted = number_format($alpal->rob, 0, ',', '.');
                $availableQty = $alpal->rob + $oldQty;
                $availableQtyFormatted = number_format($availableQty, 0, ',', '.');
                
                Notification::make()
                    ->title('Gagal Mengubah Pemakaian!')
                    ->body("Qty baru ({$newQtyFormatted}) melebihi qty yang tersedia ({$availableQtyFormatted}). ROB saat ini: {$robFormatted}. Silakan kurangi qty.")
                    ->danger()
                    ->duration(7000)
                    ->send();
                $this->halt();
            }

            // Update ROB di alpal
            $alpal->rob = $newRob;
            $alpal->save();
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->label('Hapus')
                ->before(function () {
                    // Kembalikan ROB ke alpal saat delete
                    $alpal = Alpal::find($this->record->alpal_id);
                    if ($alpal) {
                        $alpal->rob += $this->record->qty;
                        $alpal->save();
                    }
                }),
            Actions\ForceDeleteAction::make()
                ->label('Hapus Permanen')
                ->before(function () {
                    // Kembalikan ROB ke alpal saat force delete
                    $alpal = Alpal::find($this->record->alpal_id);
                    if ($alpal) {
                        $alpal->rob += $this->record->qty;
                        $alpal->save();
                    }
                }),
            Actions\RestoreAction::make()
                ->label('Pulihkan')
                ->after(function () {
                    // Kurangi ROB dari alpal saat restore
                    $alpal = Alpal::find($this->record->alpal_id);
                    if ($alpal) {
                        $alpal->rob -= $this->record->qty;
                        $alpal->save();
                    }
                }),
        ];
    }

    protected function getRedirectUrl(): string
    {
        // Redirect to the list page after creation
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Berhasil')
            ->body('Data pemakaian berhasil diperbarui.');
    }
    
    public function getTitle(): string
    {
        return 'Ubah Pemakaian';
    }
}
