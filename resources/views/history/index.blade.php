@extends('layouts.app')
@php $pageTitle = 'Procurement History'; @endphp
@section('content')
 
<div style="margin-bottom:20px">
    <h1 style="font-size:20px;font-weight:700;color:#111827;margin:0 0 3px">Procurement History</h1>
    <p style="font-size:12.5px;color:#6b7280;margin:0">All purchase requests and completed procurement records.</p>
</div>
 
{{-- STAT CARDS --}}
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:20px">
    <div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:18px 20px">
        <div style="font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.07em">Vendors Used</div>
        <div style="font-size:28px;font-weight:800;color:#111827;margin:8px 0 5px;line-height:1">{{ $vendorsUsed }}</div>
        <div style="font-size:11.5px;color:#9ca3af">Throughout {{ now()->year }}</div>
    </div>
    <div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:18px 20px">
        <div style="font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.07em">Total Value</div>
        <div style="font-size:22px;font-weight:800;color:#111827;margin:8px 0 5px;line-height:1">Rp {{ number_format($totalValue/1000000,0) }} Jt</div>
        <div style="font-size:11.5px;color:#9ca3af">Jan–{{ now()->format('M Y') }}</div>
    </div>
    <div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:18px 20px">
        <div style="font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.07em">PR Completed</div>
        <div style="font-size:28px;font-weight:800;color:#16a34a;margin:8px 0 5px;line-height:1">{{ $prsCompleted }}</div>
        <div style="font-size:11.5px;color:#9ca3af">Year {{ now()->year }}</div>
    </div>
    <div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:18px 20px">
        <div style="font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.07em">Avg. Lead Time</div>
        <div style="font-size:28px;font-weight:800;color:#d97706;margin:8px 0 5px;line-height:1">{{ $avgLeadDays }} Days</div>
        <div style="font-size:11.5px;color:#9ca3af">PR to goods received</div>
    </div>
</div>
 
