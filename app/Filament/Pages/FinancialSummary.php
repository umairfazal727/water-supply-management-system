<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\Order;
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

class FinancialSummary extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-calculator';
    protected static ?string $navigationLabel = 'Financial Summary';
    protected static string $view = 'filament.pages.financial-summary';
    protected static ?string $title = 'Financial Summary';
    protected static ?int $navigationSort = 1;

    public bool $isLoading = false;
    public ?array $data = [];
    
    public Collection $results;
    public array $summary = [
        'total_orders' => 0,
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
            // 'report_type' => 'profit',
        ]);
        
        // Generate report on initial load
        $this->generateReport();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Financial Report Filters')
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
                        
                        Select::make('branch_ids')
                            ->label('Select Branches')
                            ->options(function () {
                                return Branch::where('is_active', true)
                                    ->get()
                                    ->mapWithKeys(function ($branch) {
                                        return [$branch->id => $branch->name];
                                    });
                            })
                            ->multiple()
                            ->searchable()
                            ->placeholder('Select branches (leave empty for all)')
                            ->live()
                            ->afterStateUpdated(function () {
                                if ($this->data['from_date'] ?? null && $this->data['to_date'] ?? null) {
                                    $this->generateReport();
                                }
                            }),
                        
                        // Select::make('report_type')
                        //     ->label('Report Type')
                        //     ->options([
                        //         'profit' => 'Profit',
                        //         'revenue' => 'Revenue',
                        //         'credit' => 'Credit',
                        //         'cash' => 'Cash',
                        //         'expenses' => 'Expenses List',
                        //     ])
                        //     ->default('profit')
                        //     ->required()
                        //     ->live()
                        //     ->afterStateUpdated(function () {
                        //         if ($this->data['from_date'] ?? null && $this->data['to_date'] ?? null) {
                        //             $this->generateReport();
                        //         }
                        //     }),
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
        $branchIds = $data['branch_ids'] ?? [];
        // $reportType = $data['report_type'] ?? 'profit';

        // Query orders - exclude null dates
        $ordersQuery = Order::whereNotNull('order_date')
            ->whereBetween('order_date', [
                \Carbon\Carbon::parse($fromDate)->startOfDay(),
                \Carbon\Carbon::parse($toDate)->endOfDay()
            ]);

        if (!empty($branchIds)) {
            $ordersQuery->whereIn('branch_id', $branchIds);
        }

        $orders = $ordersQuery->get();

        // Query expenses - exclude null dates
        $expensesQuery = Expense::whereNotNull('expense_date')
            ->whereBetween('expense_date', [
                \Carbon\Carbon::parse($fromDate)->startOfDay(),
                \Carbon\Carbon::parse($toDate)->endOfDay()
            ]);

        if (!empty($branchIds)) {
            $expensesQuery->whereIn('branch_id', $branchIds);
        }

        $expenses = $expensesQuery->get();

        // Group by date
        $resultsByDate = [];

        // Process orders by date
        foreach ($orders as $order) {
            // Handle null or string dates
            if ($order->order_date === null) {
                continue; // Skip orders without dates
            }
            
            // Convert to Carbon if it's a string
            $orderDate = is_string($order->order_date) 
                ? \Carbon\Carbon::parse($order->order_date)
                : $order->order_date;
            
            $date = $orderDate->format('Y-m-d');
            
            if (!isset($resultsByDate[$date])) {
                $resultsByDate[$date] = [
                    'date' => $date,
                    'orders' => 0,
                    'revenue' => 0,
                    'cash' => 0,
                    'credit' => 0,
                    'expenses' => 0,
                ];
            }

            $resultsByDate[$date]['orders']++;
            $resultsByDate[$date]['revenue'] += (float) ($order->price ?? 0);
            
            if (in_array($order->payment_type, ['cash', 'bank_transfer'])) {
                $resultsByDate[$date]['cash'] += (float) ($order->price ?? 0);
            } else {
                $resultsByDate[$date]['credit'] += (float) ($order->price ?? 0);
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
                    'orders' => 0,
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
        $totalRevenue = $orders->sum(function ($order) {
            return (float) ($order->price ?? 0);
        });
        
        $totalCash = $orders->whereIn('payment_type', ['cash', 'bank_transfer'])->sum(function ($order) {
            return (float) ($order->price ?? 0);
        });
        
        $totalCredit = $orders->whereIn('payment_type', ['credit', 'on_account'])->sum(function ($order) {
            return (float) ($order->price ?? 0);
        });
        
        $totalExpenses = $expenses->sum(function ($expense) {
            return (float) ($expense->amount ?? 0);
        });
        
        $this->summary = [
            'total_orders' => $orders->count(),
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

