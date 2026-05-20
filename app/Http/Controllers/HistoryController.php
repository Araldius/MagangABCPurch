<?php
 
namespace App\Http\Controllers;
 
use App\Models\PurchaseRequest;
use Illuminate\Support\Facades\Auth;
 
class HistoryController extends Controller
{
    public function index()
    {
        $user = Auth::user();
 
        // Tarik semua PR, bukan cuma VendorSelection
        $query = PurchaseRequest::with([
                'items',
                'rfqs.vendorSelections.vendor',
                'rfqs.vendorSelections.selectionItems'
            ])
            ->latest();
 
        if ($user->role !== 'purchasing') {
            $query->where('user_id', $user->id);
        }
 
        $prs = $query->get();
 
        // Mapping ke format yang dibutuhkan UI (termasuk memecah Split PO)
        $records = collect();
 
        foreach ($prs as $pr) {
            $hasSelection = false;
            
            if ($pr->status === 'completed') {
                foreach ($pr->rfqs as $rfq) {
                    foreach ($rfq->vendorSelections as $sel) {
                        $hasSelection = true;
                        $vendor = $sel->vendor;
                        $totalValue = $sel->selectionItems->sum(fn($si) => ($si->final_price_per_item ?? 0) * ($si->final_quantity ?? 0));
                        $leadDays = $sel->decided_at ? (int) \Carbon\Carbon::parse($sel->decided_at)->diffInDays($pr->created_at) : null;
 
                        $records->push((object) [
                            'doc_number'  => $pr->document_number,
                            'vendor_name' => optional($vendor)->name ?? optional($vendor)->vendor_name ?? '—',
                            'vendor_city' => optional($vendor)->location ?? '',
                            'department'  => $pr->department ?? '—',
                            'items'       => $pr->items,
                            'total_value' => $totalValue,
                            'lead_days'   => $leadDays,
                            'status'      => $pr->status,
                            'decided_at'  => $sel->decided_at,
                        ]);
                    }
                }
            }
 
            // Jika PR belum completed, atau karena alasan tertentu tidak ada Vendor Selection
            if (!$hasSelection) {
                $records->push((object) [
                    'doc_number'  => $pr->document_number,
                    'vendor_name' => '—',
                    'vendor_city' => '',
                    'department'  => $pr->department ?? '—',
                    'items'       => $pr->items,
                    'total_value' => 0,
                    'lead_days'   => null,
                    'status'      => $pr->status,
                    'decided_at'  => null,
                ]);
            }
        }
 
        $vendorsUsed  = $records->pluck('vendor_name')->reject(fn($v) => $v === '—')->unique()->count();
        $totalValue   = $records->sum('total_value');
        $prsCompleted = $prs->where('status', 'completed')->count(); // Dihitung dari $prs agar Split PO tidak terhitung ganda
        $avgLeadDays  = round($records->filter(fn($r) => $r->lead_days !== null)->avg('lead_days') ?? 0);
        $departments  = $records->pluck('department')->unique()->filter()->values();
 
        return view('history.index', compact(
            'records', 'vendorsUsed', 'totalValue',
            'prsCompleted', 'avgLeadDays', 'departments'
        ));
    }
}