<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Expense Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 20px;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 2px solid #1b73b3;
            margin-bottom: 30px;
        }
        .header-table td {
            vertical-align: middle;
            padding: 10px;
            border: none;
        }
        .header-logo {
            width: 25%;
            text-align: left;
        }
        .header img {
            width: 200px;
            height: 200px;
            object-fit: contain;
        }
        .company-info {
            width: 50%;
            text-align: center;
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
        .phone-info {
            width: 25%;
            text-align: right;
            color: #1b73b3;
        }
        .phone-info p {
            margin: 3px 0;
            font-size: 13px;
        }
        .report-title {
            text-align: center;
            margin-top: 15px;
        }
        .report-title h1 {
            margin: 0;
            font-size: 24px;
            color: #333;
        }
        .report-title p {
            margin: 5px 0;
            color: #666;
        }
        .summary {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .summary h2 {
            margin-top: 0;
            font-size: 16px;
            color: #333;
            border-bottom: 1px solid #ddd;
            padding-bottom: 8px;
        }
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-top: 10px;
        }
        .summary-item {
            background: white;
            padding: 10px;
            border-radius: 4px;
        }
        .summary-item label {
            font-weight: bold;
            display: block;
            margin-bottom: 5px;
            color: #555;
        }
        .summary-item value {
            font-size: 14px;
            color: #333;
        }
        .summary-total {
            background: #fff3cd;
            padding: 15px;
            border-radius: 5px;
            margin-top: 10px;
            text-align: center;
        }
        .summary-total label {
            font-weight: bold;
            font-size: 14px;
            display: block;
            margin-bottom: 8px;
        }
        .summary-total value {
            font-size: 28px;
            font-weight: bold;
            color: #e74c3c;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th {
            background-color: #343a40;
            color: white;
            padding: 10px 8px;
            text-align: left;
            font-weight: bold;
            font-size: 11px;
        }
        td {
            padding: 8px;
            border-bottom: 1px solid #ddd;
            font-size: 11px;
        }
        tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        .badge {
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
        }
        .badge-approved {
            background-color: #d4edda;
            color: #155724;
        }
        .badge-pending {
            background-color: #fff3cd;
            color: #856404;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            border-top: 1px solid #ddd;
            padding-top: 10px;
            color: #666;
            font-size: 10px;
        }
        .currency {
            font-weight: bold;
            color: #e74c3c;
        }
    </style>
</head>
<body>
    <table class="header-table">
        <tr>
            <td class="header-logo">
                <img src="{{asset('invoice-img/logo.png')}}" style="height: 150px;  width: 150px;" alt="Company Logo">
            </td>
            <td class="company-info">
                <h2>ريم الفلج لتجارة المياه المخصصة لاعمال البناء</h2>
                <h2>REEM AL FALAJ CONST. WATER TR</h2>
                <p>Sajja Industrial Area, Sharjah - U.A.E.</p>
            </td>
            <td class="phone-info">
                <p style="margin: 0;">050 8426001</p>
                <p style="margin: 0;">050 2549368</p>
                <p style="margin: 0;">055 7466868</p>
                <p style="margin: 0;">052 9349368</p>
            </td>
        </tr>
    </table>

    <div class="report-title">
        <h1>Expense Report</h1>
        <p>Generated: {{ now()->format('M d, Y h:i A') }}</p>
    </div>

    <div class="summary">
        <h2>Report Summary</h2>
        <div class="summary-grid">
            <div class="summary-item">
                <label>Date Range</label>
                <value>{{ $startDate->format('M d, Y') }} - {{ $endDate->format('M d, Y') }}</value>
            </div>
            <div class="summary-item">
                <label>Period</label>
                <value>{{ $startDate->diffInDays($endDate) + 1 }} days</value>
            </div>
            <div class="summary-item">
                <label>Branch</label>
                <value>{{ $insights['branch_name'] ?? 'All Branches' }}</value>
            </div>
            <div class="summary-item">
                <label>Category</label>
                <value>{{ $insights['category_name'] ?? 'All Categories' }}</value>
            </div>
        </div>
        <div class="summary-total">
            <label>Total Expenses: {{ $insights['total_expenses'] ?? 0 }} records</label>
            <value>{{ config('settings.currency_symbol', 'AED') }}{{ number_format($insights['total_amount'] ?? 0, 2) }}</value>
        </div>
    </div>

    @if(count($reportData) > 0)
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Date</th>
                <th>Title</th>
                <th>Category</th>
                <th>Branch</th>
                <th>Amount</th>
                <th>Payment</th>
                <th>Status</th>
                <th>Created By</th>
            </tr>
        </thead>
        <tbody>
            @foreach($reportData as $expense)
            <tr>
                <td>#{{ $expense->id }}</td>
                <td>{{ $expense->expense_date->format('M d, Y') }}</td>
                <td>{{ $expense->title }}</td>
                <td>{{ $expense->category?->name ?? 'N/A' }}</td>
                <td>{{ $expense->branch?->name ?? 'N/A' }}</td>
                <td class="currency">{{ config('settings.currency_symbol', 'AED') }}{{ number_format($expense->amount, 2) }}</td>
                <td>{{ ucfirst(str_replace('_', ' ', $expense->payment_method ?? 'N/A')) }}</td>
                <td>
                    <span class="badge {{ $expense->is_approved ? 'badge-approved' : 'badge-pending' }}">
                        {{ $expense->is_approved ? 'Approved' : 'Pending' }}
                    </span>
                </td>
                <td>{{ $expense->user?->name ?? 'N/A' }}</td>
            </tr>
            @endforeach
            <tr style="background-color: #e9ecef; font-weight: bold;">
                <td colspan="5" style="text-align: right; padding: 12px 8px;">Total:</td>
                <td class="currency" style="font-size: 14px;">{{ config('settings.currency_symbol', 'AED') }}{{ number_format($insights['total_amount'] ?? 0, 2) }}</td>
                <td colspan="3"></td>
            </tr>
        </tbody>
    </table>
    @else
    <div style="text-align: center; padding: 40px; color: #666;">
        <p>No expense data found for the selected criteria.</p>
    </div>
    @endif

    <div class="footer">
        <p>This report was generated on {{ now()->format('M d, Y h:i:s A') }}</p>
        <p>{{ config('settings.site_description', 'Laravel Easy POS System') }}</p>
    </div>
</body>
</html>

