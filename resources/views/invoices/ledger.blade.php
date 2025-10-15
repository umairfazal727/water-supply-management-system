<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Customer Ledger</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            font-size: 12px;
            line-height: 1.4;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }
        .logo {
            width: 200px;
            height: 200px;
            margin: 0 auto 10px;
            border: 1px solid #ddd;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #f8f9fa;
        }
        .company-name {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .company-name-arabic {
            font-size: 14px;
            margin-bottom: 10px;
        }
        .address {
            font-size: 11px;
            margin-bottom: 10px;
        }
        .contact-info {
            font-size: 10px;
        }
        .document-title {
            font-size: 16px;
            font-weight: bold;
            margin: 20px 0;
            text-align: center;
        }
        .period {
            text-align: center;
            margin-bottom: 20px;
            font-size: 11px;
        }
        .account-info {
            margin-bottom: 20px;
            font-size: 11px;
        }
        .ledger-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .ledger-table th,
        .ledger-table td {
            border: 1px solid #333;
            padding: 5px;
            text-align: left;
            font-size: 10px;
        }
        .ledger-table th {
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: center;
        }
        .ledger-table .number {
            text-align: right;
        }
        .ledger-table .center {
            text-align: center;
        }
        .summary {
            margin-top: 20px;
            font-size: 11px;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }
        .footer {
            margin-top: 40px;
            display: flex;
            justify-content: space-between;
            align-items: end;
        }
        .signature {
            width: 150px;
            height: 50px;
            border-bottom: 1px solid #333;
        }
        .notes {
            margin-top: 20px;
            font-size: 10px;
        }
        .balance-negative {
            color: #d32f2f;
            font-weight: bold;
        }
        .balance-positive {
            color: #388e3c;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="logo">
            <div style="font-size: 24px; font-weight: bold;">SC</div>
        </div>
        <div class="company-name">SOLID CUBE READY MIX IND. LLC</div>
        <div class="company-name-arabic">المكعب الصلب لصناعة الخرسانة الجاهزة ذ م م</div>
        <div class="address">Alsajaa Industrial, Near BEEAH Waste Management Complex, Sharjah UAE</div>
        <div class="contact-info">
            Email: solidcuberedaymix@gmail.com<br>
            Tel: +97165237334, +971501435050, +971545849060
        </div>
    </div>

    <!-- Document Title -->
    <div class="document-title">LEDGER</div>

    <!-- Period -->
    <div class="period">
        @if($from_date && $to_date)
            {{ \Carbon\Carbon::parse($from_date)->format('d-m-Y') }} Until {{ \Carbon\Carbon::parse($to_date)->format('d-m-Y') }}
        @elseif($from_date)
            From {{ \Carbon\Carbon::parse($from_date)->format('d-m-Y') }}
        @else
            All Records
        @endif
    </div>

    <!-- Account Information -->
    <div class="account-info">
        <strong>Account Name:</strong> {{ $customer->id }}-{{ strtoupper($customer->company_name ?? $customer->first_name . ' ' . $customer->last_name) }}<br>
        <strong>Currency:</strong> AED
    </div>

    <!-- Ledger Table -->
    <table class="ledger-table">
        <thead>
            <tr>
                <th style="width: 8%;">Entry No</th>
                <th style="width: 10%;">Date</th>
                <th style="width: 12%;">Entry Origin</th>
                <th style="width: 12%;">Debit</th>
                <th style="width: 12%;">Credit</th>
                <th style="width: 12%;">Balance</th>
                <th style="width: 34%;">Line-Item Description</th>
            </tr>
        </thead>
        <tbody>
            @foreach($ledgerEntries as $entry)
                <tr>
                    <td class="center">{{ $entry->entry_number }}</td>
                    <td class="center">{{ $entry->transaction_date->format('d-m-Y') }}</td>
                    <td class="center">{{ $entry->entry_origin ?? '-' }}</td>
                    <td class="number">
                        @if($entry->debit_amount > 0)
                            {{ number_format($entry->debit_amount, 2) }}
                        @else
                            0.00
                        @endif
                    </td>
                    <td class="number">
                        @if($entry->credit_amount > 0)
                            {{ number_format($entry->credit_amount, 2) }}
                        @else
                            0.00
                        @endif
                    </td>
                    <td class="number {{ $entry->balance < 0 ? 'balance-negative' : 'balance-positive' }}">
                        {{ number_format($entry->balance, 2) }}
                    </td>
                    <td>{{ $entry->description }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Summary -->
    <div class="summary">
        <div class="summary-row">
            <span><strong>Notes:</strong></span>
            <span></span>
        </div>
        <div class="summary-row">
            <span><strong>Previous Balance:</strong></span>
            <span class="{{ $previousBalance < 0 ? 'balance-negative' : 'balance-positive' }}">
                {{ number_format($previousBalance, 2) }}
            </span>
        </div>
        <div class="summary-row">
            <span><strong>Debit (Total):</strong></span>
            <span class="balance-positive">{{ number_format($totalDebit, 2) }}</span>
        </div>
        <div class="summary-row">
            <span><strong>Credit (Total):</strong></span>
            <span class="balance-negative">{{ number_format($totalCredit, 2) }}</span>
        </div>
        <div class="summary-row" style="border-top: 1px solid #333; padding-top: 5px; margin-top: 10px;">
            <span><strong>Final Balance:</strong></span>
            <span class="{{ $finalBalance < 0 ? 'balance-negative' : 'balance-positive' }}">
                {{ number_format($finalBalance, 2) }}
            </span>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <div class="notes">
            Generated on {{ now()->format('d-m-Y H:i:s') }}
        </div>
        <div style="text-align: center;">
            <div style="margin-bottom: 20px;">Signature</div>
            <div class="signature"></div>
        </div>
    </div>
</body>
</html>
