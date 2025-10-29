<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Invoice Statement</title>
<style>
    body {
        font-family: Arial, sans-serif;
        background: #fff;
        margin: 0;
        padding: 20px;
        color: #000;
    }
    .invoice-container {
        max-width: 800px;
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
    .details .red {
        color: red;
        font-weight: bold;
    }

    .table-section {
        margin-top: 20px;
    }
    table {
        width: 100%;
        border-collapse: collapse;
        font-size: 14px;
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

    .total {
        text-align: right;
        margin-top: 15px;
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

    /* Responsive */
    @media (max-width: 600px) {
        .header {
            flex-direction: column;
            text-align: center;
        }
        .company-info {
            text-align: center;
        }
        .details td {
            display: block;
            width: 100%;
        }
    }
</style>
</head>
<body>

<div class="invoice-container">
    <!-- Header Section -->
    <div class="header">
        <img src="{{asset('invoice-img/logo.png')}}" alt="Company Logo">
        <div class="company-info">
            <h2>ريم الفلج لتجارة المياه المخصصة لاعمال البناء</h2>
            <h2>REEM AL FALAJ CONST. WATER TR</h2>
            <p>Salja Industrial Area, Sharjah - U.A.E.</p>
            <p>050 8426001 | 055 2496358 | 055 7466868 | 052 3943958</p>
        </div>
    </div>

    <!-- Statement Title -->
    <div class="statement-title">STATEMENT</div>

    <!-- Details -->
    <table class="details">
        <tr>
            <td class="label">TRN NO:</td>
            <td>100614240800003</td>
            <td class="label">DATE:</td>
            <td>{{ date('d/m/Y') }}</td>
        </tr>
        <tr>
            <td class="label">MOBILE NUMBER:</td>
            <td>{{ $customer->phone ?? 'N/A' }}</td>
            <td class="label">CUSTOMER:</td>
            <td>{{ $customer->company_name ?: $customer->first_name . ' ' . $customer->last_name }}</td>
        </tr>
    </table>

    <!-- Invoice Table -->
    <div class="table-section">
        <table>
            <thead>
                <tr>
                    <th>S.NO</th>
                    <th>INVOICE NUMBER</th>
                    <th>INVOICE DATE</th>
                    <th>INVOICE AMOUNT</th>
                    <th>CURRENCY</th>
                    <th>STATUS</th>
                </tr>
            </thead>
            <tbody>
                @foreach($orders as $index => $order)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ str_pad($order->id, 3, '0', STR_PAD_LEFT) }}</td>
                    <td>{{ \Carbon\Carbon::parse($order->order_date)->format('M-Y') }}</td>
                    <td>{{ number_format($order->total_price, 2) }}</td>
                    <td>AED</td>
                    <td>{{ $order->payment_type === 'credit' ? 'PENDING' : strtoupper($order->payment_type) }}</td>
                </tr>
                @endforeach
                <tr>
                    <td colspan="5" style="text-align: left;"><b>TOTAL AMOUNT: {{ strtoupper(\NumberFormatter::create('en', \NumberFormatter::SPELLOUT)->format($totalAmount)) }} DIRHAMS ONLY</b></td>
                    <td><b>{{ number_format($totalAmount, 2) }} AED</b></td>
                </tr>
            </tbody>
        </table>
    </div>



    <!-- Stamp -->
    {{-- <div class="stamp">
        <img src="{{asset('invoice-img/stamp.png')}}" alt="Company Stamp">
    </div> --}}
</div>

</body>
</html>
