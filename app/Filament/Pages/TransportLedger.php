<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\Delivery;
use App\Models\DeliveryCustomer;

class TransportLedger extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static ?string $navigationLabel = 'Transport Ledger';
    protected static ?string $navigationGroup = 'Water Transport';
    protected static ?int $navigationSort = 2;

    protected static string $view = 'filament.pages.transport-ledger';

    public $startDate;
    public $endDate;
    public $deliveryCustomerId;

    public $entries;
    public $summary = [];

    public function mount()
    {
        $this->startDate = now()->startOfDay();
        $this->endDate = now()->endOfDay();
        $this->entries = collect();
    }

    protected function getListeners()
    {
        return [
            'transportLedgerGenerate' => 'handleGenerate',
        ];
    }

    public function handleGenerate($filters)
    {
        $this->startDate = \Carbon\Carbon::parse($filters['startDate'])->startOfDay();
        $this->endDate = \Carbon\Carbon::parse($filters['endDate'])->endOfDay();
        $this->deliveryCustomerId = $filters['deliveryCustomerId'] ?? null;
        $this->generate();
    }

    public function generate()
    {
        $query = Delivery::with(['deliveryCustomer', 'vehicle', 'driver', 'branch'])
            ->whereBetween('delivery_date', [$this->startDate->toDateString(), $this->endDate->toDateString()]);

        if ($this->deliveryCustomerId) {
            $query->where('delivery_customer_id', $this->deliveryCustomerId);
        }

        $this->entries = $query->orderBy('delivery_date', 'asc')->orderBy('id', 'asc')->get();

        $this->summary = [
            'count' => $this->entries->count(),
            'total_amount' => $this->entries->sum('total_amount'),
        ];
    }

    public function downloadSimple()
    {
        if (empty($this->entries)) {
            $this->generate();
        }
        $html = view('invoices.transport-ledger-simple', [
            'entries' => $this->entries,
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
            'deliveryCustomer' => $this->deliveryCustomerId ? DeliveryCustomer::find($this->deliveryCustomerId) : null,
            'summary' => $this->summary,
        ])->render();

        $mpdf = new \Mpdf\Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'orientation' => 'P',
            'margin_left' => 10,
            'margin_right' => 10,
            'margin_top' => 10,
            'margin_bottom' => 10,
        ]);
        $mpdf->WriteHTML($html);
        $filename = 'transport_ledger_simple_' . $this->startDate->format('Y-m-d') . '_to_' . $this->endDate->format('Y-m-d') . '.pdf';
        return response()->streamDownload(function () use ($mpdf) { echo $mpdf->Output('', 'S'); }, $filename);
    }

    public function downloadDetailed()
    {
        if (empty($this->entries)) {
            $this->generate();
        }
        $html = view('invoices.transport-ledger-detailed', [
            'entries' => $this->entries,
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
            'deliveryCustomer' => $this->deliveryCustomerId ? DeliveryCustomer::find($this->deliveryCustomerId) : null,
            'summary' => $this->summary,
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
        $filename = 'transport_ledger_detailed_' . $this->startDate->format('Y-m-d') . '_to_' . $this->endDate->format('Y-m-d') . '.pdf';
        return response()->streamDownload(function () use ($mpdf) { echo $mpdf->Output('', 'S'); }, $filename);
    }
}


