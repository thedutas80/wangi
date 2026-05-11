<?php

// Halaman edit / ubah sesi aktivitas

namespace App\Filament\Resources\ActivitySessionResource\Pages;

use App\Filament\Resources\ActivitySessionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditActivitySession extends EditRecord
{
    // Menghubungkan ke resource ActivitySessionResource
    protected static string $resource = ActivitySessionResource::class;

    // Tombol aksi: hapus sesi
    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
