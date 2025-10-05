<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextInputColumn;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;
use pxlrbt\FilamentExcel\Columns\Column;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                    TextInput::make('name')
                        ->required()
                        ->maxLength(255),
                    TextInput::make('barcode')
                        ->required()
                        ->unique(Product::class, 'barcode', ignoreRecord: true),
                    TextInput::make('price')
                        ->numeric()
                        ->required(),
                    TextInput::make('quantity')
                        ->numeric()
                        ->minValue(0)
                        ->default(1)
                        ->required(),
                    TextInput::make('tax')
                        ->label('Tax (%)')
                        ->suffixIcon('heroicon-o-information-circle')  
                        ->helperText('Example: 5 for 5% VAT/GST.')
                        ->numeric()
                        ->default(0.00),
                    FileUpload::make('image')
                        ->disk('public_uploads') 
                        ->panelLayout('grid') 
                        ->visibility('public'),
                    Toggle::make('status')
                        ->label('Active')
                        ->default(true)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                                ->width(250)
                                ->wrap()
                                ->sortable()
                                ->searchable(),
                ImageColumn::make('image')->disk('public_uploads')  
                                ->size(50)  
                                ->square(),
                TextColumn::make('barcode')->searchable(),
                TextInputColumn::make('quantity')->type('number')  
                                ->sortable() 
                                ->width(10)
                                ->rules(['required', 'integer', 'min:1']),
                TextColumn::make('price')->sortable(),              
                TextColumn::make('created_at')->dateTime()->sortable(),
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
                ExportBulkAction::make()->exports([
                    ExcelExport::make()
                        ->withFilename(fn ($resource) => $resource::getModelLabel() . '-' . date('Y-m-d'))
                        ->withWriterType(\Maatwebsite\Excel\Excel::CSV)
                        ->withColumns([
                            Column::make('name')->heading('Name'),
                            Column::make('barcode')->heading('Barcode'),
                            Column::make('price')->heading('Price'),
                            Column::make('tax')->heading('Tax'),
                            Column::make('quantity')->heading('Quantity'),
                        ])
                ])
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
            'index' => Pages\ListProducts::route('/'),
            // 'create' => Pages\CreateProduct::route('/create'),
            // 'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
