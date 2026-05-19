<?php
 
namespace App\Http\Controllers;
 
use App\Models\History;
use App\Models\PurchaseRequest;
use App\Models\Rfq;
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
 
        $recentHistory = History::with(['vendor', 'rfq.purchaseRequest', 'vendorSelection'])
            ->where('user_id', $userId)
            ->whereNotNull('vendor_id')
            ->latest('action_date')
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
 
        $recentHistory = History::with(['vendor', 'rfq.purchaseRequest', 'vendorSelection'])
            ->whereNotNull('vendor_id')
            ->latest('action_date')
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