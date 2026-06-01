<?php
 
namespace App\Http\Controllers;
 
use App\Models\PurchaseRequest;
use App\Models\ServiceRequest;
use Illuminate\Support\Facades\Auth;

class HistoryController extends Controller
{
    private function getBaseCompletedPRs()
    {
        $user = Auth::user();
        
        $prQuery = PurchaseRequest::with([
            'items',
            'rfqs.vendorSelections.vendor',
            'rfqs.vendorSelections.selectionItems'
        ])->where('status', 'completed')->latest();

        $srQuery = ServiceRequest::with([
            'jobs.items',
            'rfqs.vendorSelections.vendor',
            'rfqs.vendorSelections.selectionItems'
        ])->where('status', 'completed')->latest();

        if ($user->role !== 'purchasing') {
            $prQuery->where('user_id', $user->id);
            $srQuery->where('user_id', $user->id);
        }

        $prs = $prQuery->get();
        $srs = $srQuery->get();

        return $prs->concat($srs)->sortByDesc('created_at')->values();
    }

    public function orders()
    {
        $prs = $this->getBaseCompletedPRs();
        $records = collect();

        foreach ($prs as $pr) {
            $isPR = $pr instanceof PurchaseRequest;
            foreach ($pr->rfqs as $rfq) {
                foreach ($rfq->vendorSelections as $sel) {
                    $vendor = $sel->vendor;
                    $totalValue = $sel->selectionItems->sum(fn($si) => ($si->final_price_per_item ?? 0) * ($si->final_quantity ?? 0));
                    $leadDays = $sel->decided_at ? (int) \Carbon\Carbon::parse($sel->decided_at)->diffInDays($pr->created_at) : null;

                    $selectionItems = $sel->selectionItems->keyBy(function($si) use ($isPR) {
                        return $isPR ? $si->purchase_request_item_id : $si->service_request_item_id;
                    });
                    $mappedItems = collect();
                    if ($isPR) {
                        foreach(optional($pr)->items ?? [] as $item) {
                            $si = $selectionItems->get($item->id);
                            $mappedItems->push((object)[
                                'item_id' => $item->item_id ?? $item->item_code,
                                'name' => $item->item_name,
                                'description' => $item->description,
                                'specification' => $item->specification,
                                'quantity' => $si ? $si->final_quantity : $item->quantity,
                                'unit' => $item->unit,
                                'final_price_per_item' => $si ? $si->final_price_per_item : null,
                            ]);
                        }
                    } else {
                        foreach(optional($pr)->jobs ?? [] as $job) {
                            foreach(optional($job)->items ?? [] as $item) {
                                $si = $selectionItems->get($item->id);
                                $mappedItems->push((object)[
                                    'item_id' => null,
                                    'name' => $item->item_name,
                                    'description' => $job->job_description,
                                    'specification' => $item->specification,
                                    'quantity' => $si ? $si->final_quantity : $item->quantity,
                                    'unit' => $item->unit,
                                    'final_price_per_item' => $si ? $si->final_price_per_item : null,
                                ]);
                            }
                        }
                    }

                    $records->push((object) [
                        'doc_number'  => $pr->document_number,
                        'vendor_name' => optional($vendor)->name ?? optional($vendor)->vendor_name ?? 'â€”',
                        'vendor_city' => optional($vendor)->location ?? '',
                        'department'  => $pr->department ?? optional($pr->user)->department ?? 'â€”',
                        'items'       => $mappedItems,
                        'total_value' => $totalValue,
                        'lead_days'   => $leadDays,
                        'status'      => $pr->status,
                        'decided_at'  => $sel->decided_at,
                        'completed_date' => $pr->updated_at ? $pr->updated_at->format('d M Y') : '-',
                    ]);
                }
            }
        }

        $vendorsUsed  = $records->pluck('vendor_name')->reject(fn($v) => $v === 'â€”')->unique()->count();
        $totalValue   = $records->sum('total_value');
        $prsCompleted = $prs->count();
        $avgLeadDays  = round($records->filter(fn($r) => $r->lead_days !== null)->avg('lead_days') ?? 0);
        $departments  = $records->pluck('department')->unique()->filter()->values();

        return view('history.orders', compact(
            'records', 'vendorsUsed', 'totalValue',
            'prsCompleted', 'avgLeadDays', 'departments'
        ));
    }

