<?php

// Halaman daftar semua sesi aktivitas (tabel)

namespace App\Filament\Resources\ActivitySessionResource\Pages;

use App\Filament\Resources\ActivitySessionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListActivitySessions extends ListRecords
{
    // Menghubungkan ke resource ActivitySessionResource
    protected static string $resource = ActivitySessionResource::class;

    // Tombol "+ Tambah Sesi" di header
    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
