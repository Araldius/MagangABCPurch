<?php

namespace App\Http\Controllers;

use App\Models\History;
use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestItem;
use App\Models\ServiceRequest;
use App\Models\ServiceRequestJob;
use App\Models\ServiceRequestItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PurchaseRequestController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        $prQuery = PurchaseRequest::with(['items', 'user'])->latest();
        $srQuery = ServiceRequest::with(['jobs.items', 'user'])->latest();

        if ($user->role !== 'purchasing') {
            $prQuery->where('user_id', $user->id);
            $srQuery->where('user_id', $user->id);
        }

        $prs = $prQuery->get()->map(function($req) {
            $req->type = 'goods';
            $req->display_doc = $req->document_number;
            $req->display_title = $req->title;
            $req->item_count = $req->items->count();
            return $req;
        });

        $srs = $srQuery->get()->map(function($req) {
            $req->type = 'service';
            $req->display_doc = 'SR-' . str_pad($req->id, 4, '0', STR_PAD_LEFT);
            $req->display_title = $req->service_name;
            $itemCount = 0;
            foreach($req->jobs as $job) { $itemCount += $job->items->count(); }
            $req->item_count = $itemCount;
            return $req;
        });

        $allRequests = $prs->concat($srs)->sortByDesc('created_at')->values();
        $isPurchasing = $user->role === 'purchasing';

        return view('purchase_requests.list', compact('allRequests', 'isPurchasing'));
    }

    public function create()
    {
        $existingItems = PurchaseRequestItem::select('item_id', 'item_name', 'unit', 'specification', 'item_notes')
            ->distinct()->get()->map(function ($item) {
                return [
                    'id'    => $item->item_id,
                    'name'  => $item->item_name,
                    'unit'  => $item->unit,
                    'spec'  => $item->specification ?? '',
                    'notes' => $item->item_notes ?? '',
                ];
            })->values();

        return view('purchase_requests.create', compact('existingItems'));
    }

    public function store(Request $request)
    {
        if ($request->item_type === 'service') {
            // Validasi data array input services bertingkat
            $request->validate([
                'requested_date' => 'required|date',
                'plant'          => 'required|string',
                'services'       => 'required|array|min:1',
            ]);

            // Lakukan perulangan untuk setiap Service yang ditambahkan
            foreach ($request->services as $svcData) {
                $sr = ServiceRequest::create([
                    'user_id'         => Auth::id(),
                    'service_name'    => $svcData['service_name'],
                    'submission_date' => now(),
                    'requested_date'  => $request->requested_date,
                    'plant'           => $request->plant,
                    'status'          => 'awaiting_approval',
                ]);

                foreach ($svcData['jobs'] as $jobData) {
                    $job = ServiceRequestJob::create([
                        'service_request_id' => $sr->id,
                        'job_description'    => $jobData['description'],
                    ]);

                    foreach ($jobData['items'] as $itemData) {
                        ServiceRequestItem::create([
                            'job_id'        => $job->id,
                            'item_name'     => $itemData['item_name'],
                            'quantity'      => $itemData['quantity'],
                            'unit'          => $itemData['unit'], 
                            'specification' => $itemData['specification'] ?? null,
                        ]);
                    }
                }
                $docInfo = 'SR-' . str_pad($sr->id, 4, '0', STR_PAD_LEFT);
            }

            History::create([
                'user_id'            => Auth::id(),
                'action'             => 'Request Created',
                'transaction_status' => 'completed',
                'notes'              => "Dokumen $docInfo berhasil dibuat.",
                'action_date'        => now(),
            ]);

            return redirect()->route('pr.list')->with('success', "Request $docInfo berhasil dibuat.");
            
        } else {
            // LOGIKA UNTUK GOODS (BARANG) YANG SEMPAT HILANG
            $request->validate([
                'document_number' => 'required|unique:purchase_requests',
                'title'           => 'required|string',
                'department'      => 'required|string',
                'plant'           => 'required|string',
                'requested_date'  => 'required|date',
                'items'           => 'required|array|min:1',
                'items.*.item_id' => 'required|string',
            ]);

            $pr = PurchaseRequest::create([
                'user_id'             => Auth::id(),
                'document_number'     => $request->document_number,
                'title'               => $request->title,
                'department'          => $request->department,
                'plant'               => $request->plant,
                'submission_date'     => now(),
                'requested_date'      => $request->requested_date,
                'status'              => 'awaiting_approval',
            ]);

            foreach ($request->items as $item) {
                PurchaseRequestItem::create([
                    'purchase_request_id' => $pr->id,
                    'item_id'             => $item['item_id'],
                    'item_name'           => $item['item_name'],
                    'quantity'            => $item['quantity'],
                    'unit'                => $item['unit'],
                    'specification'       => $item['specification'] ?? null,
                    'item_notes'          => $item['item_notes'] ?? null,
                ]);
            }
            $docInfo = $request->document_number;

            History::create([
                'user_id'            => Auth::id(),
                'action'             => 'Request Created',
                'transaction_status' => 'completed',
                'notes'              => "Dokumen $docInfo berhasil dibuat.",
                'action_date'        => now(),
            ]);

            return redirect()->route('pr.list')->with('success', "Request $docInfo berhasil dibuat.");
        }
    }
}