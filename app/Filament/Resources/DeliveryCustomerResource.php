<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DeliveryCustomerResource\Pages;
use App\Filament\Resources\DeliveryCustomerResource\RelationManagers;
use App\Models\DeliveryCustomer;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DeliveryCustomerResource extends Resource
{
    protected static ?string $model = DeliveryCustomer::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Water Transport';
    
    protected static ?int $navigationSort = 2;
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Customer Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('company_name')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('contact_person')
                            ->maxLength(255),
                    ])->columns(3),
                
                Forms\Components\Section::make('Contact Details')
                    ->schema([
                        Forms\Components\TextInput::make('phone')
                            ->tel()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->maxLength(255),
                        Forms\Components\Textarea::make('address')
                            ->rows(3)
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('delivery_location')
                            ->maxLength(255),
                    ])->columns(2),
                
                Forms\Components\Section::make('Financial Information')
                    ->schema([
                        Forms\Components\TextInput::make('opening_balance')
                            ->numeric()
                            ->default(0.00)
                            ->prefix('AED')
                            ->step(0.01),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),
                    ])->columns(2),
                
                Forms\Components\Section::make('Pricing Information')
                    ->schema([
                        Forms\Components\TextInput::make('rate')
                            ->numeric()
                            ->default(0.00)
                            ->prefix('AED')
                            ->step(0.01)
                            ->label('Rate'),
                        Forms\Components\TextInput::make('sweet_water_price')
                            ->numeric()
                            ->default(0.00)
                            ->prefix('AED')
                            ->step(0.01)
                            ->label('Sweet Water Price'),
                        Forms\Components\TextInput::make('salt_water_price')
                            ->numeric()
                            ->default(0.00)
                            ->prefix('AED')
                            ->step(0.01)
                            ->label('Salt Water Price'),
                        Forms\Components\TextInput::make('drinking_water_price')
                            ->numeric()
                            ->default(0.00)
                            ->prefix('AED')
                            ->step(0.01)
                            ->label('Drinking Water Price'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        $currency_symbol = config('settings.currency_symbol');

        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('company_name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('phone')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('delivery_location')
                    ->searchable(),
                Tables\Columns\TextColumn::make('opening_balance')
                    ->money($currency_symbol)
                    ->sortable(),
                Tables\Columns\TextColumn::make('rate')
                    ->money($currency_symbol)
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('sweet_water_price')
                    ->money($currency_symbol)
                    ->sortable()
                    ->toggleable()
                    ->label('Sweet Water'),
                Tables\Columns\TextColumn::make('salt_water_price')
                    ->money($currency_symbol)
                    ->sortable()
                    ->toggleable()
                    ->label('Salt Water'),
                Tables\Columns\TextColumn::make('drinking_water_price')
                    ->money($currency_symbol)
                    ->sortable()
                    ->toggleable()
                    ->label('Drinking Water'),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListDeliveryCustomers::route('/'),
            'create' => Pages\CreateDeliveryCustomer::route('/create'),
            'edit' => Pages\EditDeliveryCustomer::route('/{record}/edit'),
        ];
    }
}
