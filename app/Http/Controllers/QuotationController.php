<?php

namespace App\Http\Controllers;

use App\Models\History;
use App\Models\Quotation;
use App\Models\QuotationDetail;
use App\Models\QuotationSummary;
use App\Models\Rfq;
use App\Models\SelectionItem;
use App\Models\VendorSelection;
use Illuminate\Http\Request;

class QuotationController extends Controller
{
    public function status(Rfq $rfq)
    {
        $rfq->load(['purchaseRequest', 'vendor', 'vendorQuotations.vendor', 'quotationPeriods', 'quotation.quotationDetails']);

        return view('quotations.status', compact('rfq'));
    }

    public function updateStatus(Request $request, Rfq $rfq)
    {
        $data = $request->validate([
            'status' => ['required', 'in:open,closed'],
            'closed_at' => ['nullable', 'date'],
            'note' => ['nullable', 'string'],
        ]);

        $rfq->status = $data['status'];
        $rfq->closed_at = $data['status'] === 'closed' ? ($data['closed_at'] ?? now()) : null;
        $rfq->note = $data['note'] ?? $rfq->note;
        $rfq->save();

        if ($data['status'] === 'closed') {
            $period = $rfq->quotationPeriods()->latest('round')->first();
            if ($period) {
                $period->status = 'closed';
                $period->save();
            }
        }

        History::create([
            'user_id' => auth()->id(),
            'vendor_id' => $rfq->vendor_id,
            'rfq_id' => $rfq->id,
            'vendor_selection_id' => null,
            'action' => 'RFQ Status Updated',
            'transaction_status' => 'completed',
            'notes' => 'RFQ status set to ' . $rfq->status,
            'action_date' => now(),
        ]);

        return redirect()->route('quotations.final', $rfq)->with('success', 'Status RFQ berhasil diperbarui.');
    }

    public function final(Rfq $rfq)
    {
        $quotation = $rfq->quotation;
        $items = $rfq->purchaseRequest->items;
        $details = $quotation ? $quotation->quotationDetails->keyBy('purchase_request_item_id') : collect();

        return view('quotations.final', compact('rfq', 'quotation', 'items', 'details'));
    }

    public function storeFinal(Request $request, Rfq $rfq)
    {
        $data = $request->validate([
            'total_price' => ['required', 'numeric', 'min:0'],
            'note' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.purchase_request_item_id' => ['required', 'exists:purchase_request_items,id'],
            'items.*.offered_price_per_item' => ['required', 'numeric', 'min:0'],
            'items.*.offered_quantity' => ['required', 'integer', 'min:1'],
        ]);

        $quotation = Quotation::updateOrCreate([
            'rfq_id' => $rfq->id,
        ], [
            'vendor_id' => $rfq->vendor_id,
            'total_price' => $data['total_price'],
            'note' => $data['note'] ?? null,
            'status' => 'finalized',
        ]);

        QuotationDetail::where('quotation_id', $quotation->id)->delete();
        QuotationSummary::where('rfq_id', $rfq->id)->delete();

        foreach ($data['items'] as $itemData) {
            $detail = QuotationDetail::create([
                'quotation_id' => $quotation->id,
                'purchase_request_item_id' => $itemData['purchase_request_item_id'],
                'offered_price_per_item' => $itemData['offered_price_per_item'],
                'offered_quantity' => $itemData['offered_quantity'],
            ]);

            QuotationSummary::create([
                'rfq_id' => $rfq->id,
                'quotation_detail_id' => $detail->id,
                'is_sent_to_user' => true,
                'sent_to_user_at' => now(),
            ]);
        }

        $selection = VendorSelection::updateOrCreate([
            'rfq_id' => $rfq->id,
        ], [
            'vendor_id' => $rfq->vendor_id,
            'quotation_id' => $quotation->id,
            'decision_notes' => 'Final quotation selected and approved.',
            'decided_at' => now(),
        ]);

        SelectionItem::where('vendor_selection_id', $selection->id)->delete();

        $summaryIds = QuotationSummary::where('rfq_id', $rfq->id)
            ->pluck('id', 'quotation_detail_id')
            ->toArray();

        foreach ($quotation->quotationDetails as $detail) {
            SelectionItem::create([
                'vendor_selection_id' => $selection->id,
                'quotation_summary_id' => $summaryIds[$detail->id] ?? null,
                'final_price_per_item' => $detail->offered_price_per_item,
                'final_quantity' => $detail->offered_quantity,
                'notes' => 'Item included in final vendor selection.',
            ]);
        }

        $rfq->status = 'closed';
        $rfq->save();
        $rfq->purchaseRequest->update(['status' => 'completed']);

        History::create([
            'user_id' => auth()->id(),
            'vendor_id' => $rfq->vendor_id,
            'rfq_id' => $rfq->id,
            'vendor_selection_id' => $selection->id,
            'action' => 'Quotation Finalized',
            'transaction_status' => 'completed',
            'notes' => 'Final quotation saved for RFQ ' . $rfq->id,
            'action_date' => now(),
        ]);

        return redirect()->route('dashboard')->with('success', 'Quotation final berhasil disimpan.');
    }
}
