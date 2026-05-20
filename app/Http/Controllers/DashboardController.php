<?php
 
namespace App\Http\Controllers;
 
use App\Models\PurchaseRequest;
use App\Models\VendorSelection;
use Illuminate\Support\Facades\Auth;
 
class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        return $user->role === 'purchasing'
            ? $this->purchasingDashboard()
            : $this->userDashboard();
    }
 
    private function userDashboard()
    {
        $userId = Auth::id();
 
        $requests = PurchaseRequest::with(['items', 'user'])
            ->where('user_id', $userId)
            ->latest()
            ->get();
 
        $activePrs        = $requests->where('status', '!=', 'completed')->count();
        $awaitingApproval = $requests->where('status', 'awaiting_approval')->count();
        $inProcess        = $requests->where('status', 'in_process')->count();
        $completedMonth   = $requests->where('status', 'completed')
            ->filter(fn($r) => $r->updated_at->month === now()->month
                            && $r->updated_at->year  === now()->year)
            ->count();
 
        // Ubah: Hanya tampilkan PR yang sudah Completed dan memiliki Vendor Selection
        $recentHistory = VendorSelection::with(['vendor', 'rfq.purchaseRequest', 'selectionItems'])
            ->whereHas('rfq.purchaseRequest', function($q) use ($userId) {
                $q->where('user_id', $userId)->where('status', 'completed');
            })
            ->latest('decided_at')
            ->limit(5)
            ->get();
 
        return view('dashboard.user', compact(
            'requests', 'activePrs', 'awaitingApproval', 'inProcess',
            'completedMonth', 'recentHistory'
        ));
    }
 
    private function purchasingDashboard()
    {
        $totalRequests       = PurchaseRequest::count();
        $pendingRequests     = PurchaseRequest::where('status', 'awaiting_approval')->count();
        $openRfqs            = PurchaseRequest::where('status', 'in_process')->count();
        $completedQuotations = PurchaseRequest::where('status', 'approved')->count();
        $completedMonth      = PurchaseRequest::where('status', 'completed')
            ->whereMonth('updated_at', now()->month)
            ->whereYear('updated_at',  now()->year)
            ->count();
 
        $latestRequests = PurchaseRequest::with(['user', 'items'])->latest()->limit(10)->get();
 
        // Ubah: Hanya tampilkan PR yang sudah Completed dan memiliki Vendor Selection
        $recentHistory = VendorSelection::with(['vendor', 'rfq.purchaseRequest', 'selectionItems'])
            ->whereHas('rfq.purchaseRequest', function($q) {
                $q->where('status', 'completed');
            })
            ->latest('decided_at')
            ->limit(5)
            ->get();
 
        /* Re-use user dashboard view with same var names */
        $requests         = $latestRequests;
        $activePrs        = $totalRequests;
        $awaitingApproval = $pendingRequests;
        $inProcess        = $openRfqs;
 
        return view('dashboard.user', compact(
            'requests', 'activePrs', 'awaitingApproval', 'inProcess',
            'completedMonth', 'recentHistory'
        ));
    }
}