<?php

// Halaman tambah sesi aktivitas baru

namespace App\Filament\Resources\ActivitySessionResource\Pages;

use App\Filament\Resources\ActivitySessionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateActivitySession extends CreateRecord
{
    // Menghubungkan ke resource ActivitySessionResource
    // Form otomatis dari resource; setelah submit, data langsung disimpan
    protected static string $resource = ActivitySessionResource::class;
}
