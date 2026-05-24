@extends('layouts.app')
@php
    $pageTitle = 'Dashboard';
    $user      = auth()->user();
    $firstName = explode(' ', $user->name)[0];
    $statusCfg = [
        'awaiting_approval' => ['Awaiting Approval', '#fff7ed','#c2410c','#f97316'],
        'in_process'        => ['In Process',        '#f0f9ff','#0369a1','#0ea5e9'],
        'approved'          => ['Approved',           '#f0fdf4','#15803d','#22c55e'],
        'completed'         => ['Completed',          '#eff6ff','#1d4ed8','#3b82f6'],
        'rejected'          => ['Rejected',           '#fef2f2','#b91c1c','#ef4444'],
        'cancelled'         => ['Cancelled',          '#f3f4f6','#374151','#9ca3af'],
    ];
@endphp
@section('content')
 
<div style="margin-bottom:20px">
    <h1 style="font-size:20px;font-weight:700;color:#111827;margin:0 0 3px">Welcome back, {{ $firstName }}</h1>
    <p style="font-size:12.5px;color:#6b7280;margin:0">Here's a summary of your procurement requests.</p>
</div>
 
{{-- STAT CARDS --}}
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:20px">
    <div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:18px 20px">
        <div style="font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.07em">Active PR</div>
        <div style="font-size:28px;font-weight:800;color:#2563eb;margin:8px 0 5px;line-height:1">{{ $activePrs }}</div>
        <div style="font-size:11.5px;color:#9ca3af">Period: {{ now()->format('M Y') }}</div>
    </div>
    <div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:18px 20px">
        <div style="font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.07em">Awaiting Approval</div>
        <div style="font-size:28px;font-weight:800;color:#ea580c;margin:8px 0 5px;line-height:1">{{ $awaitingApproval }}</div>
        <div style="font-size:11.5px;color:#9ca3af">Needs action</div>
    </div>
    <div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:18px 20px">
        <div style="font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.07em">In Process</div>
        <div style="font-size:28px;font-weight:800;color:#0284c7;margin:8px 0 5px;line-height:1">{{ $inProcess }}</div>
        <div style="font-size:11.5px;color:#9ca3af">Purchasing verification</div>
    </div>
    <div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:18px 20px">
        <div style="font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.07em">Completed This Month</div>
        <div style="font-size:28px;font-weight:800;color:#16a34a;margin:8px 0 5px;line-height:1">{{ $completedMonth }}</div>
        <div style="font-size:11.5px;color:#9ca3af">PR fulfilled</div>
    </div>
</div>
 
