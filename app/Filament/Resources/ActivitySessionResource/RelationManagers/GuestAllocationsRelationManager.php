<?php

namespace App\Filament\Resources\ActivitySessionResource\RelationManagers;

use App\Enums\AllocationSource;
use App\Services\AllocationService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class GuestAllocationsRelationManager extends RelationManager
{
    protected static string $relationship = 'guestAllocations';

    protected static ?string $recordTitleAttribute = 'guest_name';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('guest_name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('pax')
                    ->required()
                    ->numeric()
                    ->minValue(1),
                Forms\Components\Select::make('source')
                    ->options(collect(AllocationSource::cases())->mapWithKeys(fn ($case) => [$case->value => $case->label()]))
                    ->required(),
                Forms\Components\Textarea::make('notes')
                    ->rows(2)
                    ->maxLength(65535),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('guest_name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('pax')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('source')
                    ->badge()
                    ->color(fn (AllocationSource $state): string => $state->color()),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Created By')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->using(function (array $data) {
                        $data['activity_session_id'] = $this->ownerRecord->id;
                        try {
                            app(AllocationService::class)->createAllocation($data);
                            Notification::make()->success()->title('Guest allocated successfully')->send();
                        } catch (\RuntimeException $e) {
                            Notification::make()->danger()->title($e->getMessage())->send();

                            return null;
                        }
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->using(function ($record, array $data) {
                        try {
                            app(AllocationService::class)->updateAllocation($record, $data);
                            Notification::make()->success()->title('Allocation updated')->send();
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
            ->bulkActions([]);
    }
}
