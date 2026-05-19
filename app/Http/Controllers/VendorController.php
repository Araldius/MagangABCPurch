<?php
 
namespace App\Http\Controllers;
 
use App\Models\History;
use App\Models\PurchaseRequest;
use App\Models\Rfq;
use App\Models\SelectionItem;
use App\Models\Vendor;
use App\Models\VendorQuotation;
use App\Models\VendorSelection;
use Illuminate\Http\Request;
 
class VendorController extends Controller
{
    /**
     * Vendor Selection main page (step 1 — select PR).
     * Route: GET /vendor-selection  → vendors.list
     */
    public function index()
    {
        $user = auth()->user();

        // Mulai query untuk PR yang siap dipilih vendornya
        $query = PurchaseRequest::with(['items', 'rfqs.vendorQuotations.vendor'])
            ->whereIn('status', ['in_process', 'approved', 'awaiting_approval'])
            ->latest();

        // LOGIKA PEMBATASAN AKSES:
        // Jika yang login BUKAN purchasing, batasi agar hanya muncul PR buatannya sendiri
        if ($user->role !== 'purchasing') {
            $query->where('user_id', $user->id);
        }

        $prs = $query->get();
        $vendors = Vendor::all();
 
        return view('vendors.index', compact('prs', 'vendors'));
    }
 
    /**
     * Vendor selection for a specific RFQ (old flow — kept for compat).
     * Route: GET /vendor/select/{rfq}  → vendors.select
     */
    public function select(Rfq $rfq)
    {
        $rfq->load(['purchaseRequest.items', 'vendorQuotations.vendor']);
        $vendors = Vendor::all();
        return view('vendors.select', compact('rfq', 'vendors'));
    }
 
    /**
     * Store vendor selection items (new flow — called via POST from vendors.index).
     * Receives: pr_id, selections[] = [{vendor_id, item_id, price, qty, notes}]
     * Route: POST /vendor-selection/store  → vendors.store.selection
     */
    public function storeSelection(Request $request)
    {
        $request->validate([
            'pr_id'                    => ['required', 'exists:purchase_requests,id'],
            'selection_notes'          => ['nullable', 'string'],
            'selections'               => ['required', 'array', 'min:1'],
            'selections.*.vendor_id'   => ['required', 'exists:vendors,id'],
            'selections.*.item_id'     => ['required', 'exists:purchase_request_items,id'],
            'selections.*.unit_price'  => ['required', 'numeric', 'min:0'],
            'selections.*.quantity'    => ['required', 'integer', 'min:1'],
            'selections.*.notes'       => ['nullable', 'string'],
        ]);
 
        $pr = PurchaseRequest::findOrFail($request->pr_id);
 
        /* Get or create RFQ for this PR */
        $rfq = $pr->rfqs()->first();
        if (!$rfq) {
            $todayCount = Rfq::whereDate('created_at', today())->count() + 1;
            $rfq = Rfq::create([
                'purchase_request_id' => $pr->id,
                'rfq_number'          => 'RFQ-' . now()->format('Y-md') . '-' . str_pad($todayCount, 3, '0', STR_PAD_LEFT),
                'status'              => 'closed',
                'opened_at'           => now(),
            ]);
        }
 
        /* Group selections by vendor */
        $byVendor = collect($request->selections)->groupBy('vendor_id');
 
        foreach ($byVendor as $vendorId => $items) {
            $vendor = Vendor::find($vendorId);
 
            /* Upsert VendorSelection per vendor per RFQ */
            $sel = VendorSelection::updateOrCreate(
                ['rfq_id' => $rfq->id, 'vendor_id' => $vendorId],
                [
                    'quotation_id'   => null,
                    'decision_notes' => $request->selection_notes ?? '',
                    'decided_at'     => now(),
                ]
            );
 
            /* Delete old SelectionItems for this selection */
            $sel->selectionItems()->delete();
 
            $totalValue = 0;
            foreach ($items as $row) {
                $subtotal = $row['unit_price'] * $row['quantity'];
                $totalValue += $subtotal;
                SelectionItem::create([
                    'vendor_selection_id'  => $sel->id,
                    'quotation_summary_id' => null,
                    'final_price_per_item' => $row['unit_price'],
                    'final_quantity'       => $row['quantity'],
                    'notes'                => $row['notes'] ?? 'Selected',
                    /* Store item ref via notes for display */
                    'purchase_request_item_id' => $row['item_id'],
                ]);
            }
 
            History::create([
                'user_id'             => auth()->id(),
                'vendor_id'           => $vendorId,
                'rfq_id'              => $rfq->id,
                'vendor_selection_id' => $sel->id,
                'action'              => 'Vendor Selection Submitted',
                'transaction_status'  => 'completed',
                'notes'               => 'Vendor ' . $vendor->name . ' dipilih untuk '
                                       . count($items) . ' item pada PR ' . $pr->document_number,
                'action_date'         => now(),
                'total_value'         => $totalValue,
            ]);
        }
 
        /* Update PR status → approved */
        $pr->update(['status' => 'approved']);
 
        return response()->json([
            'success' => true,
            'message' => 'Vendor selection submitted for PR ' . $pr->document_number,
            'pr_number' => $pr->document_number,
            'notes'   => $request->selection_notes ?? '—',
        ]);
    }
 
    /**
     * Old store (kept for backward compat with vendors.store route).
     */
    public function store(Request $request, Rfq $rfq)
    {
        $data = $request->validate([
            'vendor_id'       => ['nullable', 'exists:vendors,id'],
            'vendor_name'     => ['nullable', 'string', 'max:255'],
            'vendor_location' => ['nullable', 'string', 'max:255'],
            'note'            => ['nullable', 'string'],
        ]);
 
        $vendor = !empty($data['vendor_name'])
            ? Vendor::create(['name' => $data['vendor_name'], 'location' => $data['vendor_location'] ?? null, 'status' => 'active'])
            : Vendor::find($data['vendor_id']);
 
        if (!$vendor) {
            return back()->withErrors(['vendor_id' => 'Pilih vendor yang valid.']);
        }
 
        $rfq->update(['vendor_id' => $vendor->id]);
        $rfq->purchaseRequest->update(['status' => 'in_process']);
 
        VendorQuotation::create([
            'rfq_id'       => $rfq->id,
            'vendor_id'    => $vendor->id,
            'notes'        => $data['note'] ?? '',
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
            'notes'               => 'Vendor ' . $vendor->name . ' dipilih.',
            'action_date'         => now(),
        ]);
 
        return redirect()->route('quotations.status', $rfq)
            ->with('success', 'Vendor ' . $vendor->name . ' dipilih.');
    }
}