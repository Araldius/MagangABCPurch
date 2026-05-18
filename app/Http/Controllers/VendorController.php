<?php

namespace App\Http\Controllers;

use App\Models\History;
use App\Models\Rfq;
use App\Models\Vendor;
use App\Models\VendorQuotation;
use Illuminate\Http\Request;

class VendorController extends Controller
{
    /**
     * Vendor list + open RFQs (vendors.list route).
     */
    public function index()
    {
        $vendors = Vendor::with(['rfqs', 'vendorSelections'])->get();
        return view('vendors.index', compact('vendors'));
    }

    /**
     * Vendor selection form for a specific RFQ.
     */
    public function select(Rfq $rfq)
    {
        $rfq->load(['purchaseRequest.items', 'vendorQuotations.vendor']);
        $vendors = Vendor::where('status', 'active')->orWhereNull('status')->get();
        return view('vendors.select', compact('rfq', 'vendors'));
    }

    /**
     * Store vendor choice for an RFQ.
     */
    public function store(Request $request, Rfq $rfq)
    {
        $data = $request->validate([
            'vendor_id'       => ['nullable', 'exists:vendors,id'],
            'vendor_name'     => ['nullable', 'string', 'max:255'],
            'vendor_location' => ['nullable', 'string', 'max:255'],
            'vendor_contact'  => ['nullable', 'string', 'max:255'],
            'note'            => ['nullable', 'string'],
        ]);

        if (!empty($data['vendor_name'])) {
            $vendor = Vendor::create([
                'name'     => $data['vendor_name'],
                'location' => $data['vendor_location'] ?? null,
                'contact'  => $data['vendor_contact'] ?? null,
                'status'   => 'active',
            ]);
        } else {
            $vendor = Vendor::find($data['vendor_id']);
        }

        if (!$vendor) {
            return back()->withErrors(['vendor_id' => 'Pilih vendor yang valid atau tambahkan vendor baru.']);
        }

        $rfq->vendor_id = $vendor->id;
        $rfq->note      = $data['note'] ?? $rfq->note;
        $rfq->save();

        VendorQuotation::create([
            'rfq_id'       => $rfq->id,
            'vendor_id'    => $vendor->id,
            'quotation_file' => null,
            'notes'        => $data['note'] ?? 'Vendor selected for RFQ.',
            'status'       => 'submitted',
            'submitted_at' => now(),
        ]);

        History::create([
            'user_id'             => auth()->id(),
            'vendor_id'           => $vendor->id,
            'rfq_id'              => $rfq->id,
            'vendor_selection_id' => null,
            'action'              => 'Vendor Selected',
            'transaction_status'  => 'completed',
            'notes'               => 'Vendor ' . $vendor->name . ' dipilih untuk ' . ($rfq->rfq_number ?? 'RFQ-#'.$rfq->id),
            'action_date'         => now(),
        ]);

        return redirect()->route('quotations.status', $rfq)
            ->with('success', 'Vendor ' . $vendor->name . ' berhasil dipilih untuk RFQ ini.');
    }
}