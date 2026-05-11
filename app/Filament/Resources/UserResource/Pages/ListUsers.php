<?php

// Halaman daftar semua user admin/operator (tabel)

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListUsers extends ListRecords
{
    // Menghubungkan ke resource UserResource
    protected static string $resource = UserResource::class;

    // Tombol "+ Tambah User" di header
    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
