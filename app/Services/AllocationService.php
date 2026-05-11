<?php

namespace App\Services;

use App\Enums\SessionStatus;
use App\Models\ActivitySession;
use App\Models\GuestAllocation;
use Illuminate\Support\Facades\DB;

class AllocationService
{
    public function createAllocation(array $data): GuestAllocation
    {
        return DB::transaction(function () use ($data) {
            $session = ActivitySession::lockForUpdate()->findOrFail($data['activity_session_id']);

            $this->validateSessionForAllocation($session, $data['pax']);

            $data['user_id'] = auth()->id();

            return GuestAllocation::create($data);
        });
    }

    public function updateAllocation(GuestAllocation $allocation, array $data): GuestAllocation
    {
        return DB::transaction(function () use ($allocation, $data) {
            $newSessionId = $data['activity_session_id'] ?? $allocation->activity_session_id;
            $newPax = $data['pax'] ?? $allocation->pax;
            $sessionChanged = (int) $newSessionId !== (int) $allocation->activity_session_id;

            if ($sessionChanged) {
                $oldSession = ActivitySession::lockForUpdate()->findOrFail($allocation->activity_session_id);
                $newSession = ActivitySession::lockForUpdate()->findOrFail($newSessionId);

                $newSessionOccupied = $newSession->occupiedSeats() + $newPax;
                if ($newSessionOccupied > $newSession->max_capacity) {
                    throw new \RuntimeException(
                        'Cannot reallocate: new session capacity exceeded. '
                        ."Available: {$newSession->availableSeats()}, Required: {$newPax}"
                    );
                }

                $this->validateSessionForAllocation($newSession, $newPax);

                $allocation->update($data);
            } else {
                $session = ActivitySession::lockForUpdate()->findOrFail($newSessionId);
                $oldPax = $allocation->pax;
                $paxDiff = $newPax - $oldPax;

                if ($paxDiff > 0) {
                    $occupied = $session->occupiedSeats() + $paxDiff;
                    if ($occupied > $session->max_capacity) {
                        throw new \RuntimeException(
                            'Cannot increase pax: capacity exceeded. '
                            ."Available: {$session->availableSeats()}, Additional needed: {$paxDiff}"
                        );
                    }
                }

                $allocation->update($data);
            }

            return $allocation->fresh();
        });
    }

    public function deleteAllocation(GuestAllocation $allocation): void
    {
        DB::transaction(function () use ($allocation) {
            $allocation->delete();
        });
    }

    private function validateSessionForAllocation(ActivitySession $session, int $pax): void
    {
        if ($session->status !== SessionStatus::Active) {
            throw new \RuntimeException('Cannot allocate to an inactive or blocked session.');
        }

        if ($session->date < now()->toDateString()) {
            throw new \RuntimeException('Cannot allocate to a past session.');
        }

        if ($session->date == now()->toDateString() && $session->end_time < now()->format('H:i')) {
            throw new \RuntimeException('Cannot allocate to a session that has already ended.');
        }

        $occupied = $session->occupiedSeats();
        if (($occupied + $pax) > $session->max_capacity) {
            throw new \RuntimeException(
                "Capacity exceeded. Available: {$session->availableSeats()}, Required: {$pax}"
            );
        }
    }
}
