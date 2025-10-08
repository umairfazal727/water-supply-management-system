<x-filament-panels::page>
    
    <div x-load-css="[@js(\Filament\Support\Facades\FilamentAsset::getStyleHref('filament-print'))]"
        x-load-js="[@js(\Filament\Support\Facades\FilamentAsset::getScriptSrc('filament-print-js'))]"
    >
        <livewire:order.edit-cart :order-id="$this->record->id" />
    </div> 

    <livewire:error />  

</x-filament-panels::page>
