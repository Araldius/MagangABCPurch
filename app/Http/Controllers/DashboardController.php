<?php

namespace App\Http\Controllers;

use App\Models\History;
use App\Models\PurchaseRequest;
use App\Models\Rfq;
use App\Models\VendorSelection;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Status yang valid di sistem:
     *   awaiting_approval  — PR baru dibuat, menunggu persetujuan
     *   in_process         — Sudah disetujui, sedang proses RFQ / vendor
     *   approved           — Vendor sudah dipilih / manager approved
     *   completed          — Selesai / barang diterima
     */
    const STATUS_AWAITING  = 'awaiting_approval';
    const STATUS_IN_PROCESS = 'in_process';
    const STATUS_APPROVED  = 'approved';
    const STATUS_COMPLETED = 'completed';

    public function index()
    {
        $user = Auth::user();

        if ($user->role === 'purchasing') {
            return $this->purchasingDashboard();
        }

        return $this->userDashboard();
    }

    // ── User (requester) dashboard ────────────────────────────────────────────
    private function userDashboard()
    {
        $userId = Auth::id();

        $requests = PurchaseRequest::with(['items', 'user'])
            ->where('user_id', $userId)
            ->latest()
            ->get();

        // Active = semua yang belum completed
        $activePrs = $requests
            ->where('status', '!=', self::STATUS_COMPLETED)
            ->count();

        // Awaiting approval
        $awaitingApproval = $requests
            ->where('status', self::STATUS_AWAITING)
            ->count();

        // In process (RFQ/vendor sedang berjalan)
        $inProcess = $requests
            ->where('status', self::STATUS_IN_PROCESS)
            ->count();

        // Completed this month
        $completedMonth = $requests
            ->where('status', self::STATUS_COMPLETED)
            ->filter(fn($r) =>
                $r->updated_at->month === now()->month &&
                $r->updated_at->year  === now()->year
            )
            ->count();

        // Recent vendor history (dari VendorSelection, bukan History rows)
        $recentHistory = History::with(['vendor', 'rfq.purchaseRequest', 'vendorSelection'])
            ->where('user_id', $userId)
            ->whereNotNull('vendor_id')
            ->latest('action_date')
            ->limit(5)
            ->get();

        return view('dashboard.user', compact(
            'requests',
            'activePrs',
            'awaitingApproval',
            'inProcess',
            'completedMonth',
            'recentHistory'
        ));
    }

    // ── Purchasing / Admin dashboard ──────────────────────────────────────────
    private function purchasingDashboard()
    {
        // Total semua PR
        $totalRequests = PurchaseRequest::count();

        // Menunggu persetujuan
        $pendingRequests = PurchaseRequest::where('status', self::STATUS_AWAITING)->count();

        // Sedang diproses
        $openRfqs = PurchaseRequest::where('status', self::STATUS_IN_PROCESS)->count();

        // Vendor sudah dipilih (approved)
        $completedQuotations = PurchaseRequest::where('status', self::STATUS_APPROVED)->count();

        // Selesai bulan ini
        $completedMonth = PurchaseRequest::where('status', self::STATUS_COMPLETED)
            ->whereMonth('updated_at', now()->month)
            ->whereYear('updated_at', now()->year)
            ->count();

        // 10 PR terbaru (semua dept.)
        $latestRequests = PurchaseRequest::with(['user', 'items'])
            ->latest()
            ->limit(10)
            ->get();

        // Aktivitas vendor terkini
        $recentHistory = History::with(['vendor', 'rfq.purchaseRequest', 'vendorSelection'])
            ->whereNotNull('vendor_id')
            ->latest('action_date')
            ->limit(5)
            ->get();

        return view('dashboard.admin', compact(
            'totalRequests',
            'pendingRequests',
            'openRfqs',
            'completedQuotations',
            'completedMonth',
            'latestRequests',
            'recentHistory'
        ));
    }
}