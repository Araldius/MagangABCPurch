@extends('layouts.app')
@php
    $pageTitle='PR & SR List';
    $statusCfg=[
        'awaiting_approval'=>['Awaiting Approval','#fff7ed','#c2410c','#f97316'],
        'in_process'=>['In Process','#f0f9ff','#0369a1','#0ea5e9'],
        'approved'=>['Approved','#f0fdf4','#15803d','#22c55e'],
        'completed'=>['Completed','#eff6ff','#1d4ed8','#3b82f6'],
        'rejected'=>['Rejected','#fef2f2','#b91c1c','#ef4444'],
        'cancelled'=>['Cancelled','#f3f4f6','#374151','#9ca3af']
    ];
@endphp
@section('content')

<div style="margin-bottom:20px">
    <h1 style="font-size:20px;font-weight:700;color:#111827;margin:0 0 3px">Procurement List</h1>
    <p style="font-size:12.5px;color:#6b7280;margin:0">{{ $isPurchasing?'All purchase & service requests from all departments.':'All your submitted requests.' }}</p>
</div>

<div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px">
    <div style="display:flex;align-items:center;justify-content:space-between;padding:16px 20px;border-bottom:1px solid #f3f4f6;flex-wrap:wrap;gap:10px">
        <span style="font-size:14px;font-weight:700;color:#111827">All Requests</span>
        <a href="{{ route('purchase_requests.create') }}" style="padding:6px 14px;background:#111827;color:#fff;border-radius:7px;font-size:12.5px;font-weight:600;text-decoration:none">+ New Request</a>
    </div>

    {{-- Toolbar Filter --}}
    <div style="display:flex;gap:8px;align-items:center;padding:12px 20px;border-bottom:1px solid #f3f4f6;flex-wrap:wrap;">
        <input type="text" id="pr-search" placeholder="Search doc, title..." oninput="applyFilters()" style="height:32px;border:1px solid #e5e7eb;border-radius:7px;padding:0 10px;font-size:12.5px;width:200px;outline:none;">
        <select id="type-filter" onchange="applyFilters()" style="height:32px;padding:0 28px 0 10px;border:1px solid #e5e7eb;border-radius:7px;font-size:12.5px;background:#fff;cursor:pointer;">
            <option value="">All Types</option>
            <option value="goods">📦 Goods</option>
            <option value="service">🔧 Service</option>
        </select>

        @if($isPurchasing)
        <select id="dept-filter" onchange="applyFilters()" style="height:32px;padding:0 28px 0 10px;border:1px solid #e5e7eb;border-radius:7px;font-size:12.5px;background:#fff;cursor:pointer;">
            <option value="">All Dept.</option>
            @foreach($allRequests->pluck('department')->unique()->filter()->sort()->values() as $dept)
            <option value="{{ $dept }}">{{ $dept }}</option>
            @endforeach
        </select>
        @endif

        <select id="status-filter" onchange="applyFilters()" style="height:32px;padding:0 28px 0 10px;border:1px solid #e5e7eb;border-radius:7px;font-size:12.5px;background:#fff;cursor:pointer;">
            <option value="">All Status</option>
            <option value="awaiting_approval">Awaiting Approval</option>
            <option value="in_process">In Process</option>
            <option value="approved">Approved</option>
            <option value="completed">Completed</option>
            <option value="rejected">Rejected</option>
            <option value="cancelled">Cancelled</option>
        </select>
    </div>

    <div style="overflow-x:auto">
        <table id="pr-table" style="width:100%;border-collapse:collapse;font-size:12.5px">
            <thead>
                <tr style="background:#f9fafb">
                    <th onclick="prSortFn(0)" style="padding:9px 20px;text-align:left;font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase;cursor:pointer;white-space:nowrap">DOC NO. <span id="prs0">↕</span></th>
                    <th onclick="prSortFn(1)" style="padding:9px 14px;text-align:left;font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase;cursor:pointer;">DESCRIPTION <span id="prs1">↕</span></th>
                    <th style="padding:9px 14px;text-align:left;font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase;">CATEGORY</th>
                    @if($isPurchasing)
                    <th onclick="prSortFn(3)" style="padding:9px 14px;text-align:left;font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase;cursor:pointer;">REQUESTER <span id="prs3">↕</span></th>
                    @endif
                    <th style="padding:9px 14px;text-align:left;font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase;">TYPE/ITEMS</th>
                    <th onclick="prSortFn({{ $isPurchasing?5:4 }})" style="padding:9px 14px;text-align:left;font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase;cursor:pointer;">STATUS <span id="prs{{ $isPurchasing?5:4 }}">↕</span></th>
                    <th onclick="prSortFn({{ $isPurchasing?6:5 }})" style="padding:9px 14px;text-align:left;font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase;cursor:pointer;white-space:nowrap">SUBMITTED <span id="prs{{ $isPurchasing?6:5 }}">↕</span></th>
                    <th style="padding:9px 14px;text-align:left;font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase;white-space:nowrap">LAST UPDATE</th>
                    <th style="padding:9px 14px;text-align:left;font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase;">ACTION</th>
                </tr>
            </thead>
            <tbody id="pr-tbody">
                @forelse($allRequests as $pr)
                @php
                    [$sLabel,$sBg,$sText,$sDot]=$statusCfg[$pr->status]??[ucfirst(str_replace('_',' ',$pr->status)),'#f3f4f6','#374151','#9ca3af'];
                    $upd=$pr->updated_at;
                    $lu=$upd->isToday()?'Today, '.$upd->format('H:i'):($upd->isYesterday()?'Yesterday, '.$upd->format('H:i'):$upd->format('d M').', '.$upd->format('H:i'));

                    $prCategory = $pr->type
                        ?? ($pr->service_name ? 'service' : null)
                        ?? (str_contains(strtolower(class_basename($pr)), 'service') ? 'service' : 'goods');

                    $displayTitle = $pr->display_title ?? $pr->title ?? $pr->service_name ?? '—';

                    $displayDoc = $pr->display_doc
                        ?? $pr->document_number
                        ?? (($prCategory === 'service' ? 'SR-' : 'PR-') . str_pad($pr->id, 4, '0', STR_PAD_LEFT));

                    if($prCategory === 'service') {
                        $itemCount = $pr->item_count ?? 0;
                        if(!$itemCount && method_exists($pr, 'jobs') && $pr->jobs) {
                            foreach($pr->jobs as $job) { $itemCount += $job->items ? $job->items->count() : 0; }
                        }
                        $qtyLabel = $itemCount . ' item(s)';
                    } else {
                        $qtyLabel = ($pr->item_count ?? (method_exists($pr, 'items') && $pr->items ? $pr->items->count() : 0)) . ' item(s)';
                    }
                @endphp
                <tr data-status="{{ $pr->status }}" data-dept="{{ $pr->department ?? 'General' }}" data-type="{{ $prCategory }}" style="border-bottom:1px solid #f3f4f6" onmouseover="this.style.background='#fafafa'" onmouseout="this.style.background='transparent'">

                    <td style="padding:13px 20px">
                        <span style="font-family:monospace;font-size:12px;font-weight:600;">{{ $displayDoc }}</span>
                    </td>

                    <td style="padding:13px 14px;max-width:200px">
                        <div style="font-weight:500;">{{ $displayTitle }}</div>
                        <div style="font-size:11px;color:#9ca3af;">{{ $pr->plant }}</div>
                    </td>

                    <td style="padding:13px 14px;">
                        @if($prCategory === 'service')
                        <span style="padding:3px 8px;border-radius:6px;font-size:11px;font-weight:600;background:#e0e7ff;color:#3730a3;">🔧 Service</span>
                        @else
                        <span style="padding:3px 8px;border-radius:6px;font-size:11px;font-weight:600;background:#f1f5f9;color:#475569;">📦 Goods</span>
                        @endif
                    </td>

                    @if($isPurchasing)
                    <td style="padding:13px 14px">
                        <div style="font-weight:500;">{{ optional($pr->user)->name??'—' }}</div>
                        <div style="font-size:11px;color:#9ca3af;">{{ $pr->department ?? '—' }}</div>
                    </td>
                    @endif

                    <td style="padding:13px 14px;">{{ $qtyLabel }}</td>

                    <td style="padding:13px 14px">
                        <span style="display:inline-flex;align-items:center;gap:4px;padding:3px 9px;border-radius:999px;background:{{ $sBg }};font-size:11.5px;font-weight:600;color:{{ $sText }};white-space:nowrap">
                            <span style="width:5px;height:5px;border-radius:50%;background:{{ $sDot }}"></span>{{ $sLabel }}
                        </span>
                    </td>

                    <td style="padding:13px 14px;color:#6b7280;white-space:nowrap">{{ \Carbon\Carbon::parse($pr->submission_date ?? $pr->created_at)->format('d M Y') }}</td>
                    <td style="padding:13px 14px;color:#6b7280;white-space:nowrap">{{ $lu }}</td>
                    <td style="padding:13px 14px">
                        <button onclick="openPRDetail({{ $pr->id }}, '{{ $prCategory }}')" style="padding:4px 12px;border:1px solid #d1d5db;border-radius:6px;background:#fff;cursor:pointer;">Detail</button>
                    </td>
                </tr>
                @empty
                <tr id="pr-empty"><td colspan="{{ $isPurchasing?9:8 }}" style="text-align:center;padding:36px 20px;color:#9ca3af;">No requests found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div id="pr-pager" style="padding:12px 20px;border-top:1px solid #f3f4f6;"></div>
