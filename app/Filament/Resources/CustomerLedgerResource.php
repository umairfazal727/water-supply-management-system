<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CustomerLedgerResource\Pages;
use App\Models\Ledger;
use App\Models\Customer;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\Section;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkAction;
use Filament\Support\Enums\FontWeight;

class CustomerLedgerResource extends Resource
{
    protected static ?string $model = Ledger::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Customer Ledger';

    protected static ?string $modelLabel = 'Ledger Entry';

    protected static ?string $pluralModelLabel = 'Customer Ledger';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Ledger Entry Details')
                    ->schema([
                        Select::make('customer_id')
                            ->label('Customer')
                            ->options(function () {
                                return Customer::with(['driver', 'vehicle'])
                                    ->get()
                                    ->mapWithKeys(function ($customer) {
                                        $label = ($customer->company_name ?: 'N/A') . ' - ' . 
                                                ($customer->driver?->name ?: 'N/A') . ' - ' . 
                                                ($customer->vehicle?->vehicle_number ?: 'N/A');
                                        return [$customer->id => $label];
                                    });
                            })
                            ->required()
                            ->searchable()
                            ->preload(),
                        
                        TextInput::make('entry_number')
                            ->label('Entry Number')
                            ->default(Ledger::getNextEntryNumber())
                            ->required()
                            ->disabled(),
                        
                        DatePicker::make('transaction_date')
                            ->label('Transaction Date')
                            ->required()
                            ->default(now()),
                        
                        TextInput::make('entry_origin')
                            ->label('Entry Origin (JV Number)')
                            ->placeholder('e.g., JV: 3888'),
                        
                        Select::make('transaction_type')
                            ->label('Transaction Type')
                            ->options([
                                'order' => 'Order',
                                'payment' => 'Payment',
                                'opening_balance' => 'Opening Balance',
                                'manual' => 'Manual Entry'
                            ])
                            ->required()
                            ->default('manual'),
                    ])->columns(2),
                
                Section::make('Transaction Amounts')
                    ->schema([
                        TextInput::make('debit_amount')
                            ->label('Debit Amount')
                            ->numeric()
                            ->default(0)
                            ->step(0.01),
                        
                        TextInput::make('credit_amount')
                            ->label('Credit Amount')
                            ->numeric()
                            ->default(0)
                            ->step(0.01),
                        
                        TextInput::make('balance')
                            ->label('Balance After Transaction')
                            ->numeric()
                            ->disabled()
                            ->dehydrated(false),
                    ])->columns(3),
                
                Section::make('Description')
                    ->schema([
                        Textarea::make('description')
                            ->label('Description')
                            ->required()
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('entry_number')
                    ->label('Entry No')
                    ->sortable()
                    ->searchable(),
                
                TextColumn::make('transaction_date')
                    ->label('Date')
                    ->date('d-m-Y')
                    ->sortable(),
                
                TextColumn::make('entry_origin')
                    ->label('Entry Origin')
                    ->searchable(),
                
                TextColumn::make('customer.company_name')
                    ->label('Customer')
                    ->formatStateUsing(function ($record) {
                        $customer = $record->customer;
                        if ($customer) {
                            return ($customer->company_name ?: 'N/A') . ' - ' . 
                                   ($customer->driver?->name ?: 'N/A') . ' - ' . 
                                   ($customer->vehicle?->vehicle_number ?: 'N/A');
                        }
                        return 'N/A';
                    })
                    ->searchable()
                    ->sortable(),
                
                TextColumn::make('debit_amount')
                    ->label('Debit')
                    ->money('AED')
                    ->alignRight()
                    ->color('success'),
                
                TextColumn::make('credit_amount')
                    ->label('Credit')
                    ->money('AED')
                    ->alignRight()
                    ->color('warning'),
                
                TextColumn::make('balance')
                    ->label('Balance')
                    ->money('AED')
                    ->alignRight()
                    ->color(fn ($state) => $state < 0 ? 'danger' : 'success')
                    ->weight(FontWeight::Bold),
                
                TextColumn::make('description')
                    ->label('Description')
                    ->limit(50)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 50) {
                            return null;
                        }
                        return $state;
                    }),
                
                TextColumn::make('transaction_type')
                    ->label('Type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'order' => 'warning',
                        'payment' => 'success',
                        'opening_balance' => 'info',
                        'manual' => 'gray',
                    }),
            ])
            ->filters([
                SelectFilter::make('customer_id')
                    ->label('Customer')
                    ->options(function () {
                        return Customer::with(['driver', 'vehicle'])
                            ->get()
                            ->mapWithKeys(function ($customer) {
                                $label = ($customer->company_name ?: 'N/A') . ' - ' . 
                                        ($customer->driver?->name ?: 'N/A') . ' - ' . 
                                        ($customer->vehicle?->vehicle_number ?: 'N/A');
                                return [$customer->id => $label];
                            });
                    })
                    ->searchable(),
                
                SelectFilter::make('transaction_type')
                    ->label('Transaction Type')
                    ->options([
                        'order' => 'Order',
                        'payment' => 'Payment',
                        'opening_balance' => 'Opening Balance',
                        'manual' => 'Manual Entry'
                    ]),
                
                Filter::make('transaction_date')
                    ->form([
                        DatePicker::make('from')
                            ->label('From Date'),
                        DatePicker::make('until')
                            ->label('To Date'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('transaction_date', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('transaction_date', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Action::make('download_ledger')
                    ->label('Download PDF')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('primary')
                    ->url(fn ($record) => route('download-ledger', ['customer_id' => $record->customer_id, 'from' => $record->transaction_date, 'to' => $record->transaction_date]))
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('order_id', 'desc')
            ->poll('30s');
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
            'index' => Pages\ListCustomerLedgers::route('/'),
            'create' => Pages\CreateCustomerLedger::route('/create'),
            'edit' => Pages\EditCustomerLedger::route('/{record}/edit'),
        ];
    }
}
