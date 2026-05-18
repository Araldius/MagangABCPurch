<?php

namespace App\Http\Controllers;

use App\Models\History;
use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PurchaseRequestController extends Controller
{
    /**
     * PR List — purchasing role sees ALL PRs, others see only their own.
     */
    public function index()
    {
        $user = Auth::user();

        $query = PurchaseRequest::with(['items', 'user'])->latest();

        // Non-purchasing users: filter to own PRs only
        if ($user->role !== 'purchasing') {
            $query->where('user_id', $user->id);
        }

        $requests = $query->get();

        $isPurchasing = $user->role === 'purchasing';

        return view('purchase_requests.list', compact('requests', 'isPurchasing'));
    }

    /**
     * New PR form.
     */
    public function create()
    {
        return view('purchase_requests.create');
    }

    /**
     * Store — handles both goods and service item types.
     */
    public function store(Request $request)
    {
        $itemType = $request->input('item_type', 'goods');

        // Base validation
        $data = $request->validate([
            'title'      => ['required', 'string', 'max:255'],
            'department' => ['required', 'string', 'max:255'],
            'plant'      => ['required', 'string', 'max:255'],
            'need_date'  => ['required', 'date'],
            'note'       => ['nullable', 'string'],
            'item_type'  => ['required', 'in:goods,service'],
        ]);

        // Generate document number: PR-YYYY-MMDD-NNN
        $todayCount = PurchaseRequest::whereDate('created_at', today())->count() + 1;
        $docNumber  = 'PR-' . now()->format('Y') . '-' . now()->format('md') . '-' . str_pad($todayCount, 3, '0', STR_PAD_LEFT);

        $pr = PurchaseRequest::create([
            'user_id'         => Auth::id(),
            'document_number' => $docNumber,
            'title'           => $data['title'],
            'department'      => $data['department'],
            'plant'           => $data['plant'],
            'submission_date' => today(),
            'requested_date'  => $data['need_date'],
            'need_date'       => $data['need_date'],
            'note'            => $data['note'] ?? null,
            'status'          => 'in process',
        ]);

        // Save goods items
        if ($itemType === 'goods') {
            $request->validate([
                'items'               => ['required', 'array', 'min:1'],
                'items.*.name'        => ['required', 'string', 'max:255'],
                'items.*.quantity'    => ['required', 'integer', 'min:1'],
                'items.*.unit'        => ['required', 'string', 'max:100'],
                'items.*.specification' => ['nullable', 'string'],
                'items.*.item_code'   => ['nullable', 'string'],
            ]);

            foreach ($request->items as $item) {
                PurchaseRequestItem::create([
                    'purchase_request_id' => $pr->id,
                    'item_code'  => $item['item_code'] ?? ('ITM-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT)),
                    'name'       => $item['name'],
                    'quantity'   => $item['quantity'],
                    'unit'       => $item['unit'],
                    'specification' => $item['specification'] ?? null,
                ]);
            }
        }

        // Save service items (stored in same items table with service flag)
        if ($itemType === 'service') {
            $request->validate([
                'services'                   => ['required', 'array', 'min:1'],
                'services.*.service_id'      => ['required', 'string'],
                'services.*.description'     => ['required', 'string'],
                'services.*.unit'            => ['required', 'string'],
                'services.*.volume'          => ['required', 'integer', 'min:1'],
            ]);

            foreach ($request->services as $svc) {
                PurchaseRequestItem::create([
                    'purchase_request_id' => $pr->id,
                    'item_code'  => $svc['service_id'],
                    'name'       => $svc['description'],
                    'quantity'   => $svc['volume'],
                    'unit'       => $svc['unit'],
                    'specification' => 'Service',
                ]);
            }
        }

        History::create([
            'user_id'             => Auth::id(),
            'vendor_id'           => null,
            'rfq_id'              => null,
            'vendor_selection_id' => null,
            'action'              => 'Purchase Request Created',
            'transaction_status'  => 'completed',
            'notes'               => 'PR ' . $docNumber . ' dibuat.',
            'action_date'         => now(),
        ]);

        return redirect()->route('pr.list')
            ->with('success', 'Purchase request ' . $docNumber . ' berhasil dibuat.');
    }

    /**
     * Show single PR as JSON (for modal).
     */
    public function show(PurchaseRequest $pr)
    {
        $pr->load(['items', 'user', 'rfqs']);
        return response()->json($pr);
    }
}