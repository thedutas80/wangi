<?php

namespace App\Filament\Resources;

use App\Enums\SessionStatus;
use App\Filament\Resources\ActivitySessionResource\Pages;
use App\Models\ActivitySession;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ActivitySessionResource extends Resource
{
    protected static ?string $model = ActivitySession::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationGroup = 'Operations';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\Select::make('attraction_id')
                            ->label('Attraction')
                            ->relationship('attraction', 'name', fn ($query) => $query->active())
                            ->required()
                            ->searchable()
                            ->preload(),
                        Forms\Components\DatePicker::make('date')
                            ->required()
                            ->minDate(now()->subDay()),
                        Forms\Components\TimePicker::make('start_time')
                            ->required()
                            ->seconds(false),
                        Forms\Components\TimePicker::make('end_time')
                            ->required()
                            ->seconds(false)
                            ->after('start_time')
                            ->validationAttribute('end time')
                            ->helperText('Must be after start time'),
                        Forms\Components\TextInput::make('max_capacity')
                            ->required()
                            ->numeric()
                            ->minValue(1),
                        Forms\Components\Select::make('status')
                            ->options(collect(SessionStatus::cases())->mapWithKeys(fn ($case) => [$case->value => $case->label()]))
                            ->required(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('attraction.name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('start_time'),
                Tables\Columns\TextColumn::make('end_time'),
                Tables\Columns\TextColumn::make('max_capacity')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('occupied_seats')
                    ->label('Occupied')
                    ->getStateUsing(fn ($record) => $record->occupiedSeats())
                    ->sortable(),
                Tables\Columns\TextColumn::make('available_seats')
                    ->label('Available')
                    ->getStateUsing(fn ($record) => $record->availableSeats())
                    ->badge()
                    ->color(fn ($record) => match (true) {
                        $record->isFull() || $record->status === SessionStatus::Blocked => 'danger',
                        $record->isAlmostFull() => 'warning',
                        default => 'success',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('occupancy_pct')
                    ->label('Occupancy')
                    ->getStateUsing(fn ($record) => $record->occupancyPercentage().'%')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (SessionStatus $state): string => $state->color()),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('attraction_id')
                    ->label('Attraction')
                    ->relationship('attraction', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('status')
                    ->options(collect(SessionStatus::cases())->mapWithKeys(fn ($case) => [$case->value => $case->label()])),
                Tables\Filters\Filter::make('date')
                    ->form([
                        Forms\Components\DatePicker::make('date_from'),
                        Forms\Components\DatePicker::make('date_until'),
                    ])
                    ->query(fn ($query, array $data) => $query
                        ->when($data['date_from'], fn ($q, $v) => $q->where('date', '>=', $v))
                        ->when($data['date_until'], fn ($q, $v) => $q->where('date', '<=', $v))),
                Tables\Filters\TernaryFilter::make('availability')
                    ->label('Availability')
                    ->placeholder('All sessions')
                    ->trueLabel('Available')
                    ->falseLabel('Full')
                    ->queries(
                        true: fn ($query) => $query->where('status', SessionStatus::Active)->whereHas('guestAllocations', havingRaw: 'SUM(pax) < activity_sessions.max_capacity'),
                        false: fn ($query) => $query->where(function ($q) {
                            $q->where('status', '!=', SessionStatus::Active)
                                ->orWhereRaw('(SELECT COALESCE(SUM(pax), 0) FROM guest_allocations WHERE activity_session_id = activity_sessions.id) >= max_capacity');
                        }),
                        blank: fn ($query) => $query,
                    ),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('date', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            ActivitySessionResource\RelationManagers\GuestAllocationsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListActivitySessions::route('/'),
            'create' => Pages\CreateActivitySession::route('/create'),
            'edit' => Pages\EditActivitySession::route('/{record}/edit'),
        ];
    }
}
