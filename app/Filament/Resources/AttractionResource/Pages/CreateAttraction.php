<?php

// Halaman tambah atraksi baru

namespace App\Filament\Resources\AttractionResource\Pages;

use App\Filament\Resources\AttractionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAttraction extends CreateRecord
{
    // Menghubungkan ke resource AttractionResource
    // Form input otomatis di-generate dari resource ini
    // Setelah submit, data langsung disimpan ke DB via model Attraction
    protected static string $resource = AttractionResource::class;
}