</div>

{{-- DETAIL MODAL --}}
<div id="pr-detail-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:200;align-items:center;justify-content:center;padding:20px">
    <div style="background:#fff;border-radius:14px;width:100%;max-width:700px;max-height:88vh;display:flex;flex-direction:column;box-shadow:0 8px 40px rgba(0,0,0,.12)">
        <div style="padding:18px 22px;border-bottom:1px solid #f3f4f6;display:flex;justify-content:space-between;">
            <div>
                <div id="detail-title" style="font-size:15px;font-weight:700;"></div>
                <div id="detail-sub" style="font-size:12px;color:#3b5bdb;"></div>
            </div>
            <button onclick="closePRDetail()" style="background:none;border:none;cursor:pointer;font-size:20px;">&times;</button>
        </div>
        <div id="detail-body" style="padding:18px 22px;overflow-y:auto;flex:1;"></div>
    </div>
</div>

<script>
@php
    foreach($allRequests as $req) {
        $req->loadMissing('user');
        if (method_exists($req, 'jobs')) { $req->loadMissing('jobs.items'); }
        if (method_exists($req, 'items')) { $req->loadMissing('items'); }
    }
@endphp
const allPRs = @json($allRequests->keyBy('id')->toArray());
const isPurchasing = {{ $isPurchasing ? 'true' : 'false' }};
const prEng = { page:1, pageSize:10, sortCol:null, sortDir:'asc', gotoFn:'prGoto', sizeFn:'prPageSz' };