{{-- PR TABLE --}}
<div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;margin-bottom:20px">
    <div style="display:flex;align-items:center;justify-content:space-between;padding:16px 20px;border-bottom:1px solid #f3f4f6">
        <span style="font-size:14px;font-weight:700;color:#111827">Purchase Requests (PR)</span>
        <a href="{{ route('purchase_requests.create') }}"
           style="display:inline-flex;align-items:center;gap:5px;padding:7px 14px;background:#111827;color:#fff;border-radius:7px;font-size:12.5px;font-weight:600;text-decoration:none"
           onmouseover="this.style.background='#1f2937'" onmouseout="this.style.background='#111827'">
            <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14" stroke-linecap="round"/></svg>
            New Request
        </a>
    </div>

    {{-- Toolbar PR --}}
    <div style="display:flex;gap:8px;align-items:center;padding:12px 20px;border-bottom:1px solid #f3f4f6;flex-wrap:wrap;">
        <input type="text" id="dash-pr-search" placeholder="Search PR..."
            oninput="applyDashPR()"
            style="height:32px;border:1px solid #e5e7eb;border-radius:7px;padding:0 10px;font-size:12.5px;width:180px;outline:none;font-family:inherit;">
        <div style="position:relative;">
            <select id="dash-pr-category" onchange="applyDashPR()"
                style="height:32px;padding:0 28px 0 10px;border:1px solid #e5e7eb;border-radius:7px;font-size:12.5px;background:#fff;appearance:none;cursor:pointer;font-family:inherit;">
                <option value="">All Category</option>
                <option value="goods">📦 Goods</option>
                <option value="service">🔧 Service</option>
            </select>
            <svg style="position:absolute;right:8px;top:50%;transform:translateY(-50%);pointer-events:none;color:#6b7280" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 9l6 6 6-6" stroke-linecap="round"/></svg>
        </div>
        <div style="position:relative;">
            <select id="dash-pr-status" onchange="applyDashPR()"
                style="height:32px;padding:0 28px 0 10px;border:1px solid #e5e7eb;border-radius:7px;font-size:12.5px;background:#fff;appearance:none;cursor:pointer;font-family:inherit;">
                <option value="">All Status</option>
                <option value="awaiting_approval">Awaiting Approval</option>
                <option value="in_process">In Process</option>
                <option value="approved">Approved</option>
                <option value="completed">Completed</option>
            </select>
            <svg style="position:absolute;right:8px;top:50%;transform:translateY(-50%);pointer-events:none;color:#6b7280" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 9l6 6 6-6" stroke-linecap="round"/></svg>
        </div>
    </div>

    <div style="overflow-x:auto">
        <table style="width:100%;border-collapse:collapse;font-size:12.5px">
            <thead>
                <tr style="background:#f9fafb">
                    <th onclick="dashPRSortFn(0)" style="padding:9px 20px;text-align:left;font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.06em;white-space:nowrap;cursor:pointer;">DOC NO. <span id="dps0" style="font-size:9px;">↕</span></th>
                    <th onclick="dashPRSortFn(1)" style="padding:9px 14px;text-align:left;font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.06em;white-space:nowrap;cursor:pointer;">REQUESTED DATE <span id="dps1" style="font-size:9px;">↕</span></th>
                    <th style="padding:9px 14px;text-align:left;font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.06em;">DESCRIPTION</th>
                    <th style="padding:9px 14px;text-align:left;font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.06em;">CATEGORY</th>
                    <th style="padding:9px 14px;text-align:left;font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.06em;">QTY</th>
                    <th onclick="dashPRSortFn(5)" style="padding:9px 14px;text-align:left;font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.06em;cursor:pointer;">STATUS <span id="dps5" style="font-size:9px;">↕</span></th>
                    <th style="padding:9px 14px;text-align:left;font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.06em;white-space:nowrap;">LAST UPDATE</th>
                    <th style="padding:9px 14px;text-align:left;font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.06em;">ACTION</th>
                </tr>
            </thead>
            <tbody id="dash-pr-tbody">
                @forelse($requests as $pr)
                @php
                    [$sLabel,$sBg,$sText,$sDot] = $statusCfg[$pr->status] ?? [ucfirst(str_replace('_',' ',$pr->status)),'#f3f4f6','#374151','#9ca3af'];
                    $reqDate = \Carbon\Carbon::parse($pr->need_date ?? $pr->created_at);
                    $upd = $pr->updated_at;
                    $lastUpdate = $upd->isToday() ? 'Today, '.$upd->format('H:i') : ($upd->isYesterday() ? 'Yesterday, '.$upd->format('H:i') : $upd->format('d M').', '.$upd->format('H:i'));
                    $units = $pr->items->pluck('unit')->unique();
                    $qtyLabel = $units->count()===1 ? $pr->items->sum('quantity').' '.$units->first() : $pr->items->count().' Items';
                    $prCategory = $pr->type ?? 'goods';
                @endphp
                <tr data-status="{{ $pr->status }}" data-category="{{ $prCategory }}"
                    style="border-bottom:1px solid #f3f4f6"
                    onmouseover="this.style.background='#fafafa'" onmouseout="this.style.background='transparent'">
                    <td style="padding:13px 20px"><span style="font-family:'Courier New',monospace;font-size:12px;font-weight:600;color:#111827">{{ $pr->document_number }}</span></td>
                    <td style="padding:13px 14px;color:#6b7280;font-size:12px;white-space:nowrap">{{ $reqDate->format('d M Y') }}</td>
                    <td style="padding:13px 14px;max-width:220px">
                        <div style="font-size:12.5px;font-weight:500;color:#111827">{{ $pr->title }}</div>
                        <div style="font-size:11px;color:#9ca3af;margin-top:1px">{{ $pr->plant }}</div>
                    </td>
                    <td style="padding:13px 14px;">
                        @if($prCategory === 'service')
                        <span style="padding:3px 8px;border-radius:6px;font-size:11px;font-weight:600;background:#f0fdf4;color:#16a34a;">🔧 Service</span>
                        @else
                        <span style="padding:3px 8px;border-radius:6px;font-size:11px;font-weight:600;background:#f1f5f9;color:#475569;">📦 Goods</span>
                        @endif
                    </td>
                    <td style="padding:13px 14px;font-size:12px;color:#374151;white-space:nowrap">{{ $qtyLabel }}</td>
                    <td style="padding:13px 14px">
                        <span style="display:inline-flex;align-items:center;gap:5px;padding:3px 9px;border-radius:999px;background:{{ $sBg }};font-size:11.5px;font-weight:600;color:{{ $sText }};white-space:nowrap">
                            <span style="width:5px;height:5px;border-radius:50%;background:{{ $sDot }};flex-shrink:0"></span>{{ $sLabel }}
                        </span>
                    </td>
                    <td style="padding:13px 14px;font-size:12px;color:#6b7280;white-space:nowrap">{{ $lastUpdate }}</td>
                    <td style="padding:13px 14px">
                        <button onclick="openDetailModal({{ $pr->id }})"
                            style="padding:4px 12px;border:1px solid #d1d5db;border-radius:6px;background:#fff;font-size:12px;font-weight:500;color:#374151;cursor:pointer"
                            onmouseover="this.style.background='#f9fafb'" onmouseout="this.style.background='#fff'">Detail</button>
                    </td>
                </tr>
                @empty
                <tr id="dash-pr-empty"><td colspan="8" style="text-align:center;padding:36px 20px;color:#9ca3af;font-size:12.5px">No purchase requests yet. <a href="{{ route('purchase_requests.create') }}" style="color:#3b5bdb;font-weight:600;text-decoration:none">Create first PR →</a></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div id="dash-pr-pager" style="padding:12px 20px;border-top:1px solid #f3f4f6;"></div>
