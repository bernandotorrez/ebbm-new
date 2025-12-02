<?php

namespace App\Filament\Pages\Auth;

use Filament\Pages\Auth\Login as BaseLogin;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Illuminate\Validation\ValidationException;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;

class Login extends BaseLogin
{
    protected function getForms(): array
    {
        return [
            'form' => $this->form(
                $this->makeForm()
                    ->schema([
                        $this->getUsernameFormComponent(),
                        $this->getPasswordFormComponent(),
                        $this->getRememberFormComponent(),
                    ])
                    ->statePath('data'),
            ),
        ];
    }

    protected function getUsernameFormComponent(): Component
    {
        return TextInput::make('username')
            ->label('Username')
            ->required()
            ->autocomplete()
            ->autofocus()
            ->extraInputAttributes(['tabindex' => 1]);
    }

    protected function getCredentialsFromFormData(array $data): array
    {
        return [
            'username' => $data['username'],
            'password' => $data['password'],
        ];
    }

    public function authenticate(): ?\Filament\Http\Responses\Auth\Contracts\LoginResponse
    {
        try {
            return parent::authenticate();
        } catch (TooManyRequestsException $exception) {
            // Hitung waktu tunggu dalam menit dan detik
            $seconds = $exception->secondsUntilAvailable;
            $minutes = floor($seconds / 60);
            $remainingSeconds = $seconds % 60;
            
            $waitTime = $minutes > 0 
                ? "{$minutes} menit " . ($remainingSeconds > 0 ? "{$remainingSeconds} detik" : "")
                : "{$remainingSeconds} detik";
            
            // Custom error message untuk rate limiting
            Notification::make()
                ->title('Terlalu Banyak Percobaan Login')
                ->body("Anda telah mencoba login terlalu banyak kali. Akun Anda diblokir sementara untuk keamanan. Silakan tunggu {$waitTime} sebelum mencoba lagi.")
                ->danger()
                ->duration(15000)
                ->send();
            
            throw ValidationException::withMessages([
                'data.username' => "Terlalu banyak percobaan login. Silakan tunggu {$waitTime} sebelum mencoba lagi.",
            ]);
        } catch (ValidationException $exception) {
            // Tampilkan notifikasi untuk username/password salah
            Notification::make()
                ->title('Login Gagal')
                ->body('Username atau password yang Anda masukkan salah. Silakan periksa kembali dan coba lagi.')
                ->danger()
                ->duration(5000)
                ->send();
            
            throw $exception;
        }
    }
}
