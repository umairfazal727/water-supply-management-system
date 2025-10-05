<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Actions\Action;

class Pos extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-plus-circle';

    protected static string $view = 'filament.pages.pos';

    protected static ?string $navigationLabel = 'Point Of Sale';


    public function getTitle(): string
    {
        return '';  
    }
   

}