</div>
 
{{-- RECENT VENDOR HISTORY --}}
<div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px">
    <div style="display:flex;align-items:center;justify-content:space-between;padding:16px 20px;border-bottom:1px solid #f3f4f6">
        <span style="font-size:14px;font-weight:700;color:#111827">Recent Vendor History</span>
        <a href="{{ route('history.index') }}" style="font-size:12px;font-weight:500;color:#6b7280;text-decoration:none" onmouseover="this.style.color='#111827'" onmouseout="this.style.color='#6b7280'">View All →</a>
    </div>

    {{-- Toolbar History --}}
    <div style="display:flex;gap:8px;align-items:center;padding:12px 20px;border-bottom:1px solid #f3f4f6;flex-wrap:wrap;">
        <input type="text" id="dash-h-search" placeholder="Search vendor..."
            oninput="applyDashH()"
            style="height:32px;border:1px solid #e5e7eb;border-radius:7px;padding:0 10px;font-size:12.5px;width:180px;outline:none;font-family:inherit;">
        <div style="position:relative;">
            <select id="dash-h-dept" onchange="applyDashH()"
                style="height:32px;padding:0 28px 0 10px;border:1px solid #e5e7eb;border-radius:7px;font-size:12.5px;background:#fff;appearance:none;cursor:pointer;font-family:inherit;">
                <option value="">All Units</option>
                <option value="Maintenance">Maintenance</option>
                <option value="Produksi">Produksi</option>
                <option value="IT">IT</option>
                <option value="Finance">Finance</option>
            </select>
            <svg style="position:absolute;right:8px;top:50%;transform:translateY(-50%);pointer-events:none;color:#6b7280" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 9l6 6 6-6" stroke-linecap="round"/></svg>
        </div>
        <div style="position:relative;">
            <select id="dash-h-status" onchange="applyDashH()"
                style="height:32px;padding:0 28px 0 10px;border:1px solid #e5e7eb;border-radius:7px;font-size:12.5px;background:#fff;appearance:none;cursor:pointer;font-family:inherit;">
                <option value="">All Status</option>
                <option value="completed">Completed</option>
                <option value="in_process">In Process</option>
            </select>
            <svg style="position:absolute;right:8px;top:50%;transform:translateY(-50%);pointer-events:none;color:#6b7280" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 9l6 6 6-6" stroke-linecap="round"/></svg>
        </div>
    </div>

    <div style="overflow-x:auto">
        <table style="width:100%;border-collapse:collapse;font-size:12.5px">
            <thead>
                <tr style="background:#f9fafb">
                    <th onclick="dashHSortFn(0)" style="padding:9px 20px;text-align:left;font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.06em;cursor:pointer;">DOC NO. <span id="dhs0" style="font-size:9px;">↕</span></th>
                    <th onclick="dashHSortFn(1)" style="padding:9px 14px;text-align:left;font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.06em;cursor:pointer;">VENDOR <span id="dhs1" style="font-size:9px;">↕</span></th>
                    <th onclick="dashHSortFn(2)" style="padding:9px 14px;text-align:left;font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.06em;cursor:pointer;">DEPARTMENT <span id="dhs2" style="font-size:9px;">↕</span></th>
                    <th onclick="dashHSortFn(3)" style="padding:9px 14px;text-align:left;font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.06em;cursor:pointer;">TOTAL VALUE <span id="dhs3" style="font-size:9px;">↕</span></th>
                    <th style="padding:9px 14px;text-align:left;font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.06em;">STATUS</th>
                </tr>
            </thead>
            <tbody id="dash-h-tbody">
                @forelse($recentHistory as $h)
                @php
                    $vName = optional($h->vendor)->name ?? optional($h->vendor)->vendor_name ?? '—';
                    $vCity = optional($h->vendor)->location ?? '';
                    $docNo = optional($h->rfq)->purchaseRequest->document_number ?? '—';
                    $dept  = optional($h->rfq)->purchaseRequest->department ?? '—';
                    $sel   = $h; 
                    $totalVal = $sel ? $sel->selectionItems->sum(fn($si)=>($si->final_price_per_item??0)*($si->final_quantity??0)) : 0;
                    $deptColors=['Maintenance'=>['#e0f2fe','#0369a1'],'Produksi'=>['#dcfce7','#15803d'],'IT'=>['#ede9fe','#7c3aed'],'IT & Digital'=>['#ede9fe','#7c3aed'],'Finance'=>['#fef9c3','#92400e']];
                    [$dBg,$dText]=$deptColors[$dept]??['#f1f5f9','#475569'];
                @endphp
                <tr data-dept="{{ $dept }}" data-status="completed"
                    style="border-bottom:1px solid #f3f4f6"
                    onmouseover="this.style.background='#fafafa'" onmouseout="this.style.background='transparent'">
                    <td style="padding:13px 20px"><span style="font-family:'Courier New',monospace;font-size:12px;font-weight:600;color:#111827">{{ $docNo }}</span></td>
                    <td style="padding:13px 14px">
                        <div style="font-size:12.5px;font-weight:500;color:#111827">{{ $vName }}</div>
                        <div style="font-size:11px;color:#9ca3af;margin-top:1px">{{ $vCity }}</div>
                    </td>
                    <td style="padding:13px 14px"><span style="display:inline-flex;align-items:center;padding:2px 9px;border-radius:6px;font-size:11.5px;font-weight:600;background:{{ $dBg }};color:{{ $dText }}">{{ $dept }}</span></td>
                    <td style="padding:13px 14px;font-size:12.5px;font-weight:600;color:#111827">Rp {{ number_format($totalVal,0,',','.') }}</td>
                    <td style="padding:13px 14px"><span style="display:inline-flex;align-items:center;gap:5px;padding:3px 9px;border-radius:999px;background:#eff6ff;font-size:11.5px;font-weight:600;color:#1d4ed8"><span style="width:5px;height:5px;border-radius:50%;background:#3b82f6;flex-shrink:0"></span>Completed</span></td>
                </tr>
                @empty
                <tr id="dash-h-empty"><td colspan="5" style="text-align:center;padding:28px 20px;color:#9ca3af;font-size:12.5px">No vendor history yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div id="dash-h-pager" style="padding:12px 20px;border-top:1px solid #f3f4f6;"></div>
