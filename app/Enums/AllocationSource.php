<?php

namespace App\Enums;

enum AllocationSource: string
{
    case WalkIn = 'Walk In';
    case TravelAgent = 'Travel Agent';
    case HotelPartner = 'Hotel Partner';
    case OnlineBooking = 'Online Booking';
    case InternalReservation = 'Internal Reservation';

    public function label(): string
    {
        return $this->value;
    }

    public function color(): string
    {
        return match ($this) {
            self::WalkIn => 'gray',
            self::TravelAgent => 'warning',
            self::HotelPartner => 'info',
            self::OnlineBooking => 'success',
            self::InternalReservation => 'primary',
        };
    }
}
