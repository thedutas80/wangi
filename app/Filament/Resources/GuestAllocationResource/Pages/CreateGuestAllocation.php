<?php

// Halaman tambah alokasi tamu — logika alokasi via AllocationService

namespace App\Filament\Resources\GuestAllocationResource\Pages;

use App\Filament\Resources\GuestAllocationResource;
use App\Services\AllocationService;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateGuestAllocation extends CreateRecord
{
    // Menghubungkan ke resource GuestAllocationResource
    protected static string $resource = GuestAllocationResource::class;

    // Menyimpan data baru — dilempar ke AllocationService (transaksi DB + locking)
    protected function handleRecordCreation(array $data): Model
    {
        try {
            return app(AllocationService::class)->createAllocation($data);
        } catch (\RuntimeException $e) {
            // Gagal jika kuota sesi sudah penuh
            Notification::make()->danger()->title($e->getMessage())->send();
            $this->halt();
        }
    }
}