</div>
 
{{-- DETAIL MODAL --}}
<div id="detail-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:200;align-items:center;justify-content:center;padding:20px">
    <div style="background:#fff;border-radius:14px;width:100%;max-width:680px;max-height:88vh;display:flex;flex-direction:column;box-shadow:0 8px 40px rgba(0,0,0,.12)">
        <div style="padding:18px 22px;border-bottom:1px solid #f3f4f6;display:flex;align-items:flex-start;justify-content:space-between;gap:12px">
            <div>
                <div id="modal-pr-title" style="font-size:15px;font-weight:700;color:#111827"></div>
                <div id="modal-pr-sub" style="font-size:12px;color:#3b5bdb;font-weight:500;margin-top:2px"></div>
            </div>
            <button onclick="closeDetailModal()" style="background:none;border:none;cursor:pointer;color:#9ca3af;padding:4px;border-radius:6px" onmouseover="this.style.background='#f3f4f6'" onmouseout="this.style.background='none'">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M18 6L6 18M6 6l12 12" stroke-linecap="round"/></svg>
            </button>
        </div>
        <div id="modal-body-content" style="padding:18px 22px;overflow-y:auto;flex:1;font-size:12.5px"></div>
        <div style="padding:14px 22px;border-top:1px solid #f3f4f6;display:flex;justify-content:flex-end;gap:10px">
            <button onclick="closeDetailModal()" style="padding:7px 16px;border:1px solid #d1d5db;border-radius:7px;background:#fff;font-size:12.5px;font-weight:600;color:#374151;cursor:pointer">Close</button>
            <button style="display:inline-flex;align-items:center;gap:5px;padding:7px 16px;background:#111827;color:#fff;border-radius:7px;font-size:12.5px;font-weight:600;border:none;cursor:pointer">
                <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                Select Vendor
            </button>
        </div>
    </div>
