<?php 

namespace App\Filament\Resources;

use App\Filament\Resources\CustomerResource\Pages;
use App\Models\Customer;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Grid;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\BadgeColumn;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\CustomerResource\RelationManagers\OrdersRelationManager;
use App\Models\Vehicle;
use App\Models\Driver;


class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?int $navigationSort = 6;

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                // Grid::make(2)
                //     ->schema([
                //         TextInput::make('first_name')
                //             ->required()
                //             ->maxLength(20),
                //         TextInput::make('last_name')
                //             ->maxLength(20),
                //     ]),
                Grid::make(2)
                    ->schema([
                        TextInput::make('email')
                            ->email()
                            ->nullable(),
                        TextInput::make('phone')
                            ->tel()
                            ->nullable(),
                    ]),
                Textarea::make('address')
                    ->nullable(),
                Grid::make(2)
                    ->schema([
                        Select::make('vehicle_id')
                            ->label('Vehicle')
                            ->options(\App\Models\Vehicle::where('is_active', true)->pluck('vehicle_number', 'id'))
                            ->nullable()
                            ->searchable()
                            ->preload(),
                        Select::make('driver_id')
                            ->label('Driver')
                            ->options(\App\Models\Driver::where('is_active', true)->pluck('name', 'id'))
                            ->nullable()
                            ->searchable()
                            ->preload(),
                    ]),
                Grid::make(2)
                    ->schema([
                        TextInput::make('company_name')
                            ->label('Transport Name')
                            ->nullable(),
                        TextInput::make('tanker_size')
                            ->label('Tanker Size')
                            ->nullable(),
                    ]),
                Grid::make(2)
                    ->schema([
                        Select::make('product_type')
                            ->label('Product Type')
                            ->options([
                                'sweet_water' => 'Sweet Water',
                                'salt_water' => 'Salt Water',
                            ])
                            ->default('sweet_water')
                            ->required(),
                        TextInput::make('price')
                            ->label('Price')
                            ->numeric()
                            ->default(0)
                            ->required(),
                    ]),
                Grid::make(1)
                    ->schema([
                        TextInput::make('opening_balance')
                            ->label('Opening Balance')
                            ->numeric()
                            ->default(0)
                            ->step(0.01)
                            ->helperText('Positive for advance payment, negative for outstanding balance')
                            ->nullable(),
                    ]),
                FileUpload::make('avatar')
                    ->image()
                    ->visibility('public')
                    ->disk('public_uploads')
                    ->directory('avatars')
                    ->nullable()
            ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        $currency_symbol = config('settings.currency_symbol');

        return $table
            ->columns([
                // TextColumn::make('first_name')->sortable()->searchable(),
                // TextColumn::make('last_name')->searchable(),
                TextColumn::make('company_name')->label('Transport Name')->searchable(),
                TextColumn::make('vehicle.vehicle_number')
                    ->label('Vehicle')
                    ->searchable(),
                TextColumn::make('driver.name')
                    ->label('Driver')
                    ->searchable(),
                BadgeColumn::make('product_type')
                    ->label('Product')
                    ->colors([
                        'success' => 'sweet_water',
                        'warning' => 'salt_water',
                    ]),
                TextColumn::make('price')
                    ->money($currency_symbol)
                    ->sortable(),
                TextColumn::make('opening_balance')
                    ->label('Opening Balance')
                    ->money($currency_symbol)
                    ->sortable()
                    ->color(fn ($state) => $state > 0 ? 'success' : ($state < 0 ? 'danger' : 'gray')),
                TextColumn::make('current_balance')
                    ->label('Current Balance')
                    ->getStateUsing(fn ($record) => $record->getCurrentBalance())
                    ->money($currency_symbol)
                    ->color(fn ($state) => $state > 0 ? 'success' : ($state < 0 ? 'danger' : 'gray'))
                    ->sortable(false),
                TextColumn::make('orders_count')
                    ->label('Orders')
                    ->counts('orders')  
                    ->sortable(),                
                TextColumn::make('created_at')->sortable()->dateTime(),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\Action::make('view_ledger')
                    ->label('View Ledger')
                    ->icon('heroicon-o-document-text')
                    ->color('info')
                    ->url(function ($record) {
                        $fromDate = now()->startOfMonth()->format('Y-m-d');
                        $toDate = now()->format('Y-m-d');
                        return \App\Filament\Pages\CustomerLedgerView::getUrl() . '?customer_id=' . $record->id . '&from_date=' . $fromDate . '&to_date=' . $toDate;
                    })
                    ->openUrlInNewTab(false),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('add_payment')
                    ->label('Add Payment')
                    ->icon('heroicon-o-plus-circle')
                    ->color('success')
                    ->form([
                        TextInput::make('amount')
                            ->label('Payment Amount')
                            ->numeric()
                            ->required()
                            ->step(0.01),
                        Forms\Components\DatePicker::make('payment_date')
                            ->label('Payment Date')
                            ->default(now())
                            ->required(),
                        Textarea::make('description')
                            ->label('Description')
                            ->default('Payment received')
                            ->required(),
                    ])
                    ->action(function ($record, array $data) {
                        \App\Models\Ledger::createEntry([
                            'customer_id' => $record->id,
                            'transaction_date' => $data['payment_date'],
                            'entry_origin' => 'PAY-' . now()->format('Ymd'),
                            'debit_amount' => $data['amount'],
                            'credit_amount' => 0,
                            'description' => $data['description'],
                            'transaction_type' => 'payment'
                        ]);
                    })
                    ->requiresConfirmation(),
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
            OrdersRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCustomers::route('/'),
            // 'create' => Pages\CreateCustomer::route('/create'),
            'edit' => Pages\EditCustomer::route('/{record}/edit'),
        ];
    }
}
