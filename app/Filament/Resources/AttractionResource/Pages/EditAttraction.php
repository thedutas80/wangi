<?php

// Halaman edit / ubah data atraksi

namespace App\Filament\Resources\AttractionResource\Pages;

use App\Filament\Resources\AttractionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAttraction extends EditRecord
{
    // Menghubungkan ke resource AttractionResource
    protected static string $resource = AttractionResource::class;

    // Tombol aksi di header: hapus (soft delete) dan restore
    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),   // Soft delete (karena model Attraction pakai SoftDeletes)
            Actions\RestoreAction::make(),  // Restore data yang sudah di-soft-delete
        ];
    }
}