</div>
 
<script>
const prData = @json($requests->load('items','user')->keyBy('id'));

const steps = [
    {label:'PR\nSubmitted'},
    {label:'Vendor\nSearch\n(Purchasing)'},
    {label:'Vendor\nSelection'},
    {label:'Completed'},
];

function getStep(s){ return s==='completed'?4:s==='approved'?3:s==='in_process'?2:1; }

function buildProgressBar(status){
    const cur = getStep(status);
    const isFail = (status === 'rejected' || status === 'cancelled');
    
    return `<div style="display:flex;align-items:flex-start;gap:0;margin-bottom:20px">
        ${steps.map((s,i)=>{
            const n=i+1;
            let done = n < cur;
            let active = n === cur;
            
            if (status === 'completed' && n === 4) {
                done = true;
                active = false;
            }
            
            let cb = done ? '#22c55e' : active ? '#3b5bdb' : '#e5e7eb';
            let cc = done || active ? '#fff' : '#9ca3af';
            let lc = active ? '#3b5bdb' : done ? '#22c55e' : '#9ca3af';
            let circleText = done ? '✓' : n;

            if (isFail && active) {
                cb = status === 'rejected' ? '#ef4444' : '#9ca3af';
                lc = cb; 
                circleText = '✕';
            }
            
            const lineColor = done ? '#22c55e' : '#e5e7eb';

            return `<div style="display:flex;flex-direction:column;align-items:center;flex:1;position:relative">
                ${i>0?`<div style="position:absolute;top:14px;right:50%;width:100%;height:2px;background:${lineColor};z-index:0"></div>`:''}
                <div style="width:28px;height:28px;border-radius:50%;background:${cb};color:${cc};font-size:11px;font-weight:700;display:flex;align-items:center;justify-content:center;position:relative;z-index:1;flex-shrink:0">${circleText}</div>
                <div style="font-size:10.5px;font-weight:600;color:${lc};text-align:center;margin-top:5px;line-height:1.3;white-space:pre-line">${s.label}</div>
            </div>`;
        }).join('')}
    </div>`;
}
 
