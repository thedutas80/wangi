<?php

namespace App\Filament\Widgets;

use App\Models\ActivitySession;
use App\Models\Attraction;
use App\Models\GuestAllocation;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $today = now()->toDateString();

        $activeAttractions = Attraction::active()->count();
        $todaySessions = ActivitySession::where('date', $today)->count();
        $todayGuests = GuestAllocation::whereHas('activitySession', fn ($q) => $q->where('date', $today))->sum('pax');

        $allActiveSessions = ActivitySession::active()->upcoming()->get();
        $almostFull = $allActiveSessions->filter(fn ($s) => $s->isAlmostFull())->count();
        $full = $allActiveSessions->filter(fn ($s) => $s->isFull())->count();
        $upcoming = ActivitySession::upcoming()->count();

        return [
            Stat::make('Active Attractions', $activeAttractions)
                ->description('Currently active')
                ->descriptionIcon('heroicon-o-sparkles')
                ->color('success'),
            Stat::make("Today's Sessions", $todaySessions)
                ->description('Scheduled for today')
                ->descriptionIcon('heroicon-o-calendar')
                ->color('info'),
            Stat::make("Today's Guests", $todayGuests)
                ->description('Total pax allocated')
                ->descriptionIcon('heroicon-o-users')
                ->color('warning'),
            Stat::make('Almost Full', $almostFull)
                ->description('Sessions at 80%+ occupancy')
                ->descriptionIcon('heroicon-o-exclamation-triangle')
                ->color('warning'),
            Stat::make('Full Sessions', $full)
                ->description('Completely booked')
                ->descriptionIcon('heroicon-o-x-circle')
                ->color('danger'),
            Stat::make('Upcoming Sessions', $upcoming)
                ->description('Sessions from today onward')
                ->descriptionIcon('heroicon-o-clock')
                ->color('gray'),
        ];
    }
}
