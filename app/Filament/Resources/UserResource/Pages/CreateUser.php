<?php

// Halaman tambah user baru (admin/operator)

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    // Menghubungkan ke resource UserResource
    // Form input (nama, email, password, role) otomatis di-generate dari resource
    protected static string $resource = UserResource::class;
}
