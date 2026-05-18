<?php

namespace App\Http\Controllers;

use App\Models\History;
use App\Models\PurchaseRequest;
use App\Models\QuotationPeriod;
use App\Models\Rfq;
use Illuminate\Http\Request;

class RfqController extends Controller
{
    public function create()
    {
        $requests = PurchaseRequest::where('status', 'in process')
            ->whereDoesntHave('rfqs', function ($query) {
                $query->whereIn('status', ['open', 'closed']);
            })
            ->latest()
            ->get();

        return view('rfqs.create', compact('requests'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'purchase_request_id' => ['required', 'exists:purchase_requests,id'],
            'note'                => ['nullable', 'string'],
        ]);

        // Auto-generate RFQ number: RFQ-YYYY-MMDD-NNN
        $todayCount = Rfq::whereDate('created_at', today())->count() + 1;
        $rfqNumber  = 'RFQ-' . now()->format('Y') . '-' . now()->format('md') . '-' . str_pad($todayCount, 3, '0', STR_PAD_LEFT);

        $rfq = Rfq::create([
            'purchase_request_id' => $data['purchase_request_id'],
            'rfq_number'          => $rfqNumber,
            'note'                => $data['note'] ?? null,
            'status'              => 'open',
            'is_sent_to_user'     => false,
            'opened_at'           => now(),
        ]);

        QuotationPeriod::create([
            'rfq_id'     => $rfq->id,
            'round'      => 1,
            'start_date' => now()->toDateString(),
            'end_date'   => now()->addDays(7)->toDateString(),
            'status'     => 'open',
        ]);

        $rfq->purchaseRequest->update(['status' => 'rfq_open']);

        History::create([
            'user_id'            => auth()->id(),
            'vendor_id'          => null,
            'rfq_id'             => $rfq->id,
            'vendor_selection_id' => null,
            'action'             => 'RFQ Created',
            'transaction_status' => 'completed',
            'notes'              => 'RFQ ' . $rfqNumber . ' dibuat untuk PR ' . $rfq->purchaseRequest->document_number,
            'action_date'        => now(),
        ]);

        return redirect()->route('vendors.select', $rfq)
            ->with('success', 'RFQ ' . $rfqNumber . ' berhasil dibuat. Silakan pilih vendor.');
    }
}