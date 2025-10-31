<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Transport Ledger (Detailed)</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            font-size: 10px; 
            margin: 0;
            padding: 20px;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 2px solid #1b73b3;
            margin-bottom: 15px;
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
        h1 { font-size: 18px; margin: 0; }
        .muted { color: #666; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th { background: #1b73b3; color: #fff; padding: 6px; text-align: left; font-size: 10px; }
        td { border-bottom: 1px solid #ddd; padding: 5px; font-size: 10px; }
        tfoot td { font-weight: bold; background: #f4f4f4; }
    </style>
</head>
<body>
    <!-- Header Section -->
    <table class="header-table">
        <tr>
            <td class="header-logo">
                <img src="{{asset('invoice-img/logo.png')}}" style="height: 200px;  width: 200px;" alt="Company Logo">
            </td>
            <td class="company-info">
                <h2>ريم الفلج لتجارة المياه المخصصة لاعمال البناء</h2>
                <h2>REEM AL FALAJ CONST. WATER TR</h2>
                <p>Sajja Industrial Area, Sharjah - U.A.E.</p>
            </td>
            <td class="phone-info">
                <p>050 8426001</p>
                <p>050 2549368</p>
                <p>055 7466868</p>
                <p>052 9349368</p>
            </td>
        </tr>
    </table>

    <h1>Transport Ledger (Detailed)</h1>
    <div class="muted">Period: {{ $startDate->format('M d, Y') }} - {{ $endDate->format('M d, Y') }}</div>
    @if($deliveryCustomer)
        <div class="muted">Customer: {{ $deliveryCustomer->name }}{{ $deliveryCustomer->company_name ? ' - ' . $deliveryCustomer->company_name : '' }}</div>
    @endif

    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Delivery No</th>
                <th>Customer</th>
                <th>Vehicle</th>
                <th>Driver</th>
                <th>Site</th>
                <th>Location</th>
                <th>Trip Size</th>
                <th>Amount</th>
                <th>Payment Method</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($entries as $d)
                <tr>
                    <td>{{ $d->delivery_date?->format('M d, Y') }}</td>
                    <td>{{ $d->delivery_number }}</td>
                    <td>{{ $d->deliveryCustomer?->name }}</td>
                    <td>{{ $d->order?->vehicle_number ?? 'N/A' }}</td>
                    <td>{{ $d->order?->driver_name ?? 'N/A' }}</td>
                    <td>{{ $d->customer_site ?? 'N/A' }}</td>
                    <td>{{ $d->customer_location ?? 'N/A' }}</td>
                    <td>{{ $d->trip_size }} gal</td>
                    <td>{{ config('settings.currency_symbol', 'AED') }}{{ number_format($d->total_amount, 2) }}</td>
                    <td>{{ ucfirst(str_replace('_',' ', $d->payment_method)) }}</td>
                    <td>{{ ucfirst(str_replace('_',' ', $d->status)) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="8">Total</td>
                <td>{{ $summary['count'] ?? 0 }} trips</td>
                <td>{{ config('settings.currency_symbol', 'AED') }}{{ number_format($summary['total_amount'] ?? 0, 2) }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>
</body>
</html>


