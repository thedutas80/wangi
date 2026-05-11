<?php

// Halaman edit alokasi tamu — menggunakan AllocationService untuk logika bisnis

namespace App\Filament\Resources\GuestAllocationResource\Pages;

use App\Filament\Resources\GuestAllocationResource;
use App\Services\AllocationService;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditGuestAllocation extends EditRecord
{
    // Menghubungkan ke resource GuestAllocationResource
    protected static string $resource = GuestAllocationResource::class;

    // Memodifikasi data form sebelum disimpan (bisa ditimpa untuk validasi tambahan)
    protected function mutateFormDataBeforeSave(array $data): array
    {
        return $data;
    }

    // Menyimpan perubahan alokasi — dilempar ke AllocationService (transaksi DB + row locking)
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        try {
            return app(AllocationService::class)->updateAllocation($record, $data);
        } catch (\RuntimeException $e) {
            // Tampilkan notif error jika kuota tidak mencukupi dll.
            Notification::make()->danger()->title($e->getMessage())->send();
            $this->halt(); // Hentikan proses simpan
        }
    }

    // Tombol aksi: hapus — panggil AllocationService dulu sebelum hapus
    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->before(function ($record) {
                    app(AllocationService::class)->deleteAllocation($record);
                }),
        ];
    }
}
