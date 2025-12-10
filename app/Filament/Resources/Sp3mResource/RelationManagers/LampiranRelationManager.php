<?php

namespace App\Filament\Resources\Sp3mResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class LampiranRelationManager extends RelationManager
{
    protected static string $relationship = 'lampiran';

    protected static ?string $title = 'Lampiran';

    protected static ?string $modelLabel = 'Lampiran';

    protected static ?string $pluralModelLabel = 'Lampiran';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nama_file')
                    ->label('Nama File')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('Contoh: Surat Persetujuan, Dokumen Pendukung')
                    ->validationMessages([
                        'required' => 'Nama file harus diisi',
                    ]),
                Forms\Components\FileUpload::make('file_path')
                    ->label('File')
                    ->required()
                    ->disk('public')
                    ->directory('sp3m/lampiran')
                    ->visibility('public')
                    ->acceptedFileTypes(['application/pdf', 'image/*'])
                    ->maxSize(5120)
                    ->validationMessages([
                        'required' => 'File harus diunggah',
                        'file' => 'File harus berupa PDF atau gambar',
                        'max' => 'Ukuran file maksimal 5MB',
                    ])
                    ->uploadingMessage('Mengunggah...'),
                Forms\Components\Textarea::make('keterangan')
                    ->label('Keterangan')
                    ->maxLength(500)
                    ->rows(3)
                    ->placeholder('Keterangan tambahan (opsional)'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('nama_file')
            ->columns([
                Tables\Columns\TextColumn::make('nama_file')
                    ->label('Nama File')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('file_path')
                    ->label('File')
                    ->formatStateUsing(fn ($state) => basename($state))
                    ->url(fn ($record) => asset('storage/' . $record->file_path))
                    ->openUrlInNewTab()
                    ->color('primary')
                    ->icon('heroicon-o-document')
                    ->sortable(),
                Tables\Columns\TextColumn::make('keterangan')
                    ->label('Keterangan')
                    ->limit(50)
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Tambah Lampiran')
                    ->icon('heroicon-o-plus')
                    ->modalHeading('Tambah Lampiran')
                    ->modalButton('Simpan'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Lihat')
                    ->modalHeading('Detail Lampiran'),
                Tables\Actions\EditAction::make()
                    ->label('Ubah')
                    ->modalHeading('Ubah Lampiran')
                    ->modalButton('Simpan'),
                Tables\Actions\DeleteAction::make()
                    ->label('Hapus')
                    ->modalHeading('Konfirmasi Hapus Lampiran')
                    ->modalSubheading('Apakah Anda yakin ingin menghapus lampiran ini?')
                    ->modalButton('Ya, Hapus'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Hapus Terpilih')
                        ->modalHeading('Konfirmasi Hapus Lampiran')
                        ->modalSubheading('Apakah Anda yakin ingin menghapus lampiran yang dipilih?')
                        ->modalButton('Ya, Hapus'),
                ]),
            ])
            ->emptyStateHeading('Belum ada lampiran')
            ->emptyStateDescription('Klik tombol "Tambah Lampiran" untuk menambahkan lampiran.')
            ->emptyStateIcon('heroicon-o-document');
    }
}