// Auto-detects number → date → string and applies direction
function smartCompare(a, b, dir) {
    const aStripped = a.replace(/[^0-9.-]/g, '');
    const bStripped = b.replace(/[^0-9.-]/g, '');
    const an = parseFloat(aStripped);
    const bn = parseFloat(bStripped);
    let cmp;
    if (aStripped !== '' && bStripped !== '' && !isNaN(an) && !isNaN(bn)) {
        cmp = an - bn;
    } else {
        const da = new Date(a), db = new Date(b);
        cmp = (!isNaN(da.getTime()) && !isNaN(db.getTime())) ? da - db : a.localeCompare(b, 'id');
    }
    return dir === 'asc' ? cmp : -cmp;
}

function renderPager(id, eng, total, start, end, pages) {
    const pager = document.getElementById(id);
    if (!pager) return;
    let btns = '';
    for (let i = 1; i <= pages; i++)
        btns += `<button onclick="${eng.gotoFn}(${i})" style="margin:0 2px;padding:3px 8px;background:${i===eng.page?'#111827':'#fff'};color:${i===eng.page?'#fff':'#000'};border:1px solid #d1d5db;border-radius:4px;cursor:pointer;">${i}</button>`;
    pager.innerHTML = `<div style="display:flex;justify-content:space-between;align-items:center;font-size:12px;color:#6b7280;">
        <span>Showing ${total===0?0:start+1}-${end} of ${total} entries</span>
        <div style="display:flex;align-items:center;gap:10px;">
            <div>${btns}</div>
            <select onchange="${eng.sizeFn}(this.value)" style="height:28px;border:1px solid #e5e7eb;border-radius:6px;font-size:12px;padding:0 6px;background:#fff;cursor:pointer;">
                ${[5,10,20,50].map(n=>`<option value="${n}" ${n===eng.pageSize?'selected':''}>${n} / page</option>`).join('')}
            </select>
        </div>
    </div>`;
}

