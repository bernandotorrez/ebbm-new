<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\User;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->label('Hapus')
                ->visible(fn (): bool => $this->record->level !== \App\Enums\LevelUser::ADMIN)
                ->before(function () {
                    if ($this->record->level === \App\Enums\LevelUser::ADMIN) {
                        Notification::make()
                            ->title('Tidak Dapat Menghapus!')
                            ->body('User dengan level Admin tidak dapat dihapus.')
                            ->danger()
                            ->send();
                        return false;
                    }
                }),
            Actions\ForceDeleteAction::make()
                ->label('Hapus Permanen')
                ->visible(fn (): bool => $this->record->level !== \App\Enums\LevelUser::ADMIN)
                ->before(function () {
                    if ($this->record->level === \App\Enums\LevelUser::ADMIN) {
                        Notification::make()
                            ->title('Tidak Dapat Menghapus!')
                            ->body('User dengan level Admin tidak dapat dihapus secara permanen.')
                            ->danger()
                            ->send();
                        return false;
                    }
                }),
            Actions\RestoreAction::make()
                ->label('Pulihkan'),
        ];
    }

    protected function getRedirectUrl(): string
    {
        // Redirect to the list page after creation
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Apply ucwords() to the 'name' field before saving
        $data['name'] = ucwords($data['name']);
        
        // Only update password if it's filled
        if (empty($data['password'])) {
            unset($data['password']);
        }

        return $data;
    }

    protected function beforeSave(): void
    {
        // Get input values
        $userId = $this->record->user_id ?? null;
        $email = $this->data['email'] ?? null;

        // Check if the same record exists
        $exists = User::where('email', $email)
            ->where('user_id', '!=', $userId)
            ->exists();

        if ($exists) {
            // Show Filament error notification
            Notification::make()
                ->title('Error!')
                ->body('Email "'.$email.'" sudah ada')
                ->danger()
                ->send();

            // Prevent form submission
            $this->halt();
        }
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Berhasil')
            ->body('Data user berhasil diperbarui.');
    }

    public function getFormActions(): array
    {
        return [
            $this->getSaveFormAction()
                ->label('Simpan'),
            $this->getCancelFormAction()
                ->label('Batal'),
        ];
    }

    public function getTitle(): string
    {
        return 'Ubah User';
    }
}
