<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DeliveryResource\Pages;
use App\Filament\Resources\DeliveryResource\RelationManagers;
use App\Models\Delivery;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DeliveryResource extends Resource
{
    protected static ?string $model = Delivery::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';
    
    protected static ?string $navigationGroup = 'Water Transport';
    
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('branch_id')
                    ->relationship('branch', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),
                Forms\Components\Select::make('delivery_customer_id')
                    ->relationship('deliveryCustomer', 'name')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->label('Delivery Customer'),
                Forms\Components\Select::make('order_id')
                    ->relationship('order', 'id')
                    ->searchable()
                    ->preload(),
                Forms\Components\TextInput::make('delivery_number')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255)
                    ->default('DEL-' . date('Ymd') . '-' . rand(1000, 9999)),
                Forms\Components\DatePicker::make('delivery_date')
                    ->required()
                    ->default(now()),
                Forms\Components\TimePicker::make('delivery_time')
                    ->required()
                    ->default(now()),
                Forms\Components\TextInput::make('customer_site')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('customer_location')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('trip_size')
                    ->required()
                    ->numeric()
                    ->suffix('gallons/liters'),
                Forms\Components\TextInput::make('rate_per_gallon')
                    ->required()
                    ->numeric()
                    ->prefix('SAR')
                    ->step(0.01),
                Forms\Components\TextInput::make('total_amount')
                    ->required()
                    ->numeric()
                    ->prefix('SAR')
                    ->step(0.01),
                Forms\Components\Select::make('payment_method')
                    ->options([
                        'cash' => 'Cash',
                        'credit' => 'Credit',
                        'bank_transfer' => 'Bank Transfer',
                        'check' => 'Check',
                    ])
                    ->required()
                    ->default('cash'),
                Forms\Components\Select::make('status')
                    ->options([
                        'scheduled' => 'Scheduled',
                        'in_progress' => 'In Progress',
                        'delivered' => 'Delivered',
                        'cancelled' => 'Cancelled',
                    ])
                    ->required()
                    ->default('scheduled'),
                Forms\Components\Textarea::make('notes')
                    ->maxLength(65535),
                Forms\Components\FileUpload::make('delivery_photos')
                    ->multiple()
                    ->directory('delivery-photos')
                    ->visibility('private'),
                Forms\Components\DateTimePicker::make('delivered_at')
                    ->label('Delivered At'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('delivery_number')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('deliveryCustomer.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('delivery_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('delivery_time')
                    ->time()
                    ->sortable(),
                Tables\Columns\TextColumn::make('customer_site')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('trip_size')
                    ->numeric()
                    ->suffix(' gal')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_amount')
                    ->money('SAR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'scheduled' => 'info',
                        'in_progress' => 'warning',
                        'delivered' => 'success',
                        'cancelled' => 'danger',
                    }),
                Tables\Columns\TextColumn::make('payment_method')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'cash' => 'success',
                        'credit' => 'warning',
                        'bank_transfer' => 'info',
                        'check' => 'gray',
                    }),
                Tables\Columns\TextColumn::make('branch.name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('delivered_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('branch')
                    ->relationship('branch', 'name'),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'scheduled' => 'Scheduled',
                        'in_progress' => 'In Progress',
                        'delivered' => 'Delivered',
                        'cancelled' => 'Cancelled',
                    ]),
              
                Tables\Filters\Filter::make('delivery_date')
                    ->form([
                        Forms\Components\DatePicker::make('from'),
                        Forms\Components\DatePicker::make('until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('delivery_date', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('delivery_date', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('download_invoice')
                    ->label('Invoice')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->url(fn ($record) => $record->order_id ? url('/download-invoice/' . $record->order_id) : null)
                    ->openUrlInNewTab()
                    ->visible(fn ($record) => $record->order_id !== null),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDeliveries::route('/'),
            'create' => Pages\CreateDelivery::route('/create'),
            'edit' => Pages\EditDelivery::route('/{record}/edit'),
        ];
    }
}
