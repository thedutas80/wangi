<?php

// Halaman daftar semua atraksi (tabel)

namespace App\Filament\Resources\AttractionResource\Pages;

use App\Filament\Resources\AttractionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAttractions extends ListRecords
{
    // Menghubungkan halaman ini ke resource AttractionResource
    // Resource inilah yang mendefinisikan model, form, tabel, dll.
    protected static string $resource = AttractionResource::class;

    // Tombol-tombol yang muncul di bagian atas halaman (header)
    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(), // Tombol "+ Tambah Atraksi" -> mengarah ke halaman CreateAttraction
        ];
    }
}
