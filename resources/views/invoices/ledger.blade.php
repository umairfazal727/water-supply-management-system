<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Customer Ledger</title>
<style>
    body {
        font-family: Arial, sans-serif;
        background: #fff;
        margin: 0;
        padding: 20px;
        color: #000;
    }
    .invoice-container {
        max-width: 900px;
        margin: auto;
        border: 1px solid #ddd;
        padding: 25px;
        border-radius: 8px;
        background: #fff;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    .header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        border-bottom: 2px solid #1b73b3;
        padding-bottom: 10px;
        margin-bottom: 15px;
    }
    .header img {
        width: 200px;
        height: 200px;
        object-fit: contain;
    }
    .company-info {
        flex: 1;
        text-align: right;
        color: #1b73b3;
    }
    .company-info h2 {
        margin: 0;
        font-size: 20px;
        font-weight: 700;
        color: #1b73b3;
    }
    .company-info p {
        margin: 2px 0;
        font-size: 13px;
    }

    .statement-title {
        text-align: center;
        font-weight: bold;
        font-size: 18px;
        margin: 10px 0;
        color: #000;
    }

    .details {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 20px;
    }
    .details td {
        padding: 6px 10px;
        font-size: 14px;
    }
    .details .label {
        font-weight: bold;
        width: 180px;
    }

    .table-section {
        margin-top: 20px;
    }
    table {
        width: 100%;
        border-collapse: collapse;
        font-size: 12px;
    }
    th, td {
        border: 1px solid #ccc;
        padding: 8px;
        text-align: center;
    }
    th {
        background-color: #1b73b3;
        color: white;
        font-weight: 600;
    }
    tr:nth-child(even) {
        background: #f9f9f9;
    }
    .text-right {
        text-align: right;
    }
    .text-left {
        text-align: left;
    }
    .balance-negative {
        color: #d32f2f;
        font-weight: bold;
    }
    .balance-positive {
        color: #388e3c;
        font-weight: bold;
    }
    .stamp {
        margin-top: 40px;
        text-align: left;
    }
    .stamp img {
        width: 200px;
        height: 200px;
        object-fit: contain;
        opacity: 0.8;
    }
    .summary-section {
        margin-top: 20px;
        border-top: 2px solid #1b73b3;
        padding-top: 15px;
    }
    .summary-row {
        display: flex;
        justify-content: space-between;
        padding: 5px 0;
        font-size: 13px;
    }
    .summary-row.final {
        border-top: 2px solid #333;
        margin-top: 10px;
        padding-top: 10px;
        font-size: 15px;
        font-weight: bold;
    }
</style>
</head>
<body>

<div class="invoice-container">
    <!-- Header Section -->
    <div class="header">
        <img src="{{asset('invoice-img/logo.png')}}" alt="Company Logo" style="width: 150px; height: 150px; object-fit: contain;">
        <div class="company-info">
            <h2>ريم الفلج لتجارة المياه المخصصة لاعمال البناء</h2>
            <h2>REEM AL FALAJ CONST. WATER TR</h2>
            <p>Salja Industrial Area, Sharjah - U.A.E.</p>
            <p>050 8426001 | 055 2496358 | 055 7466868 | 052 3943958</p>
        </div>
    </div>

    <!-- Ledger Title -->
    <div class="statement-title">CUSTOMER LEDGER</div>

    <!-- Details -->
    <table class="details">
        <tr>
            <td class="label">TRN NO:</td>
            <td>100614240800003</td>
            <td class="label">DATE:</td>
            <td>{{ date('d/m/Y') }}</td>
        </tr>
        <tr>
            <td class="label">ACCOUNT NAME:</td>
            <td colspan="3">{{ $customer->id }}-{{ strtoupper($customer->company_name ?? $customer->first_name . ' ' . $customer->last_name) }}</td>
        </tr>
        <tr>
            <td class="label">PERIOD:</td>
            <td colspan="3">
                @if($from_date && $to_date)
                    {{ \Carbon\Carbon::parse($from_date)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($to_date)->format('d/m/Y') }}
                @elseif($from_date)
                    From {{ \Carbon\Carbon::parse($from_date)->format('d/m/Y') }}
                @else
                    All Records
                @endif
            </td>
        </tr>
    </table>

    <!-- Ledger Table -->
    <div class="table-section">
        <table>
            <thead>
                <tr>
                    <th style="width: 8%;">ENTRY NO</th>
                    <th style="width: 10%;">DATE</th>
                    <th style="width: 12%;">ENTRY ORIGIN</th>
                    <th style="width: 12%;">DEBIT</th>
                    <th style="width: 12%;">CREDIT</th>
                    <th style="width: 12%;">BALANCE</th>
                    <th style="width: 34%;">DESCRIPTION</th>
                </tr>
            </thead>
            <tbody>
                @forelse($ledgerEntries as $entry)
                <tr>
                    <td>{{ $entry->entry_number }}</td>
                    <td>{{ $entry->transaction_date->format('d/m/Y') }}</td>
                    <td>{{ $entry->entry_origin ?? '-' }}</td>
                    <td class="text-right">
                        @if($entry->debit_amount > 0)
                            {{ number_format($entry->debit_amount, 2) }}
                        @else
                            0.00
                        @endif
                    </td>
                    <td class="text-right">
                        @if($entry->credit_amount > 0)
                            {{ number_format($entry->credit_amount, 2) }}
                        @else
                            0.00
                        @endif
                    </td>
                    <td class="text-right {{ $entry->balance < 0 ? 'balance-negative' : 'balance-positive' }}">
                        {{ number_format($entry->balance, 2) }}
                    </td>
                    <td class="text-left">{{ $entry->description }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align: center;">No ledger entries found for this period</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Summary Section -->
    <div class="summary-section">
        <div class="summary-row">
            <span><strong>Previous Balance:</strong></span>
            <span class="{{ $previousBalance < 0 ? 'balance-negative' : 'balance-positive' }}">
                AED {{ number_format($previousBalance, 2) }}
            </span>
        </div>
        <div class="summary-row">
            <span><strong>Total Debit (Sales/Invoices):</strong></span>
            <span class="balance-positive">AED {{ number_format($totalDebit, 2) }}</span>
        </div>
        <div class="summary-row">
            <span><strong>Total Credit (Payments):</strong></span>
            <span class="balance-negative">AED {{ number_format($totalCredit, 2) }}</span>
        </div>
        <div class="summary-row final">
            <span><strong>FINAL BALANCE:</strong></span>
            <span class="{{ $finalBalance < 0 ? 'balance-negative' : 'balance-positive' }}">
                AED {{ number_format($finalBalance, 2) }}
            </span>
        </div>
    </div>

    <!-- Stamp -->
    {{-- <div class="stamp">
        <img src="{{asset('invoice-img/stamp.png')}}" alt="Company Stamp" style="width: 150px; height: 150px; object-fit: contain;">
    </div> --}}
</div>

</body>
</html>
