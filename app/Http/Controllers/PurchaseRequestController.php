<?php
 
namespace App\Http\Controllers;
 
use App\Models\History;
use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
 
class PurchaseRequestController extends Controller
{
    public function index()
    {
        $user  = Auth::user();
        $query = PurchaseRequest::with(['items', 'user'])->latest();
 
        if ($user->role !== 'purchasing') {
            $query->where('user_id', $user->id);
        }
 
        $requests     = $query->get();
        $isPurchasing = $user->role === 'purchasing';
 
        return view('purchase_requests.list', compact('requests', 'isPurchasing'));
    }
 
    public function create()
    {
        // Mengambil semua item unik yang pernah dibuat sebelumnya dari database
        // Item ini akan dikirim ke Frontend (Javascript) sebagai Katalog Master.
        $existingItems = PurchaseRequestItem::whereNotNull('item_code')
            ->where('item_code', '!=', '')
            ->orderBy('id', 'desc')
            ->get()
            ->unique('item_code') // Hanya ambil 1 data terbaru untuk setiap kode item
            ->map(function ($item) {
                return [
                    'id'   => $item->item_code,
                    'name' => $item->name,
                    'desc' => $item->item_name ?? $item->name,
                    'unit' => $item->unit,
                    'spec' => $item->specification ?? '',
                ];
            })
            ->values();

        return view('purchase_requests.create', compact('existingItems'));
    }
 
    public function store(Request $request)
    {
        $itemType = $request->input('item_type', 'goods');
 
        // Menghapus 'title' dari validasi karena dihilangkan dari form UI
        $request->validate([
            'department' => ['required', 'string', 'max:255'],
            'plant'      => ['required', 'string', 'max:255'],
            'need_date'  => ['required', 'date'],
            'note'       => ['nullable', 'string'],
            'item_type'  => ['required', 'in:goods,service'],
        ]);
 
        $todayCount = PurchaseRequest::whereDate('created_at', today())->count() + 1;
        $docNumber  = 'PR-' . now()->format('Y') . '-' . now()->format('md') . '-'
                    . str_pad($todayCount, 3, '0', STR_PAD_LEFT);
        
        // Membuat title otomatis untuk memenuhi syarat database
        $autoTitle = 'Pengadaan ' . ucfirst($itemType) . ' - ' . $request->department;
 
        $pr = PurchaseRequest::create([
            'user_id'         => Auth::id(),
            'document_number' => $docNumber,
            'title'           => $autoTitle,
            'department'      => $request->department,
            'plant'           => $request->plant,
            'submission_date' => today(),
            'requested_date'  => $request->need_date,
            'need_date'       => $request->need_date,
            'note'            => $request->note,
            'status'          => 'awaiting_approval',
            'item_type'       => $itemType, // Menyimpan tipe di DB
        ]);
 
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
                    'item_code'     => $item['item_code'] ?? ('ITM-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT)),
                    'name'          => $item['name'],
                    'item_name'     => $item['name'],
                    'quantity'      => $item['quantity'],
                    'unit'          => $item['unit'],
                    'specification' => $item['specification'] ?? null,
                ]);
            }
        }
 
        if ($itemType === 'service') {
            $request->validate([
                'services'               => ['required', 'array', 'min:1'],
                'services.*.service_id'  => ['required', 'string'],
                'services.*.description' => ['required', 'string'],
                'services.*.unit'        => ['required', 'string'],
                'services.*.volume'      => ['required', 'integer', 'min:1'],
            ]);
            foreach ($request->services as $svc) {
                PurchaseRequestItem::create([
                    'purchase_request_id' => $pr->id,
                    'item_code'     => $svc['service_id'],
                    'name'          => $svc['description'],
                    'item_name'     => $svc['description'],
                    'quantity'      => $svc['volume'],
                    'unit'          => $svc['unit'],
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
 
    public function show(PurchaseRequest $pr)
    {
        $pr->load(['items', 'user', 'rfqs']);
        return response()->json($pr);
    }
}