<?php

namespace App\Filament\Widgets;

use App\Models\Ledger;
use App\Models\Order;
use App\Models\Expense;
use App\Models\Delivery;
use App\Models\Branch;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Tables\Actions\Action;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Response;
use Carbon\Carbon;

class LedgerOverviewWidget extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';
    
    protected static ?int $sort = 4;
    
    protected static ?string $heading = 'Ledger Entries & Financial Overview';

    public function table(Table $table): Table
    {
        $currency_symbol = config('settings.currency_symbol', 'AED');
        
        return $table
            ->query(
                Ledger::query()
                    ->with(['customer.driver', 'customer.vehicle', 'order.branch'])
                    ->latest('transaction_date')
                    ->latest('id')
            )
            ->columns([
                Tables\Columns\TextColumn::make('entry_number')
                    ->label('Entry No')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('transaction_date')
                    ->label('Date')
                    ->date('d-m-Y')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('entry_origin')
                    ->label('Origin')
                    ->searchable()
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('customer.company_name')
                    ->label('Customer')
                    ->formatStateUsing(function ($record) {
                        $customer = $record->customer;
                        if ($customer) {
                            return ($customer->company_name ?: 'N/A') . ' - ' . 
                                   ($customer->driver?->name ?: 'N/A') . ' - ' . 
                                   ($customer->vehicle?->vehicle_number ?: 'N/A');
                        }
                        return 'N/A';
                    })
                    ->searchable()
                    ->sortable()
                    ->wrap(),
                
                Tables\Columns\TextColumn::make('order.branch.name')
                    ->label('Branch')
                    ->formatStateUsing(function ($record) {
                        // Only show branch if transaction is from an order
                        if ($record->transaction_type === 'order' && $record->order) {
                            return $record->order->branch?->name ?: 'N/A';
                        }
                        return '-';
                    })
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('transaction_type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'order' => 'success',
                        'payment' => 'info',
                        'opening_balance' => 'warning',
                        'manual' => 'gray',
                        default => 'gray',
                    }),
                
                Tables\Columns\TextColumn::make('debit_amount')
                    ->label('Debit')
                    ->money($currency_symbol)
                    ->alignRight()
                    ->color('success')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('credit_amount')
                    ->label('Credit')
                    ->money($currency_symbol)
                    ->alignRight()
                    ->color('danger')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('balance')
                    ->label('Balance')
                    ->money($currency_symbol)
                    ->alignRight()
                    ->color(fn ($state) => $state < 0 ? 'danger' : 'success')
                    ->weight('bold')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('description')
                    ->label('Description')
                    ->limit(50)
                    ->searchable()
                    ->toggleable()
                    ->wrap(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('transaction_type')
                    ->options([
                        'order' => 'Order',
                        'payment' => 'Payment',
                        'opening_balance' => 'Opening Balance',
                        'manual' => 'Manual Entry',
                    ]),
                
                Tables\Filters\SelectFilter::make('customer')
                    ->label('Customer')
                    ->options(function () {
                        return \App\Models\Customer::whereNotNull('company_name')
                            ->where('company_name', '!=', '')
                            ->get()
                            ->mapWithKeys(function ($customer) {
                                $label = ($customer->company_name ?: 'N/A') . ' - ' . 
                                        ($customer->driver?->name ?: 'N/A') . ' - ' . 
                                        ($customer->vehicle?->vehicle_number ?: 'N/A');
                                return [$customer->id => $label];
                            });
                    })
                    ->searchable(),
                
                Tables\Filters\Filter::make('transaction_date')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('from')
                            ->label('From Date'),
                        \Filament\Forms\Components\DatePicker::make('until')
                            ->label('Until Date'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('transaction_date', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('transaction_date', '<=', $date),
                            );
                    }),
            ])
            ->headerActions([
                Action::make('exportCsv')
                    ->label('Export CSV')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->action(function () {
                        return $this->exportToCsv();
                    }),
                
                Action::make('exportPdf')
                    ->label('Export PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('danger')
                    ->action(function () {
                        return $this->exportToPdf();
                    }),
                
                Action::make('viewSummary')
                    ->label('View Summary')
                    ->icon('heroicon-o-chart-bar')
                    ->color('info')
                    ->modalHeading('Financial Summary')
                    ->modalContent(fn () => view('filament.widgets.ledger-summary', [
                        'summary' => $this->getFinancialSummary()
                    ]))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close'),
            ])
            ->defaultPaginationPageOption(25)
            ->poll('30s');
    }

    protected function getFinancialSummary(): array
    {
        $currency_symbol = config('settings.currency_symbol', 'AED');
        
        // Get all ledger entries
        $totalDebit = Ledger::sum('debit_amount');
        $totalCredit = Ledger::sum('credit_amount');
        
        // Get orders total (this is revenue)
        $ordersTotal = Order::sum('price');
        
        // Get expenses total
        $expensesTotal = Expense::where('is_approved', true)->sum('amount');
        
        // Get deliveries (pending and scheduled only - not counted in revenue)
        $pendingDeliveries = Delivery::whereIn('status', ['pending', 'scheduled'])->count();
        $scheduledDeliveries = Delivery::where('status', 'scheduled')->count();
        
        // Calculate profit/loss (Revenue - Expenses)
        $profit = $ordersTotal - $expensesTotal;
        
        return [
            'total_debit' => $totalDebit,
            'total_credit' => $totalCredit,
            'net_balance' => $totalDebit - $totalCredit,
            'orders_total' => $ordersTotal,
            'expenses_total' => $expensesTotal,
            'profit_loss' => $profit,
            'pending_deliveries' => $pendingDeliveries,
            'scheduled_deliveries' => $scheduledDeliveries,
            'currency_symbol' => $currency_symbol,
        ];
    }

    protected function exportToCsv()
    {
        $ledgers = Ledger::with(['customer.driver', 'customer.vehicle', 'order.branch'])
            ->orderBy('transaction_date', 'desc')
            ->orderBy('id', 'desc')
            ->get();
        
        $summary = $this->getFinancialSummary();
        $currency = $summary['currency_symbol'];
        
        $filename = 'ledger_export_' . now()->format('Y-m-d_His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        
        $callback = function() use ($ledgers, $summary, $currency) {
            $file = fopen('php://output', 'w');
            
            // Add summary header
            fputcsv($file, ['FINANCIAL SUMMARY']);
            fputcsv($file, ['']);
            fputcsv($file, ['Total Debit', number_format($summary['total_debit'], 2) . ' ' . $currency]);
            fputcsv($file, ['Total Credit', number_format($summary['total_credit'], 2) . ' ' . $currency]);
            fputcsv($file, ['Net Balance', number_format($summary['net_balance'], 2) . ' ' . $currency]);
            fputcsv($file, ['']);
            fputcsv($file, ['Orders Total (Revenue)', number_format($summary['orders_total'], 2) . ' ' . $currency]);
            fputcsv($file, ['Expenses Total', number_format($summary['expenses_total'], 2) . ' ' . $currency]);
            fputcsv($file, ['Profit/Loss', number_format($summary['profit_loss'], 2) . ' ' . $currency]);
            fputcsv($file, ['']);
            fputcsv($file, ['Pending Deliveries', $summary['pending_deliveries']]);
            fputcsv($file, ['Scheduled Deliveries', $summary['scheduled_deliveries']]);
            fputcsv($file, ['']);
            fputcsv($file, ['']);
            
            // Add table header
            fputcsv($file, [
                'Entry No',
                'Date',
                'Origin',
                'Customer',
                'Driver',
                'Vehicle',
                'Branch',
                'Type',
                'Debit',
                'Credit',
                'Balance',
                'Description'
            ]);
            
            // Add data rows
            foreach ($ledgers as $ledger) {
                // Get branch name (only for orders)
                $branchName = '-';
                if ($ledger->transaction_type === 'order' && $ledger->order) {
                    $branchName = $ledger->order->branch?->name ?: 'N/A';
                }
                
                fputcsv($file, [
                    $ledger->entry_number,
                    $ledger->transaction_date?->format('d-m-Y'),
                    $ledger->entry_origin,
                    $ledger->customer?->company_name ?: 'N/A',
                    $ledger->customer?->driver?->name ?: 'N/A',
                    $ledger->customer?->vehicle?->vehicle_number ?: 'N/A',
                    $branchName,
                    $ledger->transaction_type,
                    number_format($ledger->debit_amount, 2),
                    number_format($ledger->credit_amount, 2),
                    number_format($ledger->balance, 2),
                    $ledger->description
                ]);
            }
            
            fclose($file);
        };
        
        return Response::stream($callback, 200, $headers);
    }

    protected function exportToPdf()
    {
        $ledgers = Ledger::with(['customer.driver', 'customer.vehicle', 'order.branch'])
            ->orderBy('transaction_date', 'desc')
            ->orderBy('id', 'desc')
            ->get();
        
        $summary = $this->getFinancialSummary();
        
        $html = view('filament.widgets.ledger-pdf', [
            'ledgers' => $ledgers,
            'summary' => $summary
        ])->render();
        
        $mpdf = new \Mpdf\Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4-L',
            'orientation' => 'L',
            'margin_left' => 10,
            'margin_right' => 10,
            'margin_top' => 10,
            'margin_bottom' => 10,
        ]);
        
        $mpdf->WriteHTML($html);
        
        return response()->streamDownload(function() use ($mpdf) {
            echo $mpdf->Output('', 'S');
        }, 'ledger_export_' . now()->format('Y-m-d_His') . '.pdf');
    }

    protected function getTableHeading(): string
    {
        return 'Ledger Entries & Financial Overview';
    }
}