function applyFilters() {
    const q      = (document.getElementById('pr-search')?.value   || '').toLowerCase();
    const type   =  document.getElementById('type-filter')?.value  || '';
    const status =  document.getElementById('status-filter')?.value || '';
    const dept   = isPurchasing ? (document.getElementById('dept-filter')?.value || '') : '';

    const tbody    = document.getElementById('pr-tbody');
    const allRows  = Array.from(tbody.querySelectorAll('tr[data-status]'));
    const emptyRow = document.getElementById('pr-empty');

    // 1. Filter
    let filtered = allRows.filter(r => {
        if (status && r.dataset.status !== status) return false;
        if (type   && r.dataset.type   !== type)   return false;
        if (dept   && r.dataset.dept   !== dept)   return false;
        if (q      && !r.textContent.toLowerCase().includes(q)) return false;
        return true;
    });

    // 2. Sort — re-orders the JS array
    if (prEng.sortCol !== null) {
        filtered.sort((a, b) => {
            const at = (a.querySelectorAll('td')[prEng.sortCol]?.textContent || '').trim();
            const bt = (b.querySelectorAll('td')[prEng.sortCol]?.textContent || '').trim();
            return smartCompare(at, bt, prEng.sortDir);
        });
    }

    // 3. Pagination math
    const pages = Math.max(1, Math.ceil(filtered.length / prEng.pageSize));
    if (prEng.page > pages) prEng.page = 1;
    const start = (prEng.page - 1) * prEng.pageSize;
    const end   = Math.min(prEng.page * prEng.pageSize, filtered.length);

    // 4. Hide all data rows
    allRows.forEach(r => r.style.display = 'none');

    if (filtered.length === 0) {
        if (emptyRow) emptyRow.style.display = '';
    } else {
        if (emptyRow) emptyRow.style.display = 'none';
        // 5. Re-append in sorted order — actually reorders the DOM
        filtered.forEach(r => tbody.appendChild(r));
        // 6. Show only the current page slice
        filtered.slice(start, end).forEach(r => r.style.display = '');
    }

    // Keep empty row pinned at bottom
    if (emptyRow) tbody.appendChild(emptyRow);

    renderPager('pr-pager', prEng, filtered.length, start, end, pages);
}

function prSortFn(col) {
    if (prEng.sortCol === col) {
        prEng.sortDir = prEng.sortDir === 'asc' ? 'desc' : 'asc';
    } else {
        prEng.sortCol = col;
        prEng.sortDir = 'asc';
    }
    // Reset all sort indicators, then set the active one
    document.querySelectorAll('[id^="prs"]').forEach(el => el.textContent = '↕');
    const el = document.getElementById('prs' + col);
    if (el) el.textContent = prEng.sortDir === 'asc' ? '↑' : '↓';
    applyFilters();
}

function prGoto(p)   { prEng.page = p;               applyFilters(); }
function prPageSz(s) { prEng.pageSize = parseInt(s); prEng.page = 1; applyFilters(); }

