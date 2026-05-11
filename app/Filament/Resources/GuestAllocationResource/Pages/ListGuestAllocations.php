<?php

// Halaman daftar semua alokasi tamu (tabel)

namespace App\Filament\Resources\GuestAllocationResource\Pages;

use App\Filament\Resources\GuestAllocationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListGuestAllocations extends ListRecords
{
    // Menghubungkan ke resource GuestAllocationResource
    protected static string $resource = GuestAllocationResource::class;

    // Tombol "+ Tambah Alokasi" di header
    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
