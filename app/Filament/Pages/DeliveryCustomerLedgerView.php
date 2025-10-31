<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\DeliveryCustomer;
use App\Models\DeliveryLedger;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Filament\Support\Enums\MaxWidth;

class DeliveryCustomerLedgerView extends Page implements HasForms, HasActions
{
    use InteractsWithForms, InteractsWithActions;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Delivery Customer Ledger';
    protected static string $view = 'filament.pages.delivery-customer-ledger-view';
    protected static ?string $title = 'Delivery Customer Ledger';
    protected static ?string $navigationGroup = 'Water Transport';
    protected static ?int $navigationSort = 13;

    public Collection $ledgerEntries;
    public ?DeliveryCustomer $deliveryCustomer = null;
    public float $previousBalance = 0;
    public float $totalDebit = 0;
    public float $totalCredit = 0;
    public float $finalBalance = 0;
    public bool $isLoading = false;
    
    public ?array $data = [];

    public function mount(): void
    {
        $this->ledgerEntries = collect([]);
        $this->form->fill([
            'from_date' => now()->startOfMonth()->format('Y-m-d'),
            'to_date' => now()->format('Y-m-d'),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Filter Ledger')
                    ->schema([
                        Select::make('delivery_customer_id')
                            ->label('Select Delivery Customer')
                            ->options(function () {
                                return DeliveryCustomer::get()
                                    ->mapWithKeys(function ($deliveryCustomer) {
                                        $label = ($deliveryCustomer->company_name ?: $deliveryCustomer->name) . 
                                                ($deliveryCustomer->name != $deliveryCustomer->company_name ? ' - ' . $deliveryCustomer->name : '');
                                        return [$deliveryCustomer->id => $label];
                                    });
                            })
                            ->searchable()
                            ->required()
                            ->placeholder('Select a delivery customer')
                            ->live()
                            ->afterStateUpdated(function () {
                                $this->refreshLedgerData();
                            }),
                        
                        DatePicker::make('from_date')
                            ->label('From Date')
                            ->default(now()->startOfMonth())
                            ->live()
                            ->afterStateUpdated(function () {
                                $this->refreshLedgerData();
                            }),
                        
                        DatePicker::make('to_date')
                            ->label('To Date')
                            ->default(now())
                            ->live()
                            ->afterStateUpdated(function () {
                                $this->refreshLedgerData();
                            }),
                    ])->columns(3),
            ])
            ->statePath('data');
    }

    public function loadLedgerData(): void
    {
        $data = $this->form->getState();
        $delivery_customer_id = $data['delivery_customer_id'] ?? null;
        $from_date = $data['from_date'] ?? null;
        $to_date = $data['to_date'] ?? null;

        if (!$delivery_customer_id) {
            $this->ledgerEntries = collect([]);
            $this->deliveryCustomer = null;
            $this->totalDebit = 0;
            $this->totalCredit = 0;
            $this->previousBalance = 0;
            $this->finalBalance = 0;
            return;
        }

        $this->deliveryCustomer = DeliveryCustomer::find($delivery_customer_id);
        
        $query = DeliveryLedger::where('delivery_customer_id', $delivery_customer_id)
                      ->with(['delivery', 'deliveryCustomer'])
                      ->orderBy('transaction_date', 'asc')
                      ->orderBy('id', 'asc');

        if ($from_date) {
            $query->whereDate('transaction_date', '>=', $from_date);
        }

        if ($to_date) {
            $query->whereDate('transaction_date', '<=', $to_date);
        }

        $this->ledgerEntries = $query->get();

        // Calculate totals
        $this->totalDebit = $this->ledgerEntries->sum('debit_amount');
        $this->totalCredit = $this->ledgerEntries->sum('credit_amount');

        // Get previous balance (balance before the filtered period)
        if ($from_date) {
            $previousEntry = DeliveryLedger::where('delivery_customer_id', $delivery_customer_id)
                                 ->whereDate('transaction_date', '<', $from_date)
                                 ->orderBy('transaction_date', 'desc')
                                 ->orderBy('id', 'desc')
                                 ->first();
            $this->previousBalance = $previousEntry ? $previousEntry->balance : (float) ($this->deliveryCustomer->opening_balance ?? 0);
        } else {
            $this->previousBalance = (float) ($this->deliveryCustomer->opening_balance ?? 0);
        }

        $this->finalBalance = $this->previousBalance + $this->totalDebit - $this->totalCredit;
    }

    public function refreshLedgerData(): void
    {
        $this->isLoading = true;
        $this->loadLedgerData();
        $this->isLoading = false;
    }

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function getMaxContentWidth(): MaxWidth
    {
        return MaxWidth::Full;
    }
}