function openDetailModal(id){
    const pr=prData[id]; if(!pr)return;
    document.getElementById('modal-pr-title').textContent=pr.title||'Purchase Request';
    document.getElementById('modal-pr-sub').textContent=(pr.document_number||'')+' | '+(pr.plant||'');
    const items=pr.items||[];
    const itemRows=items.map((item,i)=>`<tr>
        <td style="padding:9px 12px;border-bottom:1px solid #f9fafb;color:#6b7280">${i+1}</td>
        <td style="padding:9px 12px;border-bottom:1px solid #f9fafb;font-family:'Courier New',monospace;font-size:11.5px;color:#3b5bdb;font-weight:600">${item.item_id||'—'}</td>
        <td style="padding:9px 12px;border-bottom:1px solid #f9fafb;font-weight:500;color:#111827">${item.item_name}</td>
        <td style="padding:9px 12px;border-bottom:1px solid #f9fafb;font-size:11.5px;color:#6b7280">${item.specification||'—'}</td>
        <td style="padding:9px 12px;border-bottom:1px solid #f9fafb;color:#374151">${item.quantity}</td>
        <td style="padding:9px 12px;border-bottom:1px solid #f9fafb;color:#374151">${item.unit}</td>
        <td style="padding:9px 12px;border-bottom:1px solid #f9fafb;color:#9ca3af">—</td>
        <td style="padding:9px 12px;border-bottom:1px solid #f9fafb;color:#9ca3af">—</td>
    </tr>`).join('')||'<tr><td colspan="8" style="padding:16px;text-align:center;color:#9ca3af">No items</td></tr>';
    const subDate=pr.created_at?new Date(pr.created_at).toLocaleDateString('id-ID',{day:'2-digit',month:'short',year:'numeric'}):'—';
    document.getElementById('modal-body-content').innerHTML=`
        <div style="background:#f9fafb;border-radius:8px;padding:12px 14px;margin-bottom:16px">
            <div style="font-size:10px;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.08em;margin-bottom:10px">Progress Status</div>
            ${buildProgressBar(pr.status)}
        </div>
        <div style="background:#f9fafb;border-radius:8px;padding:12px 14px;margin-bottom:16px">
            <div style="font-size:10px;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.08em;margin-bottom:10px">Request Information</div>
            <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:10px">
                <div><div style="font-size:10px;color:#9ca3af;text-transform:uppercase;font-weight:600;margin-bottom:3px">Submission Date</div><div style="font-weight:500;font-size:12.5px">${subDate}</div></div>
                <div><div style="font-size:10px;color:#9ca3af;text-transform:uppercase;font-weight:600;margin-bottom:3px">Department</div><div style="font-weight:500;font-size:12.5px">${pr.department||'—'}</div></div>
                <div><div style="font-size:10px;color:#9ca3af;text-transform:uppercase;font-weight:600;margin-bottom:3px">Requested By</div><div style="font-weight:500;font-size:12.5px">${pr.user?.name||'You'}</div></div>
                <div><div style="font-size:10px;color:#9ca3af;text-transform:uppercase;font-weight:600;margin-bottom:3px">Priority</div><div style="font-weight:500;font-size:12.5px">Normal</div></div>
                <div><div style="font-size:10px;color:#9ca3af;text-transform:uppercase;font-weight:600;margin-bottom:3px">Plant</div><div style="font-weight:500;font-size:12.5px">${pr.plant||'—'}</div></div>
            </div>
        </div>
        <div style="font-size:10px;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.08em;margin-bottom:8px">Item List</div>
        <div style="border:1px solid #e5e7eb;border-radius:8px;overflow:hidden;margin-bottom:16px">
            <table style="width:100%;border-collapse:collapse;font-size:12px">
                <thead><tr style="background:#f9fafb">
                    <th style="padding:8px 12px;text-align:left;font-size:10px;color:#9ca3af;font-weight:700;text-transform:uppercase">NO</th>
                    <th style="padding:8px 12px;text-align:left;font-size:10px;color:#9ca3af;font-weight:700;text-transform:uppercase">ITEM CODE</th>
                    <th style="padding:8px 12px;text-align:left;font-size:10px;color:#9ca3af;font-weight:700;text-transform:uppercase">ITEM NAME</th>
                    <th style="padding:8px 12px;text-align:left;font-size:10px;color:#9ca3af;font-weight:700;text-transform:uppercase">SPEC</th>
                    <th style="padding:8px 12px;text-align:left;font-size:10px;color:#9ca3af;font-weight:700;text-transform:uppercase">QTY</th>
                    <th style="padding:8px 12px;text-align:left;font-size:10px;color:#9ca3af;font-weight:700;text-transform:uppercase">UNIT</th>
                    <th style="padding:8px 12px;text-align:left;font-size:10px;color:#9ca3af;font-weight:700;text-transform:uppercase">UNIT PRICE (RP)</th>
                    <th style="padding:8px 12px;text-align:left;font-size:10px;color:#9ca3af;font-weight:700;text-transform:uppercase">TOTAL (RP)</th>
                </tr></thead>
                <tbody>${itemRows}</tbody>
                <tfoot><tr style="background:#f9fafb"><td colspan="7" style="padding:9px 12px;text-align:right;font-size:11.5px;font-weight:700;color:#374151;border-top:1px solid #e5e7eb">Total Request Value</td><td style="padding:9px 12px;font-weight:700;color:#374151;border-top:1px solid #e5e7eb">Rp —</td></tr></tfoot>
            </table>
        </div>
        <div style="font-size:10px;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.08em;margin-bottom:8px">Activity Log</div>
        <div style="display:flex;gap:10px;align-items:flex-start">
            <div style="width:6px;height:6px;background:#6b7280;border-radius:50%;margin-top:4px;flex-shrink:0"></div>
            <div><div style="font-size:12.5px;font-weight:500;color:#111827">PR created and submitted to supervisor</div><div style="font-size:11.5px;color:#9ca3af;margin-top:1px">${subDate} — ${pr.user?.name||'You'}</div></div>
        </div>`;
    const m=document.getElementById('detail-modal'); m.style.display='flex';
}
function closeDetailModal(){document.getElementById('detail-modal').style.display='none';}
document.getElementById('detail-modal').addEventListener('click',function(e){if(e.target===this)closeDetailModal();});