    public function items()
    {
        $prs = $this->getBaseCompletedPRs();
        $itemMap = [];

        foreach ($prs as $pr) {
            foreach ($pr->rfqs as $rfq) {
                foreach ($rfq->vendorSelections as $sel) {
                    $vendor = $sel->vendor;
                    $vName = optional($vendor)->name ?? optional($vendor)->vendor_name ?? '-';
                    $leadDays = $sel->decided_at ? (int) \Carbon\Carbon::parse($sel->decided_at)->diffInDays($pr->created_at) : null;
                    
                    foreach ($sel->selectionItems as $si) {
                        $pri = null;
                        if ($pr->type === 'service' || method_exists($pr, 'jobs')) {
                            foreach ($pr->jobs ?? [] as $job) {
                                $found = collect($job->items)->firstWhere('id', $si->service_request_item_id);
                                if ($found) { $pri = $found; break; }
                            }
                        } else {
                            $pri = collect($pr->items)->firstWhere('id', $si->purchase_request_item_id);
                        }

                        if (!$pri) continue;
                        $itemId = $pri->item_id ?? $pri->item_code ?? null;
                        if (!$itemId) {
                            $itemId = 'SVC-00' . ($pri->id ?? rand(1000, 9999));
                        }

                        if (!isset($itemMap[$itemId])) {
                            $itemMap[$itemId] = [
                                'item_id' => $itemId,
                                'item_name' => $pri->item_name ?? $pri->name ?? '-',
                                'last_purchase' => null,
                                'last_value' => 0,
                                'history' => []
                            ];
                        }

                        $dateStr = $sel->decided_at ? \Carbon\Carbon::parse($sel->decided_at)->format('Y-m-d') : '';
                        if (!$itemMap[$itemId]['last_purchase'] || $dateStr > $itemMap[$itemId]['last_purchase']) {
                            $itemMap[$itemId]['last_purchase'] = $dateStr;
                            $itemMap[$itemId]['last_value'] = $si->final_price_per_item * $si->final_quantity;
                        }

                        $itemMap[$itemId]['history'][] = [
                            'item_name' => $pri->item_name ?? $pri->name ?? '-',
                            'vendor' => $vName,
                            'vendor_city' => optional($vendor)->location ?? '',
                            'value' => $si->final_price_per_item * $si->final_quantity,
                            'qty' => $si->final_quantity,
                            'unit' => $pri->unit ?? '-',
                            'spec' => $pri->specification ?? '-',
                            'requested_by' => $pr->user->name ?? '-',
                            'lead_time' => $leadDays ? $leadDays . ' days' : '-',
                            'req_date' => $pr->requested_date ? \Carbon\Carbon::parse($pr->requested_date)->format('Y-m-d') : '-',
                            'doc_no' => $pr->document_number,
                            'pr_id' => $pr->id,
                            'type' => $pr->type ?? 'goods'
                        ];
                    }
                }
            }
        }

        $items = collect(array_values($itemMap))->sortByDesc('last_purchase')->values();

        $vendorsUsed = 0; $totalValue = 0;
        foreach ($prs as $p) {
            foreach ($p->rfqs as $r) {
                foreach ($r->vendorSelections as $s) {
                    $totalValue += $s->selectionItems->sum(fn($si) => ($si->final_price_per_item ?? 0) * ($si->final_quantity ?? 0));
                }
            }
        }
        $vendorsUsed = collect($prs)->flatMap->rfqs->flatMap->vendorSelections->pluck('vendor_id')->unique()->count();
        $prsCompleted = $prs->count();
        $avgLeadDays = 0;
        $lDays = collect();
        foreach ($prs as $p) {
            foreach ($p->rfqs as $r) {
                foreach ($r->vendorSelections as $s) {
                    if ($s->decided_at) {
                        $lDays->push(\Carbon\Carbon::parse($s->decided_at)->diffInDays($p->created_at));
                    }
                }
            }
        }
        if ($lDays->count() > 0) $avgLeadDays = round($lDays->avg());

        return view('history.items', compact('items', 'vendorsUsed', 'totalValue', 'prsCompleted', 'avgLeadDays'));
    }

