<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Ledger Export - {{ now()->format('d-m-Y') }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10px;
            line-height: 1.4;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #333;
        }
        .header h1 {
            margin: 0;
            font-size: 20px;
            color: #333;
        }
        .header p {
            margin: 5px 0;
            color: #666;
        }
        .summary-section {
            margin-bottom: 20px;
            background: #f5f5f5;
            padding: 15px;
            border-radius: 5px;
        }
        .summary-grid {
            display: table;
            width: 100%;
            margin-bottom: 10px;
        }
        .summary-row {
            display: table-row;
        }
        .summary-cell {
            display: table-cell;
            padding: 5px 10px;
            border-bottom: 1px solid #ddd;
        }
        .summary-label {
            font-weight: bold;
            color: #333;
        }
        .summary-value {
            text-align: right;
            color: #000;
        }
        .profit { color: #22c55e; font-weight: bold; }
        .loss { color: #ef4444; font-weight: bold; }
        .section-title {
            font-size: 14px;
            font-weight: bold;
            margin: 15px 0 10px 0;
            padding: 5px 0;
            border-bottom: 1px solid #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th {
            background-color: #333;
            color: white;
            padding: 8px 5px;
            text-align: left;
            font-weight: bold;
            font-size: 9px;
        }
        td {
            padding: 6px 5px;
            border-bottom: 1px solid #ddd;
            font-size: 9px;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .debit {
            color: #22c55e;
            font-weight: bold;
        }
        .credit {
            color: #ef4444;
            font-weight: bold;
        }
        .balance {
            font-weight: bold;
        }
        .footer {
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #ddd;
            text-align: center;
            font-size: 8px;
            color: #666;
        }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
        }
        .badge-success { background: #22c55e; color: white; }
        .badge-info { background: #3b82f6; color: white; }
        .badge-warning { background: #f59e0b; color: white; }
        .badge-gray { background: #6b7280; color: white; }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>Ledger & Financial Report</h1>
        <p>Generated on: {{ now()->format('d M Y, h:i A') }}</p>
    </div>

    <!-- Financial Summary -->
    <div class="summary-section">
        <div class="section-title">FINANCIAL SUMMARY</div>
        
        <div class="summary-grid">
            <div class="summary-row">
                <div class="summary-cell summary-label">Total Debit</div>
                <div class="summary-cell summary-value debit">
                    {{ $summary['currency_symbol'] }} {{ number_format($summary['total_debit'], 2) }}
                </div>
                <div class="summary-cell summary-label">Total Credit</div>
                <div class="summary-cell summary-value credit">
                    {{ $summary['currency_symbol'] }} {{ number_format($summary['total_credit'], 2) }}
                </div>
            </div>
            <div class="summary-row">
                <div class="summary-cell summary-label">Net Balance</div>
                <div class="summary-cell summary-value {{ $summary['net_balance'] >= 0 ? 'profit' : 'loss' }}">
                    {{ $summary['currency_symbol'] }} {{ number_format($summary['net_balance'], 2) }}
                </div>
                <div class="summary-cell summary-label">Profit/Loss</div>
                <div class="summary-cell summary-value {{ $summary['profit_loss'] >= 0 ? 'profit' : 'loss' }}">
                    {{ $summary['currency_symbol'] }} {{ number_format($summary['profit_loss'], 2) }}
                </div>
            </div>
        </div>

        <div class="section-title" style="margin-top: 15px;">REVENUE & EXPENSES</div>
        <div class="summary-grid">
            <div class="summary-row">
                <div class="summary-cell summary-label">Orders Total (Revenue)</div>
                <div class="summary-cell summary-value profit">
                    {{ $summary['currency_symbol'] }} {{ number_format($summary['orders_total'], 2) }}
                </div>
                <div class="summary-cell summary-label">Expenses Total</div>
                <div class="summary-cell summary-value loss">
                    {{ $summary['currency_symbol'] }} {{ number_format($summary['expenses_total'], 2) }}
                </div>
            </div>
        </div>

        <div class="section-title" style="margin-top: 15px;">DELIVERIES STATUS</div>
        <div class="summary-grid">
            <div class="summary-row">
                <div class="summary-cell summary-label">Pending Deliveries</div>
                <div class="summary-cell summary-value">{{ $summary['pending_deliveries'] }}</div>
                <div class="summary-cell summary-label">Scheduled Deliveries</div>
                <div class="summary-cell summary-value">{{ $summary['scheduled_deliveries'] }}</div>
            </div>
        </div>
        
        <p style="margin-top: 10px; font-size: 8px; color: #666; font-style: italic;">
            Note: Delivery amounts are not included in profit/loss calculations.
        </p>
    </div>

    <!-- Ledger Entries Table -->
    <div class="section-title">LEDGER ENTRIES</div>
    <table>
        <thead>
            <tr>
                <th>Entry No</th>
                <th>Date</th>
                <th>Origin</th>
                <th>Customer</th>
                <th>Driver</th>
                <th>Vehicle</th>
                <th>Type</th>
                <th class="text-right">Debit</th>
                <th class="text-right">Credit</th>
                <th class="text-right">Balance</th>
            </tr>
        </thead>
        <tbody>
            @forelse($ledgers as $ledger)
            <tr>
                <td>{{ $ledger->entry_number }}</td>
                <td>{{ $ledger->transaction_date?->format('d-m-Y') }}</td>
                <td>{{ $ledger->entry_origin ?: '-' }}</td>
                <td>{{ $ledger->customer?->company_name ?: 'N/A' }}</td>
                <td>{{ $ledger->customer?->driver?->name ?: 'N/A' }}</td>
                <td>{{ $ledger->customer?->vehicle?->vehicle_number ?: 'N/A' }}</td>
                <td>
                    <span class="badge badge-{{ 
                        $ledger->transaction_type === 'order' ? 'success' : 
                        ($ledger->transaction_type === 'payment' ? 'info' : 
                        ($ledger->transaction_type === 'opening_balance' ? 'warning' : 'gray')) 
                    }}">
                        {{ ucfirst(str_replace('_', ' ', $ledger->transaction_type)) }}
                    </span>
                </td>
                <td class="text-right debit">
                    {{ $ledger->debit_amount > 0 ? number_format($ledger->debit_amount, 2) : '-' }}
                </td>
                <td class="text-right credit">
                    {{ $ledger->credit_amount > 0 ? number_format($ledger->credit_amount, 2) : '-' }}
                </td>
                <td class="text-right balance">
                    {{ number_format($ledger->balance, 2) }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="10" class="text-center">No ledger entries found</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Footer -->
    <div class="footer">
        <p>This is a computer-generated document. No signature required.</p>
        <p>Page generated on {{ now()->format('d M Y, h:i:s A') }}</p>
    </div>
</body>
</html>