function openPRDetail(id, category) {
    const pr = allPRs[id];
    if (!pr) return;
    document.getElementById('detail-title').textContent = pr.display_title || pr.title || pr.service_name || 'Request Detail';
    document.getElementById('detail-sub').textContent   = (pr.display_doc || pr.document_number || (category==='service'?'SR-':'PR-')+String(pr.id).padStart(4,'0')) + ' | ' + (pr.plant||'');

    let itemRows = '';
    if (category === 'service' || pr.type === 'service' || pr.jobs) {
        (pr.jobs||[]).forEach(job => {
            itemRows += `<tr><td colspan="6" style="background:#f3f4f6;padding:6px 12px;font-weight:700;font-size:11.5px;">💼 JOB: ${job.job_description}</td></tr>`;
            (job.items||[]).forEach((it, i) => {
                itemRows += `<tr>
                    <td style="padding:9px 12px;border-bottom:1px solid #f9fafb;">${i+1}</td>
                    <td style="padding:9px 12px;border-bottom:1px solid #f9fafb;color:#9ca3af;">—</td>
                    <td style="padding:9px 12px;border-bottom:1px solid #f9fafb;font-weight:500;">${it.item_name||it.name||'—'}</td>
                    <td style="padding:9px 12px;border-bottom:1px solid #f9fafb;">${it.specification||'—'}</td>
                    <td style="padding:9px 12px;border-bottom:1px solid #f9fafb;">${it.quantity}</td>
                    <td style="padding:9px 12px;border-bottom:1px solid #f9fafb;">${it.unit}</td>
                </tr>`;
            });
        });
    } else {
        itemRows = (pr.items||[]).map((it, i) => `<tr>
            <td style="padding:9px 12px;border-bottom:1px solid #f9fafb;">${i+1}</td>
            <td style="padding:9px 12px;border-bottom:1px solid #f9fafb;font-family:monospace;color:#3b5bdb;font-weight:600">${it.item_id||it.item_code||'—'}</td>
            <td style="padding:9px 12px;border-bottom:1px solid #f9fafb;font-weight:500;">${it.item_name||it.name||'—'}</td>
            <td style="padding:9px 12px;border-bottom:1px solid #f9fafb;">${it.specification||'—'}</td>
            <td style="padding:9px 12px;border-bottom:1px solid #f9fafb;">${it.quantity}</td>
            <td style="padding:9px 12px;border-bottom:1px solid #f9fafb;">${it.unit}</td>
        </tr>`).join('');
    }

    const subDate = new Date(pr.submission_date||pr.created_at).toLocaleDateString('id-ID',{day:'2-digit',month:'short',year:'numeric'});
    document.getElementById('detail-body').innerHTML = `
        <div style="border:1px solid #e5e7eb;border-radius:8px;overflow:hidden;margin-bottom:16px">
            <table style="width:100%;border-collapse:collapse;font-size:12px">
                <thead><tr style="background:#f9fafb">
                    <th style="padding:8px 12px;text-align:left;">NO</th>
                    <th style="padding:8px 12px;text-align:left;">ITEM ID</th>
                    <th style="padding:8px 12px;text-align:left;">ITEM NAME</th>
                    <th style="padding:8px 12px;text-align:left;">SPEC</th>
                    <th style="padding:8px 12px;text-align:left;">QTY</th>
                    <th style="padding:8px 12px;text-align:left;">UNIT</th>
                </tr></thead>
                <tbody>${itemRows||'<tr><td colspan="6" style="text-align:center;padding:16px;">No items</td></tr>'}</tbody>
            </table>
        </div>
        <div style="font-size:12px;color:#111827;">Submitted on ${subDate} by ${pr.user?.name||'System'}</div>`;
    document.getElementById('pr-detail-modal').style.display = 'flex';
}

function closePRDetail() { document.getElementById('pr-detail-modal').style.display = 'none'; }

document.addEventListener('DOMContentLoaded', applyFilters);
</script>
@endsection