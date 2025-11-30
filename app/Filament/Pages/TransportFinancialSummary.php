<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\Delivery;
use App\Models\Expense;
use App\Models\Branch;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Illuminate\Support\Collection;
use Filament\Support\Enums\MaxWidth;

class TransportFinancialSummary extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-calculator';
    protected static ?string $navigationLabel = 'Transport Financial Summary';
    protected static ?string $navigationGroup = 'Water Transport';
    protected static string $view = 'filament.pages.transport-financial-summary';
    protected static ?string $title = 'Transport Financial Summary';
    protected static ?int $navigationSort = 4;

    public bool $isLoading = false;
    public ?array $data = [];
    
    public Collection $results;
    public array $summary = [
        'total_deliveries' => 0,
        'total_revenue' => 0,
        'total_cash' => 0,
        'total_credit' => 0,
        'total_expenses' => 0,
        'profit' => 0,
    ];

    public function mount(): void
    {
        $this->results = collect([]);
        $this->form->fill([
            'from_date' => now()->startOfMonth()->format('Y-m-d'),
            'to_date' => now()->format('Y-m-d'),
        ]);
        
        // Generate report on initial load
        $this->generateReport();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Transport Financial Report Filters')
                    ->schema([
                        DatePicker::make('from_date')
                            ->label('From Date')
                            ->default(now()->startOfMonth())
                            ->required()
                            ->live()
                            ->afterStateUpdated(function () {
                                if ($this->data['to_date'] ?? null) {
                                    $this->generateReport();
                                }
                            }),
                        
                        DatePicker::make('to_date')
                            ->label('To Date')
                            ->default(now())
                            ->required()
                            ->live()
                            ->afterStateUpdated(function () {
                                if ($this->data['from_date'] ?? null) {
                                    $this->generateReport();
                                }
                            }),
                    ])->columns(2),
            ])
            ->statePath('data');
    }

    public function generateReport(): void
    {
        $this->isLoading = true;
        $data = $this->form->getState();
        
        $fromDate = $data['from_date'] ?? now()->startOfMonth();
        $toDate = $data['to_date'] ?? now();

        // Query deliveries - exclude null dates
        $deliveriesQuery = Delivery::whereNotNull('delivery_date')
            ->whereBetween('delivery_date', [
                \Carbon\Carbon::parse($fromDate)->startOfDay(),
                \Carbon\Carbon::parse($toDate)->endOfDay()
            ]);

        $deliveries = $deliveriesQuery->get();

        // Query transport expenses - exclude null dates
        $expensesQuery = Expense::whereNotNull('expense_date')
            ->where('expense_type', 'transport')
            ->whereBetween('expense_date', [
                \Carbon\Carbon::parse($fromDate)->startOfDay(),
                \Carbon\Carbon::parse($toDate)->endOfDay()
            ]);

        $expenses = $expensesQuery->get();

        // Group by date
        $resultsByDate = [];

        // Process deliveries by date
        foreach ($deliveries as $delivery) {
            // Handle null or string dates
            if ($delivery->delivery_date === null) {
                continue; // Skip deliveries without dates
            }
            
            // Convert to Carbon if it's a string
            $deliveryDate = is_string($delivery->delivery_date) 
                ? \Carbon\Carbon::parse($delivery->delivery_date)
                : $delivery->delivery_date;
            
            $date = $deliveryDate->format('Y-m-d');
            
            if (!isset($resultsByDate[$date])) {
                $resultsByDate[$date] = [
                    'date' => $date,
                    'deliveries' => 0,
                    'revenue' => 0,
                    'cash' => 0,
                    'credit' => 0,
                    'expenses' => 0,
                ];
            }

            $resultsByDate[$date]['deliveries']++;
            $resultsByDate[$date]['revenue'] += (float) ($delivery->total_amount ?? 0);
            
            if (in_array($delivery->payment_method, ['cash', 'bank_transfer'])) {
                $resultsByDate[$date]['cash'] += (float) ($delivery->total_amount ?? 0);
            } else {
                $resultsByDate[$date]['credit'] += (float) ($delivery->total_amount ?? 0);
            }
        }

        // Process expenses by date
        foreach ($expenses as $expense) {
            // Handle null or string dates
            if ($expense->expense_date === null) {
                continue; // Skip expenses without dates
            }
            
            // Convert to Carbon if it's a string
            $expenseDate = is_string($expense->expense_date)
                ? \Carbon\Carbon::parse($expense->expense_date)
                : $expense->expense_date;
            
            $date = $expenseDate->format('Y-m-d');
            
            if (!isset($resultsByDate[$date])) {
                $resultsByDate[$date] = [
                    'date' => $date,
                    'deliveries' => 0,
                    'revenue' => 0,
                    'cash' => 0,
                    'credit' => 0,
                    'expenses' => 0,
                ];
            }

            $resultsByDate[$date]['expenses'] += (float) ($expense->amount ?? 0);
        }

        // Calculate profit for each date
        foreach ($resultsByDate as &$result) {
            $result['profit'] = $result['revenue'] - $result['expenses'];
        }

        // Sort by date
        ksort($resultsByDate);

        $this->results = collect($resultsByDate);

        // Calculate summary
        $totalRevenue = $deliveries->sum(function ($delivery) {
            return (float) ($delivery->total_amount ?? 0);
        });
        
        $totalCash = $deliveries->whereIn('payment_method', ['cash', 'bank_transfer'])->sum(function ($delivery) {
            return (float) ($delivery->total_amount ?? 0);
        });
        
        $totalCredit = $deliveries->whereIn('payment_method', ['credit', 'check'])->sum(function ($delivery) {
            return (float) ($delivery->total_amount ?? 0);
        });
        
        $totalExpenses = $expenses->sum(function ($expense) {
            return (float) ($expense->amount ?? 0);
        });
        
        $this->summary = [
            'total_deliveries' => $deliveries->count(),
            'total_revenue' => $totalRevenue,
            'total_cash' => $totalCash,
            'total_credit' => $totalCredit,
            'total_expenses' => $totalExpenses,
            'profit' => $totalRevenue - $totalExpenses,
        ];

        $this->isLoading = false;
    }

    public function getMaxContentWidth(): MaxWidth
    {
        return MaxWidth::Full;
    }
}

