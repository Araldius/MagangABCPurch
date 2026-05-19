<?php
 
namespace App\Http\Controllers;
 
use App\Models\PurchaseRequest;
use App\Models\SelectionItem;
use App\Models\VendorSelection;
use Illuminate\Support\Facades\Auth;
 
class HistoryController extends Controller
{
    /**
     * Procurement History.
     *
     * Fix "double items" bug: loop per VendorSelection (1 completed transaction),
     * NOT per History row (which has many rows per PR).
     *
     * Fix "total value = 0" bug: sum final_price_per_item × final_quantity
     * from SelectionItems, which are the actual stored prices.
     */
    public function index()
    {
        $user = Auth::user();
 
        $query = VendorSelection::with([
                'rfq.purchaseRequest.items',
                'vendor',
                'selectionItems',
            ])
            ->latest('decided_at');
 
        if ($user->role !== 'purchasing') {
            $query->whereHas('rfq.purchaseRequest', fn($q) => $q->where('user_id', $user->id));
        }
 
        $selections = $query->get();
 
        /* Map to flat display objects — one row per VendorSelection */
        $records = $selections->map(function ($sel) {
            $rfq    = $sel->rfq;
            $pr     = optional($rfq)->purchaseRequest;
            $vendor = $sel->vendor;
 
            /* Total value: sum of (final_price × final_qty) across SelectionItems */
            $totalValue = $sel->selectionItems->sum(
                fn($si) => ($si->final_price_per_item ?? 0) * ($si->final_quantity ?? 0)
            );
 
            /* Lead time: days from PR creation to vendor decided_at */
            $leadDays = ($pr && $sel->decided_at)
                ? (int) \Carbon\Carbon::parse($sel->decided_at)->diffInDays($pr->created_at)
                : null;
 
            return (object) [
                'doc_number'  => optional($pr)->document_number ?? '—',
                'vendor_name' => optional($vendor)->name ?? optional($vendor)->vendor_name ?? '—',
                'vendor_city' => optional($vendor)->location ?? '',
                'department'  => optional($pr)->department ?? '—',
                'items'       => optional($pr)->items ?? collect(),
                'total_value' => $totalValue,
                'lead_days'   => $leadDays,
                'status'      => optional($pr)->status ?? 'completed',
                'decided_at'  => $sel->decided_at,
            ];
        });
 
        /* Stat cards */
        $vendorsUsed  = $records->pluck('vendor_name')->unique()->filter()->count();
        $totalValue   = $records->sum('total_value');
        $prsCompleted = PurchaseRequest::where('status', 'completed')
            ->when($user->role !== 'purchasing', fn($q) => $q->where('user_id', $user->id))
            ->whereYear('updated_at', now()->year)
            ->count();
        $avgLeadDays  = round($records->filter(fn($r) => $r->lead_days !== null)->avg('lead_days') ?? 0);
        $departments  = $records->pluck('department')->unique()->filter()->values();
 
        return view('history.index', compact(
            'records', 'vendorsUsed', 'totalValue',
            'prsCompleted', 'avgLeadDays', 'departments'
        ));
    }
}