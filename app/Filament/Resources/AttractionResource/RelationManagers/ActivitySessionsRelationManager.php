<?php

namespace App\Filament\Resources\AttractionResource\RelationManagers;

use App\Enums\SessionStatus;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ActivitySessionsRelationManager extends RelationManager
{
    protected static string $relationship = 'activitySessions';

    protected static ?string $recordTitleAttribute = 'id';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\DatePicker::make('date')
                    ->required(),
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
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('start_time'),
                Tables\Columns\TextColumn::make('end_time'),
                Tables\Columns\TextColumn::make('max_capacity')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (SessionStatus $state): string => $state->color()),
                Tables\Columns\TextColumn::make('occupied_seats')
                    ->label('Occupied')
                    ->getStateUsing(fn ($record) => $record->occupiedSeats())
                    ->sortable(),
                Tables\Columns\TextColumn::make('available_seats')
                    ->label('Available')
                    ->getStateUsing(fn ($record) => $record->availableSeats())
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(collect(SessionStatus::cases())->mapWithKeys(fn ($case) => [$case->value => $case->label()])),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