{{-- TABLE --}}
<div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px">
    <div style="display:flex;align-items:center;justify-content:space-between;padding:16px 20px;border-bottom:1px solid #f3f4f6;gap:10px;flex-wrap:wrap">
        <div style="font-size:14px;font-weight:700;color:#111827">Vendor &amp; Procurement Records</div>
    </div>

    {{-- Toolbar --}}
    <div style="display:flex;gap:8px;align-items:center;padding:12px 20px;border-bottom:1px solid #f3f4f6;flex-wrap:wrap;">
        <input type="text" id="hist-search" placeholder="Search vendor, doc number..."
            oninput="applyHFilters()"
            style="height:32px;border:1px solid #e5e7eb;border-radius:7px;padding:0 10px;font-size:12.5px;width:200px;outline:none;font-family:inherit;">
        <div style="position:relative;">
            <select id="unit-filter" onchange="applyHFilters()"
                style="height:32px;padding:0 28px 0 10px;border:1px solid #e5e7eb;border-radius:7px;font-size:12.5px;color:#374151;background:#fff;appearance:none;cursor:pointer;font-family:inherit;">
                <option value="">All Units</option>
                <option value="IT">IT</option>
                <option value="Produksi">Produksi</option>
                <option value="Finance">Finance</option>
                <option value="Maintenance">Maintenance</option>
            </select>
            <svg style="position:absolute;right:8px;top:50%;transform:translateY(-50%);pointer-events:none;color:#6b7280" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 9l6 6 6-6" stroke-linecap="round"/></svg>
        </div>
        <div style="position:relative;">
            <select id="hist-status-filter" onchange="applyHFilters()"
                style="height:32px;padding:0 28px 0 10px;border:1px solid #e5e7eb;border-radius:7px;font-size:12.5px;color:#374151;background:#fff;appearance:none;cursor:pointer;font-family:inherit;">
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
                    <th onclick="histSort(0)" style="padding:9px 20px;text-align:left;font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.06em;cursor:pointer;">DOC NO. <span id="hs0" style="font-size:9px;">↕</span></th>
                    <th onclick="histSort(1)" style="padding:9px 14px;text-align:left;font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.06em;cursor:pointer;">VENDOR NAME <span id="hs1" style="font-size:9px;">↕</span></th>
                    <th onclick="histSort(2)" style="padding:9px 14px;text-align:left;font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.06em;cursor:pointer;">UNIT / DEPT. <span id="hs2" style="font-size:9px;">↕</span></th>
                    <th style="padding:9px 14px;text-align:left;font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.06em;">ITEM</th>
                    <th style="padding:9px 14px;text-align:left;font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.06em;">QTY</th>
                    <th onclick="histSort(5)" style="padding:9px 14px;text-align:left;font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.06em;cursor:pointer;">VALUE (RP) <span id="hs5" style="font-size:9px;">↕</span></th>
                    <th onclick="histSort(6)" style="padding:9px 14px;text-align:left;font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.06em;cursor:pointer;">LEAD TIME <span id="hs6" style="font-size:9px;">↕</span></th>
                    <th style="padding:9px 14px;text-align:left;font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.06em;">STATUS</th>
                </tr>
            </thead>
            <tbody id="hist-tbody">
                @forelse($records as $rec)
                @php
                    $sCfg=[
                        'awaiting_approval'=>['Awaiting Approval','#fff7ed','#c2410c','#f97316'],
                        'in_process'=>['In Process','#f0f9ff','#0369a1','#0ea5e9'],
                        'approved'=>['Approved','#f0fdf4','#15803d','#22c55e'],
                        'completed'=>['Completed','#eff6ff','#1d4ed8','#3b82f6'],
                        'rejected'=>['Rejected','#fef2f2','#b91c1c','#ef4444'],
                        'cancelled'=>['Cancelled','#f3f4f6','#374151','#9ca3af']
                    ];
                    [$sLabel,$sBg,$sText,$sDot]=$sCfg[$rec->status]??[ucfirst($rec->status),'#eff6ff','#1d4ed8','#3b82f6'];
                    $firstItem=$rec->items->first();
                    $deptColors=['Maintenance'=>['#e0f2fe','#0369a1'],'Produksi'=>['#dcfce7','#15803d'],'Production'=>['#dcfce7','#15803d'],'IT'=>['#ede9fe','#7c3aed'],'IT & Digital'=>['#ede9fe','#7c3aed'],'Finance'=>['#fef9c3','#92400e']];
                    [$dBg,$dText]=$deptColors[$rec->department]??['#f1f5f9','#475569'];
                @endphp
                <tr data-dept="{{ $rec->department }}" data-status="{{ $rec->status }}"
                    style="border-bottom:1px solid #f3f4f6"
                    onmouseover="this.style.background='#fafafa'" onmouseout="this.style.background='transparent'">
                    <td style="padding:13px 20px"><span style="font-family:'Courier New',monospace;font-size:12px;font-weight:600;color:#111827">{{ $rec->doc_number }}</span></td>
                    <td style="padding:13px 14px">
                        <div style="font-size:12.5px;font-weight:500;color:#111827">{{ $rec->vendor_name }}</div>
                        <div style="font-size:11px;color:#9ca3af;margin-top:1px">{{ $rec->vendor_city }}</div>
                    </td>
                    <td style="padding:13px 14px"><span style="display:inline-flex;align-items:center;padding:2px 9px;border-radius:6px;font-size:11.5px;font-weight:600;background:{{ $dBg }};color:{{ $dText }}">{{ $rec->department }}</span></td>
                    <td style="padding:13px 14px;font-size:12.5px;color:#111827">{{ optional($firstItem)->name??'—' }}</td>
                    <td style="padding:13px 14px;font-size:12px;color:#374151">{{ optional($firstItem)->quantity??'—' }} {{ optional($firstItem)->unit??'' }}</td>
                    <td style="padding:13px 14px;font-size:12.5px;font-weight:600;color:#111827">{{ $rec->total_value > 0 ? number_format($rec->total_value,0,',','.') : '—' }}</td>
                    <td style="padding:13px 14px;font-size:12px;color:#6b7280">{{ $rec->lead_days ? $rec->lead_days.' days' : '—' }}</td>
                    <td style="padding:13px 14px"><span style="display:inline-flex;align-items:center;gap:4px;padding:3px 9px;border-radius:999px;background:{{ $sBg }};font-size:11.5px;font-weight:600;color:{{ $sText }};white-space:nowrap"><span style="width:5px;height:5px;border-radius:50%;background:{{ $sDot }}"></span>{{ $sLabel }}</span></td>
                </tr>
                @empty
                <tr id="hist-empty"><td colspan="8" style="text-align:center;padding:36px 20px;color:#9ca3af;font-size:12.5px">No procurement records found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div id="hist-pager" style="padding:12px 20px;border-top:1px solid #f3f4f6;"></div>