/* ══ TABLE ENGINE ══ */
function renderPager(id,eng,pages,start,end,total){
    const el=document.getElementById(id); if(!el)return;
    let btns='';
    for(let i=1;i<=pages;i++) btns+=`<button onclick="${eng.gotoFn}(${i})" style="min-width:28px;height:28px;border-radius:6px;border:1px solid ${i===eng.page?'#111827':'#e5e7eb'};background:${i===eng.page?'#111827':'#fff'};color:${i===eng.page?'#fff':'#374151'};font-size:12px;font-weight:600;cursor:pointer;padding:0 6px;">${i}</button>`;
    el.innerHTML=`<div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
        <span style="font-size:12px;color:#6b7280;">${total===0?'No results':`Showing ${start+1}–${end} of ${total}`}</span>
        <div style="display:flex;gap:4px;">
            <button onclick="${eng.gotoFn}(${eng.page-1})" ${eng.page<=1?'disabled':''} style="min-width:28px;height:28px;border-radius:6px;border:1px solid #e5e7eb;background:#fff;cursor:pointer;font-size:13px;opacity:${eng.page<=1?.35:1};">‹</button>
            ${btns}
            <button onclick="${eng.gotoFn}(${eng.page+1})" ${eng.page>=pages?'disabled':''} style="min-width:28px;height:28px;border-radius:6px;border:1px solid #e5e7eb;background:#fff;cursor:pointer;font-size:13px;opacity:${eng.page>=pages?.35:1};">›</button>
        </div>
        <select onchange="${eng.sizeFn}(this.value)" style="height:28px;border:1px solid #e5e7eb;border-radius:6px;font-size:12px;padding:0 6px;background:#fff;">
            ${[5,10,20].map(n=>`<option value="${n}" ${n===eng.pageSize?'selected':''}>${n} / page</option>`).join('')}
        </select>
    </div>`;
}

/* ── PR table ── */
const prEng = { page:1, pageSize:5, sortCol:null, sortDir:'asc',
    tbodyId:'dash-pr-tbody', pagerId:'dash-pr-pager',
    gotoFn:'dashPRGoto', sizeFn:'dashPRPageSz' };

