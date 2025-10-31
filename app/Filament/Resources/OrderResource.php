<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Models\Order;
use App\Models\Setting;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Forms\Components\DatePicker;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;
use pxlrbt\FilamentExcel\Columns\Column;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    protected static ?int $navigationSort = 1;


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        $currency_symbol = config('settings.currency_symbol');

        return $table
            ->columns([
                TextColumn::make('id')->label('ID')->sortable(),
                // TextColumn::make('customer.first_name')
                //             ->label('Customer Name')
                //             ->searchable()
                //             ->formatStateUsing(fn ($record) => $record->customer->first_name . ' ' . $record->customer->last_name),
                TextColumn::make('vehicle_number')
                            ->label('Vehicle')
                            ->searchable(),
                TextColumn::make('driver_name')
                            ->label('Driver')
                            ->searchable(),
                TextColumn::make('company_name')
                            ->label('Company')
                            ->searchable(),
                TextColumn::make('branch.name')
                            ->label('Branch'),
                BadgeColumn::make('product_type')
                            ->label('Product')
                            ->colors([
                                'success' => 'sweet_water',
                                'warning' => 'salt_water',
                            ])
                            ->formatStateUsing(fn ($state) => $state === 'sweet_water' ? 'Sweet Water' : 'Salt Water'),
                TextColumn::make('tanker_size')
                            ->label('Tanker Size'),
                TextColumn::make('total_price')
                            ->label('Amount')
                            ->formatStateUsing(fn ($record) => $currency_symbol.$record->total_price)
                            ->sortable(),
                BadgeColumn::make('payment_type')
                            ->label('Payment')
                            ->colors([
                                'success' => 'cash',
                                'warning' => 'credit',
                                'info' => 'bank_transfer',
                                'danger' => 'on_account',
                            ])
                            ->formatStateUsing(fn ($state) => ucfirst(str_replace('_', ' ', $state))),
                TextColumn::make('order_date')
                            ->label('Order Date')
                            ->dateTime()
                            ->sortable(),
                TextColumn::make('created_at')->sortable()->dateTime(),
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                SelectFilter::make('branch_id')
                    ->label('Branch')
                    ->relationship('branch', 'name')
                    ->multiple()
                    ->preload()
                    ->searchable()
                    ->indicator('Branches'),
                Filter::make('created_at')
                ->form([
                    DatePicker::make('start_date')
                        ->label('From Date'),
                    DatePicker::make('end_date')
                        ->label('To Date'),
                ])
                ->query(function ($query, array $data) {
                    return $query
                        ->when($data['start_date'] ?? null, fn ($query, $date) => $query->whereDate('created_at', '>=', $date))
                        ->when($data['end_date'] ?? null, fn ($query, $date) => $query->whereDate('created_at', '<=', $date));
                }) 
                ->indicateUsing(function (array $data) {
                    $indicators = [];
        
                    if (!empty($data['start_date'])) {
                        $indicators[] = 'From: ' . $data['start_date'];
                    }
        
                    if (!empty($data['end_date'])) {
                        $indicators[] = 'To: ' . $data['end_date'];
                    }
        
                    return $indicators;
                }),
            ])
            ->actions([
                Tables\Actions\Action::make('download_invoice')
                ->label('Invoice')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->url(fn ($record) => $record->id ? url('/download-invoice/' . $record->id) : null)
                ->openUrlInNewTab()
                ->visible(fn ($record) => $record->id !== null),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    ExportBulkAction::make()->exports([
                        ExcelExport::make()
                            ->fromTable()
                            ->withFilename(fn ($resource) => $resource::getModelLabel() . '-' . date('Y-m-d'))
                            ->withWriterType(\Maatwebsite\Excel\Excel::CSV)
                            ->withColumns([
                                Column::make('customer.phone')->heading('Mobile'),
                                Column::make('customer.email')->heading('Email'),
                                Column::make('customer.address')->heading('Address'),
                                Column::make('updated_at'),
                            ])
                    ])
                ]),
            ]);
    }

    public static function canCreate(): bool
    {
        return false;
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
            'index' => Pages\ListOrders::route('/'),
            // 'create' => Pages\CreateOrder::route('/create'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