</div>

<script>
let histSortState = { col: null, dir: 'asc' };
let histPage = 1, histPageSize = 10;

function applyHFilters() {
    const q      = (document.getElementById('hist-search')?.value || '').toLowerCase();
    const dept   = document.getElementById('unit-filter')?.value || '';
    const status = document.getElementById('hist-status-filter')?.value || '';

    let rows = Array.from(document.querySelectorAll('#hist-tbody tr[data-dept]'));

    let filtered = rows.filter(r => {
        if (dept   && r.dataset.dept   !== dept)   return false;
        if (status && r.dataset.status !== status) return false;
        if (q && !r.textContent.toLowerCase().includes(q)) return false;
        return true;
    });

    if (histSortState.col !== null) {
        filtered.sort((a, b) => {
            const at = (a.querySelectorAll('td')[histSortState.col]?.textContent || '').trim();
            const bt = (b.querySelectorAll('td')[histSortState.col]?.textContent || '').trim();
            const an = parseFloat(at.replace(/[^0-9.]/g,'')), bn = parseFloat(bt.replace(/[^0-9.]/g,''));
            const cmp = (!isNaN(an)&&!isNaN(bn)) ? an-bn : at.localeCompare(bt,'id');
            return histSortState.dir === 'asc' ? cmp : -cmp;
        });
    }

    rows.forEach(r => r.style.display = 'none');
    const empty = document.getElementById('hist-empty');
    const pages = Math.max(1, Math.ceil(filtered.length / histPageSize));
    if (histPage > pages) histPage = 1;
    const start = (histPage - 1) * histPageSize;
    const end   = Math.min(histPage * histPageSize, filtered.length);
    const tbody = document.getElementById('hist-tbody');
    filtered.slice(start, end).forEach(r => { r.style.display = ''; tbody.appendChild(r); });
    if (empty) empty.style.display = filtered.length === 0 ? '' : 'none';

    let btns = '';
    for (let i = 1; i <= pages; i++) {
        btns += `<button onclick="histGoto(${i})" style="min-width:28px;height:28px;border-radius:6px;border:1px solid ${i===histPage?'#111827':'#e5e7eb'};background:${i===histPage?'#111827':'#fff'};color:${i===histPage?'#fff':'#374151'};font-size:12px;font-weight:600;cursor:pointer;padding:0 6px;">${i}</button>`;
    }
    const pager = document.getElementById('hist-pager');
    if (pager) pager.innerHTML = `<div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
        <span style="font-size:12px;color:#6b7280;">${filtered.length===0?'No results':`Showing ${start+1}–${end} of ${filtered.length}`}</span>
        <div style="display:flex;gap:4px;">
            <button onclick="histGoto(${histPage-1})" ${histPage<=1?'disabled':''} style="min-width:28px;height:28px;border-radius:6px;border:1px solid #e5e7eb;background:#fff;cursor:pointer;font-size:13px;opacity:${histPage<=1?.35:1};">‹</button>
            ${btns}
            <button onclick="histGoto(${histPage+1})" ${histPage>=pages?'disabled':''} style="min-width:28px;height:28px;border-radius:6px;border:1px solid #e5e7eb;background:#fff;cursor:pointer;font-size:13px;opacity:${histPage>=pages?.35:1};">›</button>
        </div>
        <select onchange="histSetPageSize(this.value)" style="height:28px;border:1px solid #e5e7eb;border-radius:6px;font-size:12px;padding:0 6px;background:#fff;">
            ${[5,10,20].map(n=>`<option value="${n}" ${n===histPageSize?'selected':''}>${n} / page</option>`).join('')}
        </select>
    </div>`;
}

function histSort(col) {
    if (histSortState.col === col) histSortState.dir = histSortState.dir==='asc'?'desc':'asc';
    else { histSortState.col = col; histSortState.dir = 'asc'; }
    document.querySelectorAll('[id^="hs"]').forEach(el => el.textContent = '↕');
    const el = document.getElementById('hs'+col);
    if (el) el.textContent = histSortState.dir==='asc'?'↑':'↓';
    applyHFilters();
}
function histGoto(p) { histPage = p; applyHFilters(); }
function histSetPageSize(s) { histPageSize = parseInt(s); histPage = 1; applyHFilters(); }

document.addEventListener('DOMContentLoaded', applyHFilters);
</script>
@endsection