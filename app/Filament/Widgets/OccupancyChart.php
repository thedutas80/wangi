<?php

namespace App\Filament\Widgets;

use App\Models\ActivitySession;
use Filament\Widgets\ChartWidget;

class OccupancyChart extends ChartWidget
{
    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $sessions = ActivitySession::active()
            ->upcoming()
            ->with('attraction')
            ->get()
            ->take(10);

        return [
            'datasets' => [
                [
                    'label' => 'Occupancy %',
                    'data' => $sessions->map(fn ($s) => $s->occupancyPercentage()),
                    'backgroundColor' => $sessions->map(function ($s) {
                        if ($s->isFull()) {
                            return '#ef4444';
                        }
                        if ($s->isAlmostFull()) {
                            return '#f59e0b';
                        }

                        return '#22c55e';
                    })->toArray(),
                    'borderColor' => '#e5e7eb',
                ],
            ],
            'labels' => $sessions->map(fn ($s) => "{$s->attraction->name}\n{$s->date}"),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'max' => 100,
                ],
            ],
        ];
    }

    public function getDescription(): ?string
    {
        return 'Occupancy percentage for upcoming active sessions (top 10)';
    }
}
