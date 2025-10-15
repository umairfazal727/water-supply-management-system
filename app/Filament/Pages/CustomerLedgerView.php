<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\Customer;
use App\Models\Ledger;
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

class CustomerLedgerView extends Page implements HasForms, HasActions
{
    use InteractsWithForms, InteractsWithActions;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Customer Ledger';
    protected static string $view = 'filament.pages.customer-ledger-view';
    protected static ?string $title = 'Customer Ledger';
    protected static ?int $navigationSort = 3;

    public Collection $ledgerEntries;
    public ?Customer $customer = null;
    public float $previousBalance = 0;
    public float $totalDebit = 0;
    public float $totalCredit = 0;
    public float $finalBalance = 0;
    public bool $isLoading = false;

    public function mount(): void
    {
        $this->ledgerEntries = collect([]);
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Filter Ledger')
                    ->schema([
                        Select::make('customer_id')
                            ->label('Select Customer')
                            ->options(Customer::all()->pluck('first_name', 'id'))
                            ->searchable()
                            ->live()
                            ->loadingMessage('Loading customer data...')
                            ->afterStateUpdated(function () {
                                $this->isLoading = true;
                                $this->loadLedgerData();
                                $this->isLoading = false;
                            }),
                        
                        DatePicker::make('from_date')
                            ->label('From Date')
                            ->live()
                            ->afterStateUpdated(function () {
                                $this->isLoading = true;
                                $this->loadLedgerData();
                                $this->isLoading = false;
                            }),
                        
                        DatePicker::make('to_date')
                            ->label('To Date')
                            ->live()
                            ->afterStateUpdated(function () {
                                $this->isLoading = true;
                                $this->loadLedgerData();
                                $this->isLoading = false;
                            }),
                    ])->columns(3),
            ])
            ->statePath('data');
    }

    public function loadLedgerData(): void
    {
        $data = $this->form->getState();
        $customer_id = $data['customer_id'] ?? null;
        $from_date = $data['from_date'] ?? null;
        $to_date = $data['to_date'] ?? null;

        if (!$customer_id) {
            $this->ledgerEntries = collect([]);
            $this->customer = null;
            $this->totalDebit = 0;
            $this->totalCredit = 0;
            $this->previousBalance = 0;
            $this->finalBalance = 0;
            return;
        }

        $this->customer = Customer::find($customer_id);
        
        $query = Ledger::where('customer_id', $customer_id)
                      ->with('order')
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
            $previousEntry = Ledger::where('customer_id', $customer_id)
                                 ->whereDate('transaction_date', '<', $from_date)
                                 ->orderBy('transaction_date', 'desc')
                                 ->orderBy('id', 'desc')
                                 ->first();
            $this->previousBalance = $previousEntry ? $previousEntry->balance : $this->customer->opening_balance;
        } else {
            $this->previousBalance = $this->customer->opening_balance;
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
        return [
            Action::make('load_ledger')
                ->label('Load Ledger Data')
                ->icon('heroicon-o-magnifying-glass')
                ->color('primary')
                ->action('refreshLedgerData')
                ->visible(function () {
                    try {
                        $state = $this->form->getState();
                        return !empty($state['customer_id']);
                    } catch (\Exception $e) {
                        return false;
                    }
                }),
            
            Action::make('download_ledger')
                ->label('Download PDF')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->visible(fn () => $this->customer && $this->ledgerEntries->count() > 0)
                ->url(function () {
                    $data = $this->form->getState();
                    return route('download-ledger', [
                        'customer_id' => $data['customer_id'],
                        'from' => $data['from_date'],
                        'to' => $data['to_date']
                    ]);
                })
                ->openUrlInNewTab(),
        ];
    }

    public function getMaxContentWidth(): MaxWidth
    {
        return MaxWidth::Full;
    }
}
