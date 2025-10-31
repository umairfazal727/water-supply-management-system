<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Transport Ledger (Detailed)</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 10px; }
        h1 { font-size: 18px; margin: 0; }
        .muted { color: #666; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th { background: #222; color: #fff; padding: 6px; text-align: left; font-size: 10px; }
        td { border-bottom: 1px solid #ddd; padding: 5px; font-size: 10px; }
        tfoot td { font-weight: bold; background: #f4f4f4; }
    </style>
</head>
<body>
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
                    <td>{{ $d->order?->name ?? 'N/A' }}</td>
                    <td>{{ $d->customer_site ?? 'N/A' }}</td>
                    <td>{{ $d->customer_location ?? 'N/A' }}</td>
                    <td>{{ $d->trip_size }} gal</td>
                    <td>{{ config('settings.currency_symbol', 'AED') }}{{ number_format($d->total_amount, 2) }}</td>
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


