<?php

namespace App\Http\Controllers;

use App\Models\History;
use App\Models\PurchaseRequest;
use App\Models\VendorSelection;
use Illuminate\Support\Facades\Auth;

class HistoryController extends Controller
{
    /**
     * Procurement History.
     *
     * Root cause of the "double items" bug:
     * The old code looped over History records. But one PR/RFQ can generate
     * multiple History rows (e.g. "PR Created", "Vendor Selected", etc.).
     * Each of those rows referenced the same PR items → the same items appeared
     * multiple times.
     *
     * Fix: derive one record per VendorSelection (= one completed procurement
     * transaction), then join back to History only for status/dates.
     */
    public function index()
    {
        $user = Auth::user();

        // ── Build records: one row per VendorSelection ────────────────────────
        // VendorSelection is the final step — it uniquely represents one
        // completed vendor+PR transaction.
        $query = VendorSelection::with([
                'rfq.purchaseRequest.user',
                'rfq.purchaseRequest.items',
                'vendor',
            ])
            ->latest('decided_at');

        // Non-purchasing users: only see PRs they submitted
        if ($user->role !== 'purchasing') {
            $query->whereHas('rfq.purchaseRequest', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        }

        $selections = $query->get();

        // Map to a flat list for the view
        $records = $selections->map(function ($sel) {
            $rfq  = $sel->rfq;
            $pr   = optional($rfq)->purchaseRequest;
            $vendor = $sel->vendor;

            // Approximate total value from quotation details if available
            $totalValue = 0;
            if ($rfq && $rfq->quotationSummaries) {
                // sum via SelectionItems if available
                $totalValue = $sel->selectionItems->sum(function ($si) {
                    return ($si->final_price_per_item ?? 0) * ($si->final_quantity ?? 0);
                });
            }

            // Lead time: days between PR submission and vendor selection
            $leadDays = null;
            if ($pr && $sel->decided_at) {
                $leadDays = \Carbon\Carbon::parse($sel->decided_at)->diffInDays($pr->created_at);
            }

            return (object) [
                'doc_number'     => optional($pr)->document_number ?? '—',
                'vendor_name'    => optional($vendor)->vendor_name ?? optional($vendor)->name ?? '—',
                'vendor_city'    => optional($vendor)->location ?? '',
                'department'     => optional($pr)->department ?? '—',
                'items'          => optional($pr)->items ?? collect(),
                'total_value'    => $totalValue,
                'lead_days'      => $leadDays,
                'status'         => optional($pr)->status ?? 'completed',
                'decided_at'     => $sel->decided_at,
            ];
        });

        // ── Stat cards ────────────────────────────────────────────────────────
        $vendorsUsed  = $records->pluck('vendor_name')->unique()->filter()->count();
        $totalValue   = $records->sum('total_value');
        $prsCompleted = PurchaseRequest::where('status', 'completed')
            ->when($user->role !== 'purchasing', fn($q) => $q->where('user_id', $user->id))
            ->whereYear('updated_at', now()->year)
            ->count();
        $avgLeadDays  = round($records->filter(fn($r) => $r->lead_days !== null)->avg('lead_days') ?? 0);

        // ── Dept filter dropdown ──────────────────────────────────────────────
        $departments = $records->pluck('department')->unique()->filter()->values();

        return view('history.index', compact(
            'records',
            'vendorsUsed',
            'totalValue',
            'prsCompleted',
            'avgLeadDays',
            'departments'
        ));
    }
}