    public function vendors()
    {
        $prs = $this->getBaseCompletedPRs();
        $vendorMap = [];

        foreach ($prs as $pr) {
            foreach ($pr->rfqs as $rfq) {
                foreach ($rfq->vendorSelections as $sel) {
                    $vendor = $sel->vendor;
                    if (!$vendor) continue;
                    $vid = $vendor->id;
                    $vName = $vendor->name ?? $vendor->vendor_name ?? '-';
                    if ($vName === '-' || $vName === '-') continue;

                    if (!isset($vendorMap[$vid])) {
                        $vendorMap[$vid] = [
                            'vendor_id' => $vid,
                            'vendor_name' => $vName,
                            'vendor_city' => $vendor->location ?? '',
                            'last_purchase' => null,
                            'total_value' => 0,
                            'history' => []
                        ];
                    }

                    $dateStr = $sel->decided_at ? \Carbon\Carbon::parse($sel->decided_at)->format('Y-m-d') : '';
                    if (!$vendorMap[$vid]['last_purchase'] || $dateStr > $vendorMap[$vid]['last_purchase']) {
                        $vendorMap[$vid]['last_purchase'] = $dateStr;
                    }

                    $subtotal = 0;
                    foreach ($sel->selectionItems as $si) {
                        $val = $si->final_price_per_item * $si->final_quantity;
                        $subtotal += $val;
                        $pri = null;
                        if ($pr->type === 'service' || method_exists($pr, 'jobs')) {
                            foreach ($pr->jobs ?? [] as $job) {
                                $found = collect($job->items)->firstWhere('id', $si->service_request_item_id);
                                if ($found) { $pri = $found; break; }
                            }
                        } else {
                            $pri = collect($pr->items)->firstWhere('id', $si->purchase_request_item_id);
                        }

                        $leadDays = $sel->decided_at ? (int) \Carbon\Carbon::parse($sel->decided_at)->diffInDays($pr->created_at) : null;

                        $vendorMap[$vid]['history'][] = [
                            'item_id' => $pri->item_id ?? $pri->item_code ?? '-',
                            'item_name' => $pri->item_name ?? $pri->name ?? '-',
                            'value' => $val,
                            'qty' => $si->final_quantity,
                            'unit' => $pri->unit ?? '-',
                            'specification' => $pri->specification ?? '-',
                            'requested_by' => $pr->user->name ?? '-',
                            'lead_time' => $leadDays ? $leadDays . ' days' : '-',
                            'req_date' => $pr->requested_date ? \Carbon\Carbon::parse($pr->requested_date)->format('Y-m-d') : '-',
                            'doc_no' => $pr->document_number,
                            'pr_id' => $pr->id,
                            'type' => $pr->type ?? 'goods'
                        ];
                    }
                    $vendorMap[$vid]['total_value'] += $subtotal;
                }
            }
        }

        $vendors = collect(array_values($vendorMap))->sortByDesc('last_purchase')->values();

        $vendorsUsed = count($vendorMap);
        $totalValue = $vendors->sum('total_value');
        $prsCompleted = $prs->count();
        $lDays = collect();
        foreach ($prs as $p) {
            foreach ($p->rfqs as $r) {
                foreach ($r->vendorSelections as $s) {
                    if ($s->decided_at) {
                        $lDays->push(\Carbon\Carbon::parse($s->decided_at)->diffInDays($p->created_at));
                    }
                }
            }
        }
        $avgLeadDays = $lDays->count() > 0 ? round($lDays->avg()) : 0;

        return view('history.vendors', compact('vendors', 'vendorsUsed', 'totalValue', 'prsCompleted', 'avgLeadDays'));
    }
}