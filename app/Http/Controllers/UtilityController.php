<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Customer;
use App\Models\Ledger;
use Mpdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class UtilityController extends Controller
{

    public function __construct()
    {
         
        $this->middleware('auth');
    }


    public function print($order_id)
    {   

        $order = Order::with('items')->findOrFail( $order_id );
        $currency_symbol = config('settings.currency_symbol');
        $site_name = config('settings.site_name');
        $site_description = config('settings.site_description');

        $data = [
            'invoiceNumber' => $order->id,
            'date' => $order->created_at->format('M d, Y'),
            'time' => $order->created_at->format('h:i:s A'),
            'items' => $order->items,
            'order' => $order,
            'currency_symbol' => $currency_symbol,
            'site_name' => $site_name,
            'site_description' => $site_description
        ];

        $html = view('invoices.3-invoice', $data)->render();

        $defaultConfig = (new \Mpdf\Config\ConfigVariables())->getDefaults();
        $fontDirs = $defaultConfig['fontDir'];
        
        $defaultFontConfig = (new \Mpdf\Config\FontVariables())->getDefaults();
        $fontData = $defaultFontConfig['fontdata'];
        
        $mpdf = new Mpdf\Mpdf([
            'fontDir' => array_merge($fontDirs, [
                public_path(''),
            ]),
            'fontdata' => $fontData + [  
                'terminus' => [
                    'R' => 'Terminus.ttf',
                ]
            ],
            'default_font' => 'terminus',
            'mode' => 'utf-8',
            'shrink_tables_to_fit' => 0,
            'format' => [75, 200],  
            'orientation' => 'P',
            'margin_left' => 3,
            'margin_right' => 3,
            'margin_top' => 3,
            'margin_bottom' => 3,
        ]);

        $mpdf->WriteHTML($html);
        return $mpdf->Output('invoice-'.$order_id.'.pdf', 'I');
    }

    public function downloadStatement($customer_id, Request $request)
    {
        $customer = \App\Models\Customer::findOrFail($customer_id);
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->endOfMonth()->format('Y-m-d'));
        
        // Get orders with payment_type = 'on_account' (pending/deferred)
        $orders = Order::where('customer_id', $customer_id)
            ->where('payment_type', 'on_account')
            ->whereBetween('order_date', [$startDate, $endDate])
            ->orderBy('order_date', 'desc')
            ->get();
        
        $data = [
            'customer' => $customer,
            'orders' => $orders,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'totalAmount' => $orders->sum('total_price'),
        ];

        $html = view('invoices.statement', $data)->render();

        $mpdf = new Mpdf\Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'orientation' => 'P',
        ]);

        $mpdf->WriteHTML($html);
        return $mpdf->Output('statement-'.$customer_id.'.pdf', 'I');
    }

    public function downloadInvoice($order_id)
    {
        $order = Order::with(['customer', 'branch'])->findOrFail($order_id);
        
        $data = [
            'order' => $order,
        ];

        $html = view('invoices.invoice', $data)->render();

        $mpdf = new Mpdf\Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'orientation' => 'P',
        ]);

        $mpdf->WriteHTML($html);
        return $mpdf->Output('invoice-'.$order_id.'.pdf', 'I');
    }

    public function downloadStatementByCompany($company_name, Request $request)
    {
        $company_name = urldecode($company_name);
        $company_name = trim($company_name);
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->endOfMonth()->format('Y-m-d'));
        
        // Debug: Log the company name being searched
        Log::info('Searching for company: ' . $company_name);
        
        // Get all customer IDs with this company name (trim and case-insensitive match)
        $customerIds = Customer::whereRaw('LOWER(TRIM(company_name)) = LOWER(?)', [$company_name])->pluck('id');
        
        // Debug: Log found customer IDs
        Log::info('Found customer IDs: ' . $customerIds->implode(', '));
        
        if ($customerIds->isEmpty()) {
            // Debug: Show what companies exist in database
            $existingCompanies = Customer::whereNotNull('company_name')
                ->where('company_name', '!=', '')
                ->pluck('company_name')
                ->unique()
                ->sort();
            
            abort(404, 'No customers found with company name: "' . $company_name . '". Available companies: ' . $existingCompanies->implode(', '));
        }
        
        // Get orders with payment_type = 'credit'
        $orders = Order::whereIn('customer_id', $customerIds)
            ->where('payment_type', 'credit')
            ->whereBetween('order_date', [$startDate, $endDate])
            ->orderBy('order_date', 'desc')
            ->get();
        
        // Debug: Log order count
        Log::info('Found orders count: ' . $orders->count());
        
        $data = [
            'company_name' => $company_name,
            'orders' => $orders,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'totalAmount' => $orders->sum('total_price'),
        ];

        $html = view('invoices.company-statement', $data)->render();

        $mpdf = new Mpdf\Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'orientation' => 'P',
        ]);

        $mpdf->WriteHTML($html);
        return $mpdf->Output('statement-'.str_replace(' ', '-', $company_name).'.pdf', 'I');
    }

    public function downloadLedger(Request $request)
    {
        $customer_id = $request->get('customer_id');
        $from_date = $request->get('from');
        $to_date = $request->get('to');
        
        $customer = Customer::findOrFail($customer_id);
        
        $query = Ledger::where('customer_id', $customer_id)
                      ->with('order')
                      ->orderBy('transaction_date', 'asc')
                      ->orderBy('id', 'asc');

        if ($from_date) {
            $query->whereDate('transaction_date', '>=', $from_date);
        }

        if ($to_date) {
            $query->whereDate('transaction_date', '<=', $to_date);
        }

        $ledgerEntries = $query->get();
        
        // Calculate totals
        $totalDebit = $ledgerEntries->sum('debit_amount');
        $totalCredit = $ledgerEntries->sum('credit_amount');

        // Get previous balance (balance before the filtered period)
        if ($from_date) {
            $previousEntry = Ledger::where('customer_id', $customer_id)
                                 ->whereDate('transaction_date', '<', $from_date)
                                 ->orderBy('transaction_date', 'desc')
                                 ->orderBy('id', 'desc')
                                 ->first();
            $previousBalance = $previousEntry ? $previousEntry->balance : $customer->opening_balance;
        } else {
            $previousBalance = $customer->opening_balance;
        }

        $finalBalance = $previousBalance + $totalDebit - $totalCredit;
        
        $data = [
            'customer' => $customer,
            'ledgerEntries' => $ledgerEntries,
            'from_date' => $from_date,
            'to_date' => $to_date,
            'previousBalance' => $previousBalance,
            'totalDebit' => $totalDebit,
            'totalCredit' => $totalCredit,
            'finalBalance' => $finalBalance,
        ];

        $html = view('invoices.ledger', $data)->render();

        $mpdf = new Mpdf\Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'orientation' => 'L', // Landscape for better table display
        ]);

        $mpdf->WriteHTML($html);
        return $mpdf->Output('ledger-'.$customer_id.'.pdf', 'I');
    }
}
