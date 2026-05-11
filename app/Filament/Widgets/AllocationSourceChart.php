<?php

namespace App\Filament\Widgets;

use App\Models\GuestAllocation;
use Filament\Widgets\ChartWidget;

class AllocationSourceChart extends ChartWidget
{
    protected static ?int $sort = 3;

    protected function getData(): array
    {
        $sources = GuestAllocation::toBase()
            ->selectRaw('source, SUM(pax) as total')
            ->groupBy('source')
            ->get()
            ->pluck('total', 'source');

        $colors = [
            'Walk In' => '#9ca3af',
            'Travel Agent' => '#f59e0b',
            'Hotel Partner' => '#3b82f6',
            'Online Booking' => '#22c55e',
            'Internal Reservation' => '#6366f1',
        ];

        return [
            'datasets' => [
                [
                    'label' => 'Guests by Source',
                    'data' => $sources->values()->toArray(),
                    'backgroundColor' => $sources->keys()->map(fn ($k) => $colors[$k] ?? '#6b7280')->toArray(),
                ],
            ],
            'labels' => $sources->keys()->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    public function getDescription(): ?string
    {
        return 'Guest allocation distribution by source channel';
    }
}
