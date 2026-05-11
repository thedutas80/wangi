<?php

// Halaman edit data user (admin/operator)

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    // Menghubungkan ke resource UserResource
    protected static string $resource = UserResource::class;

    // Tombol aksi: hapus user
    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
