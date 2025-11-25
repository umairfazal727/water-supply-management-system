<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransportEmployeeResource\Pages;
use App\Models\TransportEmployee;
use App\Models\Branch;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Enums\FontWeight;

class TransportEmployeeResource extends Resource
{
    protected static ?string $model = TransportEmployee::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';
    protected static ?string $navigationGroup = 'Water Transport';
    
    protected static ?int $navigationSort = 6;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Basic Details')
                    ->schema([
                        Section::make('Personal Information')
                            ->schema([
                                TextInput::make('employee_number')
                                    ->label('Employee Number')
                                    ->disabled()
                                    ->dehydrated()
                                    ->default(fn () => TransportEmployee::generateEmployeeNumber()),
                                TextInput::make('first_name')
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('last_name')
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('email')
                                    ->email()
                                    ->nullable(),
                                TextInput::make('phone')
                                    ->tel()
                                    ->required(),
                                TextInput::make('alternate_phone')
                                    ->tel()
                                    ->nullable(),
                                Textarea::make('address')
                                    ->rows(3)
                                    ->nullable(),
                                DatePicker::make('date_of_birth')
                                    ->nullable(),
                                Select::make('gender')
                                    ->options([
                                        'male' => 'Male',
                                        'female' => 'Female',
                                        'other' => 'Other',
                                    ])
                                    ->nullable(),
                            ])->columns(2),
                        
                        Section::make('Emergency Contacts')
                            ->schema([
                                TextInput::make('emergency_contact_name')
                                    ->nullable(),
                                TextInput::make('emergency_contact_phone')
                                    ->tel()
                                    ->nullable(),
                                TextInput::make('emergency_contact_relationship')
                                    ->label('Relationship')
                                    ->nullable(),
                            ])->columns(3),
                    ]),
                
