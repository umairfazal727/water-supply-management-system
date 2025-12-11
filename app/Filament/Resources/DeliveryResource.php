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
        $currency_symbol = config('settings.currency_symbol');
        return $form
            ->schema([
                    Forms\Components\Select::make('delivery_customer_id')
                    ->relationship('deliveryCustomer', 'name')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->live()
                    ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                        if ($state) {
                            $customer = \App\Models\DeliveryCustomer::find($state);
                            if ($customer) {
                                $set('customer_site', $customer->address);
                                $set('customer_location', $customer->delivery_location);
                                
                                // If order is already selected, update total_amount based on order's product_type
                                $orderId = $get('order_id');
                                if ($orderId) {
                                    $order = \App\Models\Order::find($orderId);
                                    if ($order) {
                                        if ($order->product_type === 'sweet_water') {
                                            $set('total_amount', $customer->sweet_water_price ?? 0);
                                        } elseif ($order->product_type === 'salt_water') {
                                            $set('total_amount', $customer->salt_water_price ?? 0);
                                        }
                                    }
                                }
                            }
                        }
                    })
                    ->label('Delivery Customer'),
                // Forms\Components\Select::make('order_id')
                //     ->relationship('order', 'id')
                //     ->searchable()
                //     ->preload(),
                Forms\Components\Select::make('order_id')
                        ->relationship('order', 'id')
                        ->searchable()
                        ->preload()
                        ->live()
                        ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                            if ($state) {
                                $order = \App\Models\Order::find($state);
                                if ($order) {
                                    // Set trip_size from order's tanker_size
                                    if ($order->tanker_size) {
                                        $set('trip_size', $order->tanker_size);
                                    }
                                    
                                    // Set total_amount
                                    if ($order->total_amount) {
                                        $set('total_amount', $order->total_amount);
                                    }
                                    
                                    // Auto-fill delivery_date if not set
                                    if (!$get('delivery_date')) {
                                        $set('delivery_date', now());
                                    }
                                    
                                    // Auto-fill delivery_time if not set
                                    if (!$get('delivery_time')) {
                                        $set('delivery_time', now()->format('H:i:s'));
                                    }
                                    
                                    // Auto-fill delivery_number if not set
                                    if (!$get('delivery_number')) {
                                        $set('delivery_number', 'DEL-' . date('Ymd') . '-' . rand(1000, 9999));
                                    }
                                }
                            }
                        })
                    ->label('Order'),
                    
                Forms\Components\TextInput::make('delivery_number')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255)
                    ->default(fn () => 'DEL-' . date('Ymd') . '-' . rand(1000, 9999)),
                Forms\Components\DatePicker::make('delivery_date')
                    ->required()
                    ->default(fn () => now()),
                Forms\Components\TimePicker::make('delivery_time')
                    ->required()
                    ->default(fn () => now()->format('H:i:s'))
                    ->seconds(false),
                Forms\Components\TextInput::make('customer_site')
                    ->maxLength(255),
                Forms\Components\TextInput::make('customer_location')
                    ->maxLength(255),
                Forms\Components\TextInput::make('trip_size')
                    ->required()
                    ->numeric()
                    ->suffix('gallons'),
                Forms\Components\TextInput::make('total_amount')
                    ->required()
                    ->numeric()
                    ->prefix($currency_symbol)
                    ->step(0.01),
                Forms\Components\Select::make('payment_method')
                    ->options([
                        'cash' => 'Cash',
                        'credit' => 'Credit',
                        'bank_transfer' => 'Bank Transfer',
                        'check' => 'Check',
                    ])
                    ->required()
                    ->default('credit'),
                Forms\Components\Select::make('status')
                    ->options([
                        'scheduled' => 'Scheduled',
                        'in_progress' => 'In Progress',
                        'delivered' => 'Delivered',
                        'cancelled' => 'Cancelled',
                    ])
                    ->required()
                    ->default('delivered'),
                Forms\Components\Textarea::make('notes')
                    ->maxLength(65535),
                Forms\Components\FileUpload::make('delivery_photos')
                    ->multiple()
                    ->directory('delivery-photos')
                    ->visibility('private'),
            ]);
    }

    public static function table(Table $table): Table
    {
        $currency_symbol = config('settings.currency_symbol');

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
                Tables\Columns\TextColumn::make('customer_site')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('trip_size')
                    ->numeric()
                    ->suffix(' gal')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_amount')
                    ->money($currency_symbol)
                    ->sortable()
                    ->label('Total Amount'),
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
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
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
                // Tables\Actions\Action::make('download_invoice')
                //     ->label('Invoice')
                //     ->icon('heroicon-o-arrow-down-tray')
                //     ->color('success')
                //     ->url(fn ($record) => $record->order_id ? url('/download-invoice/' . $record->order_id) : null)
                //     ->openUrlInNewTab()
                //     ->visible(fn ($record) => $record->order_id !== null),
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
