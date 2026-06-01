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

        $prQuery = PurchaseRequest::with([
            'items', 'user',
            'rfqs.vendorSelections.vendor',
            'rfqs.vendorSelections.selectionItems',
            'rfqs.histories.user',
        ])->latest();

        $srQuery = ServiceRequest::with(['jobs.items', 'user'])->latest();

        if ($user->role !== 'purchasing') {
            $prQuery->where('user_id', $user->id);
            $srQuery->where('user_id', $user->id);
        }

        $prs = $prQuery->get()->map(function ($req) {
            $req->type          = 'goods';
            $req->display_doc   = $req->document_number;
            $req->display_title = $req->title;
            $req->item_count    = $req->items->count();
            return $req;
        });

        $srs = $srQuery->get()->map(function ($req) {
            $req->type          = 'service';
            $req->display_doc   = $req->document_number ?? ('SR-' . now()->format('Y') . '-' . str_pad($req->id, 4, '0', STR_PAD_LEFT));
            $req->display_title = $req->service_name;
            $itemCount = 0;
            foreach ($req->jobs as $job) { $itemCount += $job->items->count(); }
            $req->item_count = $itemCount;
            return $req;
        });

        $allRequests  = $prs->concat($srs)->sortByDesc('created_at')->values();
        $isPurchasing = $user->role === 'purchasing';

        return view('purchase_requests.list', compact('allRequests', 'isPurchasing'));
    }

    public function create()
    {
        // Goods catalog — distinct items from all past PRs
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

        // Service templates — built from past ServiceRequests in DB
        $existingServiceTemplates = ServiceRequest::with('jobs.items')
            ->latest()
            ->get()
            ->map(function ($sr) {
                return [
                    'id'           => 'SR-' . $sr->id,
                    'service_name' => $sr->service_name,
                    'doc_number'   => $sr->document_number ?? $sr->display_doc,
                    'jobs'         => $sr->jobs->map(function ($job) {
                        return [
                            'description' => $job->job_description,
                            'items'       => $job->items->map(function ($item) {
                                return [
                                    'name' => $item->item_name,
                                    'qty'  => $item->quantity,
                                    'unit' => $item->unit,
                                    'spec' => $item->specification ?? '',
                                ];
                            })->values()->toArray(),
                        ];
                    })->values()->toArray(),
                ];
            })->values();

        // Auto-generate next PR document number: PR-YYYY-NNNN
        $prYearCount  = PurchaseRequest::whereYear('created_at', now()->year)->count() + 1;
        $nextPrDocNum = 'PR-' . now()->format('Y') . '-' . str_pad($prYearCount, 4, '0', STR_PAD_LEFT);

        return view('purchase_requests.create', compact('existingItems', 'existingServiceTemplates', 'nextPrDocNum'));
    }

    public function store(Request $request)
    {
        if ($request->item_type === 'service') {
            $request->validate([
                'requested_date' => 'required|date',
                'plant'          => 'required|string',
                'services'       => 'required|array|min:1',
            ]);

            foreach ($request->services as $svcData) {
                // Generate SR document number: SR-YYYY-NNNN
                $srCount  = ServiceRequest::whereYear('created_at', now()->year)->count() + 1;
                $srDocNum = 'SR-' . now()->format('Y') . '-' . str_pad($srCount, 4, '0', STR_PAD_LEFT);

                $sr = ServiceRequest::create([
                    'user_id'         => Auth::id(),
                    'document_number' => $srDocNum,
                    'service_name'    => $svcData['service_name'],
                    'submission_date' => now(),
                    'requested_date'  => $request->requested_date,
                    'plant'           => $request->plant,
                    'status'          => 'submitted',
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
            $request->validate([
                'title'           => 'required|string',
                'department'      => 'required|string',
                'plant'           => 'required|string',
                'requested_date'  => 'required|date',
                'items'           => 'required|array|min:1',
                'items.*.item_id' => 'required|string',
            ]);

            // Auto-generate PR-YYYY-NNNN (4-digit, same format as SR)
            $prYearCount  = PurchaseRequest::whereYear('created_at', now()->year)->count() + 1;
            $prDocNum     = 'PR-' . now()->format('Y') . '-' . str_pad($prYearCount, 4, '0', STR_PAD_LEFT);

            $pr = PurchaseRequest::create([
                'user_id'         => Auth::id(),
                'document_number' => $prDocNum,
                'title'           => $request->title,
                'department'      => $request->department,
                'plant'           => $request->plant,
                'submission_date' => now(),
                'requested_date'  => $request->requested_date,
                'status'          => 'submitted',
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
            $docInfo = $prDocNum;

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