                Section::make('Employment Details')
                    ->schema([
                        Section::make('Job Information')
                            ->schema([
                                TextInput::make('job_title')
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('designation')
                                    ->nullable()
                                    ->maxLength(255),
                                DatePicker::make('date_of_joining')
                                    ->required()
                                    ->default(now()),
                                Select::make('employment_type')
                                    ->options([
                                        'full-time' => 'Full-Time',
                                        'part-time' => 'Part-Time',
                                        'contract' => 'Contract',
                                    ])
                                    ->required()
                                    ->default('full-time'),
                                Select::make('branch_id')
                                    ->label('Branch')
                                    ->relationship('branch', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->nullable(),
                                TextInput::make('department')
                                    ->nullable()
                                    ->maxLength(255),
                                Textarea::make('job_description')
                                    ->rows(3)
                                    ->nullable(),
                            ])->columns(2),
                        
                        Section::make('Transport Details')
                            ->schema([
                                Select::make('vehicle_id')
                                    ->label('Vehicle')
                                    ->relationship('vehicle', 'vehicle_number')
                                    ->searchable()
                                    ->preload()
                                    ->nullable(),
                                TextInput::make('license_number')
                                    ->label('License Number')
                                    ->nullable()
                                    ->maxLength(255),
                                DatePicker::make('license_expiry_date')
                                    ->label('License Expiry Date')
                                    ->nullable(),
                            ])->columns(3),
                    ]),
                
                Section::make('Documents')
                    ->schema([
                        Section::make('Document Upload')
                            ->schema([
                                FileUpload::make('id_document_path')
                                    ->label('ID Document')
                                    ->directory('transport-employee-documents')
                                    ->acceptedFileTypes(['application/pdf', 'image/*'])
                                    ->maxSize(5120)
                                    ->nullable(),
                                TextInput::make('id_document_number')
                                    ->label('ID Document Number')
                                    ->nullable()
                                    ->maxLength(255),
                            ])->columns(2),
                    ]),
                
                Section::make('Payroll & Salary')
                    ->schema([
                        Section::make('Salary Information')
                            ->schema([
                                TextInput::make('monthly_salary')
                                    ->label('Monthly Salary')
                                    ->numeric()
                                    ->prefix(config('settings.currency_symbol', 'AED'))
                                    ->default(0)
                                    ->required(),
                                TextInput::make('hourly_rate')
                                    ->label('Hourly Rate')
                                    ->numeric()
                                    ->prefix(config('settings.currency_symbol', 'AED'))
                                    ->nullable(),
                            ])->columns(2),
                        
                        Section::make('Bank Details')
                            ->schema([
                                TextInput::make('bank_name')
                                    ->nullable()
                                    ->maxLength(255),
                                TextInput::make('bank_account_number')
                                    ->label('Account Number')
                                    ->nullable()
                                    ->maxLength(255),
                                TextInput::make('iban')
                                    ->label('IBAN')
                                    ->nullable()
                                    ->maxLength(255),
                            ])->columns(3),
                        
                        Section::make('Balance Information')
                            ->schema([
                                TextInput::make('current_balance')
                                    ->label('Current Balance')
                                    ->numeric()
                                    ->prefix(config('settings.currency_symbol', 'AED'))
                                    ->disabled()
                                    ->dehydrated()
                                    ->default(0),
                                TextInput::make('total_advance_taken')
                                    ->label('Total Advance Taken')
                                    ->numeric()
                                    ->prefix(config('settings.currency_symbol', 'AED'))
                                    ->disabled()
                                    ->dehydrated()
                                    ->default(0),
                                TextInput::make('total_salary_paid')
                                    ->label('Total Salary Paid')
                                    ->numeric()
                                    ->prefix(config('settings.currency_symbol', 'AED'))
                                    ->disabled()
                                    ->dehydrated()
                                    ->default(0),
                            ])->columns(3),
                    ]),
                
                Section::make('Status & Notes')
                    ->schema([
                        Section::make('Status')
                            ->schema([
                                Toggle::make('is_active')
                                    ->label('Active')
                                    ->default(true),
                                DatePicker::make('date_of_leaving')
                                    ->nullable(),
                                Textarea::make('notes')
                                    ->rows(3)
                                    ->nullable(),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        $currency_symbol = config('settings.currency_symbol', 'AED');
        
        return $table
            ->columns([
                TextColumn::make('employee_number')
                    ->label('Emp. No.')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('full_name')
                    ->label('Name')
                    ->getStateUsing(fn ($record) => $record->first_name . ' ' . $record->last_name)
                    ->searchable(['first_name', 'last_name'])
                    ->sortable(),
                TextColumn::make('phone')
                    ->searchable(),
                TextColumn::make('job_title')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('vehicle.vehicle_number')
                    ->label('Vehicle')
                    ->sortable(),
                TextColumn::make('branch.name')
                    ->label('Branch')
                    ->sortable(),
                BadgeColumn::make('employment_type')
                    ->label('Type')
                    ->colors([
                        'success' => 'full-time',
                        'warning' => 'part-time',
                        'info' => 'contract',
                    ]),
                TextColumn::make('monthly_salary')
                    ->label('Salary')
                    ->money($currency_symbol)
                    ->sortable(),
                TextColumn::make('current_balance')
                    ->label('Balance')
                    ->money($currency_symbol)
                    ->color(fn ($state) => $state < 0 ? 'danger' : 'success')
                    ->weight(FontWeight::Bold)
                    ->sortable(),
                IconColumn::make('is_active')
                    ->boolean()
                    ->label('Active'),
                TextColumn::make('date_of_joining')
                    ->date()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('branch_id')
                    ->label('Branch')
                    ->relationship('branch', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('employment_type')
                    ->options([
                        'full-time' => 'Full-Time',
                        'part-time' => 'Part-Time',
                        'contract' => 'Contract',
                    ]),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active')
                    ->placeholder('All')
                    ->trueLabel('Active only')
                    ->falseLabel('Inactive only'),
            ])
            ->actions([
                Action::make('pay_salary')
                    ->label('Pay Salary')
                    ->icon('heroicon-o-banknotes')
                    ->color('success')
                    ->form([
                        TextInput::make('amount')
                            ->label('Amount')
                            ->numeric()
                            ->required()
                            ->prefix(config('settings.currency_symbol', 'AED')),
                        DatePicker::make('transaction_date')
                            ->label('Payment Date')
                            ->default(now())
                            ->required(),
                        Select::make('payment_method')
                            ->options([
                                'cash' => 'Cash',
                                'bank_transfer' => 'Bank Transfer',
                                'check' => 'Check',
                            ])
                            ->default('cash')
                            ->required(),
                        TextInput::make('reference_number')
                            ->label('Reference Number')
                            ->nullable(),
                        TextInput::make('month')
                            ->label('Month (YYYY-MM)')
                            ->placeholder('2024-11')
                            ->nullable(),
                        TextInput::make('year')
                            ->label('Year')
                            ->numeric()
                            ->default(now()->year)
                            ->nullable(),
                        Textarea::make('description')
                            ->label('Description')
                            ->nullable(),
                        Textarea::make('notes')
                            ->nullable(),
                    ])
                    ->action(function (TransportEmployee $record, array $data) {
                        $record->addSalaryPayment($data['amount'], $data);
                        Notification::make()
                            ->title('Salary Payment Recorded')
                            ->success()
                            ->send();
                    }),
                Action::make('give_advance')
                    ->label('Give Advance')
                    ->icon('heroicon-o-arrow-down-circle')
                    ->color('warning')
                    ->form([
                        TextInput::make('amount')
                            ->label('Advance Amount')
                            ->numeric()
                            ->required()
                            ->prefix(config('settings.currency_symbol', 'AED')),
                        DatePicker::make('transaction_date')
                            ->label('Date')
                            ->default(now())
                            ->required(),
                        Select::make('payment_method')
                            ->options([
                                'cash' => 'Cash',
                                'bank_transfer' => 'Bank Transfer',
                                'check' => 'Check',
                            ])
                            ->default('cash')
                            ->required(),
                        TextInput::make('reference_number')
                            ->label('Reference Number')
                            ->nullable(),
                        Textarea::make('description')
                            ->label('Description')
                            ->default('Advance payment')
                            ->nullable(),
                        Textarea::make('notes')
                            ->nullable(),
                    ])
                    ->action(function (TransportEmployee $record, array $data) {
                        $record->addAdvance($data['amount'], $data);
                        Notification::make()
                            ->title('Advance Payment Recorded')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('id', 'desc');
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
            'index' => Pages\ListTransportEmployees::route('/'),
            'create' => Pages\CreateTransportEmployee::route('/create'),
            'edit' => Pages\EditTransportEmployee::route('/{record}/edit'),
        ];
    }
}
