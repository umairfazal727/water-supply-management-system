<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ExpenseResource\Pages;
use App\Filament\Resources\ExpenseResource\RelationManagers;
use App\Models\Expense;
use App\Models\Branch;
use App\Models\ExpenseCategory;
use App\Models\Driver;
use App\Models\Vehicle;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ExpenseResource extends Resource
{
    protected static ?string $model = Expense::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';

    public static function form(Form $form): Form
    {
        $currency_symbol = config('settings.currency_symbol');

        return $form
            ->schema([
                Forms\Components\Select::make('branch_id')
                    ->label('Branch')
                    ->options(Branch::where('is_active', true)->pluck('name', 'id'))
                    ->required()
                    ->searchable()
                    ->preload(),
                Forms\Components\Select::make('expense_category_id')
                    ->label('Category')
                    ->options(ExpenseCategory::where('is_active', true)->pluck('name', 'id'))
                    ->required()
                    ->searchable()
                    ->preload(),
                Forms\Components\Select::make('user_id')
                    ->label('Created By')
                    ->options(User::pluck('name', 'id'))
                    ->required()
                    ->default(auth()->id())
                    ->searchable()
                    ->preload(),
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('description')
                    ->rows(3)
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('amount')
                    ->numeric()
                    ->required()
                    ->prefix($currency_symbol)
                    ->step(0.01),
                Forms\Components\DatePicker::make('expense_date')
                    ->required()
                    ->default(now()),
                Forms\Components\Select::make('payment_method')
                    ->options([
                        'cash' => 'Cash',
                        'credit' => 'Credit',
                        'bank_transfer' => 'Bank Transfer',
                        'check' => 'Check',
                    ])
                    ->required()
                    ->default('cash'),
                Forms\Components\TextInput::make('reference_number')
                    ->maxLength(255),
                Forms\Components\Select::make('driver_id')
                    ->label('Driver')
                    ->options(Driver::where('is_active', true)->pluck('name', 'id'))
                    ->searchable()
                    ->preload(),
                Forms\Components\Select::make('vehicle_id')
                    ->label('Vehicle')
                    ->options(Vehicle::where('is_active', true)->pluck('vehicle_number', 'id'))
                    ->searchable()
                    ->preload(),
                Forms\Components\Toggle::make('is_approved')
                    ->label('Approved')
                    ->default(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        $currency_symbol = config('settings.currency_symbol');
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Category')
                    ->searchable()
                    ->sortable()
                    ->badge(),
                Tables\Columns\TextColumn::make('amount')
                    ->money($currency_symbol)
                    ->sortable(),
                Tables\Columns\TextColumn::make('expense_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('payment_method')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'cash' => 'success',
                        'credit' => 'warning',
                        'bank_transfer' => 'info',
                        'check' => 'gray',
                    }),
                Tables\Columns\TextColumn::make('reference_number')
                    ->label('Reference')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('vehicle.vehicle_number')
                    ->label('Vehicle')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('driver.name')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('branch.name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_approved')
                    ->boolean()
                    ->label('Approved'),
                Tables\Columns\TextColumn::make('user.name')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('branch')
                    ->relationship('branch', 'name'),
                Tables\Filters\SelectFilter::make('category')
                    ->relationship('category', 'name'),
                Tables\Filters\SelectFilter::make('payment_method')
                    ->options([
                        'cash' => 'Cash',
                        'credit' => 'Credit',
                        'bank_transfer' => 'Bank Transfer',
                        'check' => 'Check',
                    ]),
                Tables\Filters\TernaryFilter::make('is_approved')
                    ->label('Approval Status'),
                Tables\Filters\Filter::make('expense_date')
                    ->form([
                        Forms\Components\DatePicker::make('from'),
                        Forms\Components\DatePicker::make('until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('expense_date', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('expense_date', '<=', $date),
                            );
                    }),
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
    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListExpenses::route('/'),
            'create' => Pages\CreateExpense::route('/create'),
            'edit' => Pages\EditExpense::route('/{record}/edit'),
        ];
    }
}