function applyDashPR(){
    const q      = (document.getElementById('dash-pr-search')?.value||'').toLowerCase();
    const cat    = document.getElementById('dash-pr-category')?.value||'';
    const status = document.getElementById('dash-pr-status')?.value||'';
    let rows = Array.from(document.querySelectorAll('#dash-pr-tbody tr[data-status]'));
    let filtered = rows.filter(r=>{
        if(cat    && r.dataset.category !== cat)   return false;
        if(status && r.dataset.status   !== status) return false;
        if(q && !r.textContent.toLowerCase().includes(q)) return false;
        return true;
    });
    if(prEng.sortCol!==null){
        filtered.sort((a,b)=>{
            const at=(a.querySelectorAll('td')[prEng.sortCol]?.textContent||'').trim();
            const bt=(b.querySelectorAll('td')[prEng.sortCol]?.textContent||'').trim();
            return prEng.sortDir==='asc'?at.localeCompare(bt,'id'):bt.localeCompare(at,'id');
        });
    }
    rows.forEach(r=>r.style.display='none');
    const pages=Math.max(1,Math.ceil(filtered.length/prEng.pageSize));
    if(prEng.page>pages)prEng.page=1;
    const start=(prEng.page-1)*prEng.pageSize, end=Math.min(prEng.page*prEng.pageSize,filtered.length);
    const tbody=document.getElementById('dash-pr-tbody');
    filtered.slice(start,end).forEach(r=>{r.style.display='';tbody.appendChild(r);});
    const empty=document.getElementById('dash-pr-empty');
    if(empty) empty.style.display=filtered.length===0?'':'none';
    renderPager('dash-pr-pager',prEng,pages,start,end,filtered.length);
}
function dashPRSortFn(col){
    if(prEng.sortCol===col)prEng.sortDir=prEng.sortDir==='asc'?'desc':'asc';
    else{prEng.sortCol=col;prEng.sortDir='asc';}
    document.querySelectorAll('[id^="dps"]').forEach(el=>el.textContent='↕');
    const el=document.getElementById('dps'+col);if(el)el.textContent=prEng.sortDir==='asc'?'↑':'↓';
    applyDashPR();
}
function dashPRGoto(p){prEng.page=p;applyDashPR();}
function dashPRPageSz(s){prEng.pageSize=parseInt(s);prEng.page=1;applyDashPR();}

/* ── History table ── */
const hEng = { page:1, pageSize:5, sortCol:null, sortDir:'asc',
    tbodyId:'dash-h-tbody', pagerId:'dash-h-pager',
    gotoFn:'dashHGoto', sizeFn:'dashHPageSz' };

function applyDashH(){
    const q      = (document.getElementById('dash-h-search')?.value||'').toLowerCase();
    const dept   = document.getElementById('dash-h-dept')?.value||'';
    const status = document.getElementById('dash-h-status')?.value||'';
    let rows = Array.from(document.querySelectorAll('#dash-h-tbody tr[data-dept]'));
    let filtered = rows.filter(r=>{
        if(dept   && r.dataset.dept   !== dept)   return false;
        if(status && r.dataset.status !== status) return false;
        if(q && !r.textContent.toLowerCase().includes(q)) return false;
        return true;
    });
    if(hEng.sortCol!==null){
        filtered.sort((a,b)=>{
            const at=(a.querySelectorAll('td')[hEng.sortCol]?.textContent||'').trim();
            const bt=(b.querySelectorAll('td')[hEng.sortCol]?.textContent||'').trim();
            const an=parseFloat(at.replace(/[^0-9.]/g,'')),bn=parseFloat(bt.replace(/[^0-9.]/g,''));
            const cmp=(!isNaN(an)&&!isNaN(bn))?an-bn:at.localeCompare(bt,'id');
            return hEng.sortDir==='asc'?cmp:-cmp;
        });
    }
    rows.forEach(r=>r.style.display='none');
    const pages=Math.max(1,Math.ceil(filtered.length/hEng.pageSize));
    if(hEng.page>pages)hEng.page=1;
    const start=(hEng.page-1)*hEng.pageSize, end=Math.min(hEng.page*hEng.pageSize,filtered.length);
    const tbody=document.getElementById('dash-h-tbody');
    filtered.slice(start,end).forEach(r=>{r.style.display='';tbody.appendChild(r);});
    const empty=document.getElementById('dash-h-empty');
    if(empty) empty.style.display=filtered.length===0?'':'none';
    renderPager('dash-h-pager',hEng,pages,start,end,filtered.length);
}
function dashHSortFn(col){
    if(hEng.sortCol===col)hEng.sortDir=hEng.sortDir==='asc'?'desc':'asc';
    else{hEng.sortCol=col;hEng.sortDir='asc';}
    document.querySelectorAll('[id^="dhs"]').forEach(el=>el.textContent='↕');
    const el=document.getElementById('dhs'+col);if(el)el.textContent=hEng.sortDir==='asc'?'↑':'↓';
    applyDashH();
}
function dashHGoto(p){hEng.page=p;applyDashH();}
function dashHPageSz(s){hEng.pageSize=parseInt(s);hEng.page=1;applyDashH();}

document.addEventListener('DOMContentLoaded',()=>{ applyDashPR(); applyDashH(); });
</script>
@endsection