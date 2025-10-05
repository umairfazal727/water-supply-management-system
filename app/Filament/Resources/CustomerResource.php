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

    protected static ?int $navigationSort = 4;

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Grid::make(2)
                    ->schema([
                        TextInput::make('first_name')
                            ->required()
                            ->maxLength(20),
                        TextInput::make('last_name')
                            ->maxLength(20),
                    ]),
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
                            ->label('Company Name')
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
        return $table
            ->columns([
                TextColumn::make('first_name')->sortable()->searchable(),
                TextColumn::make('last_name')->searchable(),
                TextColumn::make('company_name')->searchable(),
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
                    ->money('USD')
                    ->sortable(),
                TextColumn::make('orders_count')
                    ->label('Orders')
                    ->counts('orders')  
                    ->sortable(),                
                TextColumn::make('created_at')->sortable()->dateTime(),
            ])
            ->filters([])
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
