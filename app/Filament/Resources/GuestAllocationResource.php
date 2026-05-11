<?php

namespace App\Filament\Resources;

use App\Enums\AllocationSource;
use App\Filament\Resources\GuestAllocationResource\Pages;
use App\Models\ActivitySession;
use App\Models\GuestAllocation;
use App\Services\AllocationService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class GuestAllocationResource extends Resource
{
    protected static ?string $model = GuestAllocation::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'Operations';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\TextInput::make('guest_name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Select::make('activity_session_id')
                            ->label('Activity Session')
                            ->options(function () {
                                return ActivitySession::active()
                                    ->upcoming()
                                    ->with('attraction')
                                    ->get()
                                    ->mapWithKeys(fn ($session) => [
                                        $session->id => "{$session->attraction->name} - {$session->date} ({$session->start_time}-{$session->end_time}) [Avail: {$session->availableSeats()}]",
                                    ]);
                            })
                            ->required()
                            ->searchable()
                            ->preload()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state) {
                                    $session = ActivitySession::find($state);
                                    if ($session) {
                                        $set('max_capacity', $session->max_capacity);
                                        $set('occupied', $session->occupiedSeats());
                                        $set('available', $session->availableSeats());
                                    }
                                }
                            })
                            ->live(),
                        Forms\Components\Placeholder::make('capacity_info')
                            ->label('Session Capacity')
                            ->content(function (Get $get): string {
                                $available = $get('available');
                                $max = $get('max_capacity');
                                if ($available === null) {
                                    return 'Select a session to see capacity';
                                }

                                return "{$available} of {$max} seats available";
                            })
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('pax')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(function (Get $get) {
                                return (int) ($get('available') ?: 999999);
                            }),
                        Forms\Components\Select::make('source')
                            ->options(collect(AllocationSource::cases())->mapWithKeys(fn ($case) => [$case->value => $case->label()]))
                            ->required(),
                        Forms\Components\Textarea::make('notes')
                            ->rows(2)
                            ->maxLength(65535),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('guest_name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('activitySession.attraction.name')
                    ->label('Attraction')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('activitySession.date')
                    ->label('Session Date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('activitySession.start_time')
                    ->label('Time'),
                Tables\Columns\TextColumn::make('pax')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('source')
                    ->badge()
                    ->color(fn (AllocationSource $state): string => $state->color()),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Created By')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('source')
                    ->options(collect(AllocationSource::cases())->mapWithKeys(fn ($case) => [$case->value => $case->label()])),
                Tables\Filters\Filter::make('date')
                    ->form([
                        Forms\Components\DatePicker::make('date_from'),
                        Forms\Components\DatePicker::make('date_until'),
                    ])
                    ->query(fn ($query, array $data) => $query
                        ->when($data['date_from'], fn ($q, $v) => $q->whereHas('activitySession', fn ($sq) => $sq->where('date', '>=', $v)))
                        ->when($data['date_until'], fn ($q, $v) => $q->whereHas('activitySession', fn ($sq) => $sq->where('date', '<=', $v)))),
                Tables\Filters\SelectFilter::make('activity_session_id')
                    ->label('Session')
                    ->relationship('activitySession', 'id')
                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->attraction->name} - {$record->date}")
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->using(function ($record, array $data) {
                        try {
                            app(AllocationService::class)->updateAllocation($record, $data);
                            Notification::make()->success()->title('Allocation updated successfully')->send();
                        } catch (\RuntimeException $e) {
                            Notification::make()->danger()->title($e->getMessage())->send();

                            return null;
                        }
                    }),
                Tables\Actions\DeleteAction::make()
                    ->before(function ($record) {
                        app(AllocationService::class)->deleteAllocation($record);
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->before(function ($records) {
                            foreach ($records as $record) {
                                app(AllocationService::class)->deleteAllocation($record);
                            }
                        }),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGuestAllocations::route('/'),
            'create' => Pages\CreateGuestAllocation::route('/create'),
            'edit' => Pages\EditGuestAllocation::route('/{record}/edit'),
        ];
    }
}
