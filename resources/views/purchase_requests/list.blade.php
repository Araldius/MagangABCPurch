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
                        <button onclick="openPRDetail({{ $pr->id }}, '{{ $prCategory }}')" style="padding:4px 12px;border:1px solid #d1d5db;border-radius:6px;background:#fff;cursor:pointer;font-size:12px;">Detail</button>
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
    <div style="background:#fff;border-radius:14px;width:100%;max-width:820px;max-height:90vh;display:flex;flex-direction:column;box-shadow:0 8px 40px rgba(0,0,0,.12)">
        {{-- Header --}}
        <div style="padding:18px 22px;border-bottom:1px solid #f3f4f6;display:flex;justify-content:space-between;align-items:flex-start;">
            <div>
                <div id="detail-title" style="font-size:15px;font-weight:700;color:#111827"></div>
                <div id="detail-sub" style="font-size:12px;color:#3b5bdb;margin-top:2px"></div>
            </div>
            <button onclick="closePRDetail()" style="background:none;border:none;cursor:pointer;font-size:20px;color:#9ca3af;line-height:1;">&times;</button>
        </div>
        {{-- Meta (Priority / Plant) --}}
        <div id="detail-meta" style="display:flex;gap:32px;padding:10px 22px;background:#f9fafb;border-bottom:1px solid #f3f4f6;font-size:12px;"></div>
        {{-- Body --}}
        <div id="detail-body" style="padding:18px 22px;overflow-y:auto;flex:1;"></div>
        {{-- Footer --}}
        <div style="padding:14px 22px;border-top:1px solid #f3f4f6;display:flex;justify-content:space-between;align-items:center;">
            <button onclick="closePRDetail()" style="padding:7px 18px;border:1px solid #d1d5db;border-radius:7px;background:#fff;font-size:13px;cursor:pointer;color:#374151;">Close</button>
            <a id="detail-select-vendor-btn" href="#" style="padding:7px 18px;background:#1e3a5f;color:#fff;border-radius:7px;font-size:13px;font-weight:600;text-decoration:none;display:inline-flex;align-items:center;gap:6px;">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11" stroke-linecap="round" stroke-linejoin="round"/></svg>
                Select Vendor
            </a>
        </div>
    </div>
</div>

<script>
@php
    foreach($allRequests as $req) {
        $req->loadMissing('user');
        if (method_exists($req, 'jobs')) { $req->loadMissing('jobs.items'); }
        if (method_exists($req, 'items')) { $req->loadMissing('items'); }
        if (method_exists($req, 'rfqs')) { $req->loadMissing(['rfqs.vendorSelections.vendor','rfqs.vendorSelections.selectionItems','rfqs.histories.user']); }
    }
@endphp
{{-- FIX: key = type_id (e.g. "goods_1", "service_1") — prevents PR id=1 and SR id=1 overwriting each other --}}
const allPRs = @json(
    $allRequests->mapWithKeys(function($r) {
        return [($r->type ?? 'goods') . '_' . $r->id => $r];
    })->toArray()
);
const isPurchasing = {{ $isPurchasing ? 'true' : 'false' }};
const prEng = { page:1, pageSize:10, sortCol:null, sortDir:'asc', gotoFn:'prGoto', sizeFn:'prPageSz' };

function fmtRp(n) {
    if (!n && n !== 0) return '—';
    return 'Rp ' + Number(n).toLocaleString('id-ID');
}

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

    let filtered = allRows.filter(r => {
        if (status && r.dataset.status !== status) return false;
        if (type   && r.dataset.type   !== type)   return false;
        if (dept   && r.dataset.dept   !== dept)   return false;
        if (q      && !r.textContent.toLowerCase().includes(q)) return false;
        return true;
    });

    if (prEng.sortCol !== null) {
        filtered.sort((a, b) => {
            const at = (a.querySelectorAll('td')[prEng.sortCol]?.textContent || '').trim();
            const bt = (b.querySelectorAll('td')[prEng.sortCol]?.textContent || '').trim();
            return smartCompare(at, bt, prEng.sortDir);
        });
    }

    const pages = Math.max(1, Math.ceil(filtered.length / prEng.pageSize));
    if (prEng.page > pages) prEng.page = 1;
    const start = (prEng.page - 1) * prEng.pageSize;
    const end   = Math.min(prEng.page * prEng.pageSize, filtered.length);

    allRows.forEach(r => r.style.display = 'none');

    if (filtered.length === 0) {
        if (emptyRow) emptyRow.style.display = '';
    } else {
        if (emptyRow) emptyRow.style.display = 'none';
        filtered.forEach(r => tbody.appendChild(r));
        filtered.slice(start, end).forEach(r => r.style.display = '');
    }

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
    document.querySelectorAll('[id^="prs"]').forEach(el => el.textContent = '↕');
    const el = document.getElementById('prs' + col);
    if (el) el.textContent = prEng.sortDir === 'asc' ? '↑' : '↓';
    applyFilters();
}

function prGoto(p)   { prEng.page = p;               applyFilters(); }
function prPageSz(s) { prEng.pageSize = parseInt(s); prEng.page = 1; applyFilters(); }

function openPRDetail(id, category) {
    // FIX: key = "category_id" so goods_1 and service_1 never collide
    const pr = allPRs[category + '_' + id];
    if (!pr) return;

    const isService = (category === 'service' || pr.type === 'service');
    const rfq = (pr.rfqs || [])[0];
    const rfqId = rfq ? rfq.id : null;
    const vendorSelections = rfq ? (rfq.vendor_selections || []) : [];
    const hasVS = vendorSelections.length > 0;

    // === Vendor maps ===
    const itemVS = {};
    vendorSelections.forEach(vs => {
        const vName = (vs.vendor && (vs.vendor.vendor_name || vs.vendor.name)) || '—';
        (vs.selection_items || []).forEach(si => {
            const key = si.purchase_request_item_id || si.service_request_item_id;
            if (key) itemVS[key] = {
                vendor: vName,
                unit_price: parseFloat(si.final_price_per_item) || 0,
                qty: parseInt(si.final_quantity) || 0,
                total: (parseFloat(si.final_price_per_item)||0) * (parseInt(si.final_quantity)||0),
            };
        });
    });

    const vendorTotals = {};
    vendorSelections.forEach(vs => {
        const vName = (vs.vendor && (vs.vendor.vendor_name || vs.vendor.name)) || '—';
        const vid = vs.vendor_id;
        if (!vendorTotals[vid]) vendorTotals[vid] = { name: vName, items: [], total: 0 };
        (vs.selection_items || []).forEach(si => {
            const sub = (parseFloat(si.final_price_per_item)||0) * (parseInt(si.final_quantity)||0);
            vendorTotals[vid].total += sub;
            const key = si.purchase_request_item_id || si.service_request_item_id;
            const all = isService ? (pr.jobs||[]).flatMap(j=>j.items||[]) : (pr.items||[]);
            const found = all.find(it => it.id == key);
            vendorTotals[vid].items.push({ item_name: found?.item_name||found?.name||'—', qty: si.final_quantity, unit_price: si.final_price_per_item, subtotal: sub });
        });
    });

    // === Header ===
    document.getElementById('detail-title').textContent = pr.display_title || pr.title || pr.service_name || 'Request Detail';
    document.getElementById('detail-sub').textContent =
        (pr.display_doc || pr.document_number || (isService?'SR-':'PR-')+String(pr.id).padStart(4,'0'))
        + ' | ' + (pr.department||'') + (pr.plant?' | '+pr.plant:'');

    document.getElementById('detail-meta').innerHTML =
        `<div><span style="font-size:10px;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.06em">Priority</span><div style="font-weight:600;font-size:12.5px;margin-top:2px">${pr.priority||'Normal'}</div></div>
         <div><span style="font-size:10px;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.06em">Plant</span><div style="font-weight:600;font-size:12.5px;margin-top:2px">${pr.plant||'—'}</div></div>
         <div><span style="font-size:10px;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.06em">Status</span><div style="font-weight:600;font-size:12.5px;margin-top:2px">${pr.status?pr.status.replace(/_/g,' ').replace(/\b\w/g,c=>c.toUpperCase()):'—'}</div></div>`;

    document.getElementById('detail-select-vendor-btn').href = `/vendor-selection?key=${category}_${id}`;

    // === Progress bar ===
    const _steps = [{label:'PR\nSubmitted'},{label:'Vendor\nSearch'},{label:'Vendor\nSelection'},{label:'Completed'}];
    function _step(s){ return s==='completed'?4:s==='approved'?3:s==='in_process'?2:1; }
    function buildProgressBar(status){
        const cur=_step(status), isFail=(status==='rejected'||status==='cancelled');
        return `<div style="display:flex;align-items:flex-start;margin-bottom:20px">`+_steps.map((s,i)=>{
            const n=i+1; let done=n<cur, active=n===cur;
            if(status==='completed'&&n===4){done=true;active=false;}
            let cb=done?'#22c55e':active?'#3b5bdb':'#e5e7eb', cc=done||active?'#fff':'#9ca3af';
            let lc=active?'#3b5bdb':done?'#22c55e':'#9ca3af', ct=done?'✓':n;
            if(isFail&&active){cb=status==='rejected'?'#ef4444':'#9ca3af';lc=cb;ct='✕';}
            const line=n<=cur&&!isFail?'#22c55e':'#e5e7eb';
            return `<div style="display:flex;flex-direction:column;align-items:center;flex:1;position:relative">
                ${i>0?`<div style="position:absolute;top:14px;right:50%;width:100%;height:2px;background:${line};z-index:0"></div>`:''}
                <div style="width:28px;height:28px;border-radius:50%;background:${cb};color:${cc};font-size:11px;font-weight:700;display:flex;align-items:center;justify-content:center;position:relative;z-index:1">${ct}</div>
                <div style="font-size:10.5px;font-weight:600;color:${lc};text-align:center;margin-top:5px;white-space:pre-line">${s.label}</div>
            </div>`;
        }).join('')+'</div>';
    }

    // === Request info grid ===
    const subDate = new Date(pr.submission_date||pr.created_at).toLocaleDateString('id-ID',{day:'2-digit',month:'short',year:'numeric'});
    const reqDate = new Date(pr.requested_date||pr.need_date||pr.created_at).toLocaleDateString('id-ID',{day:'2-digit',month:'short',year:'numeric'});

    // === Item table ===
    const thS = 'padding:8px 10px;text-align:left;font-size:10px;font-weight:700;color:#9ca3af;white-space:nowrap;background:#f9fafb;';
    const tdS = 'padding:8px 10px;border-bottom:1px solid #f3f4f6;font-size:12px;vertical-align:middle;';
    let rows = '', grand = 0, tableHtml = '';

    if (isService) {
        const cols = hasVS ? 8 : 5;
        (pr.jobs||[]).forEach(job => {
            rows += `<tr><td colspan="${cols}" style="background:#f0f4f8;padding:7px 12px;font-weight:700;font-size:11.5px;color:#374151;border-bottom:1px solid #e5e7eb;">🛠️ ${job.job_description}</td></tr>`;
            (job.items||[]).forEach((it,i) => {
                const vs = itemVS[it.id];
                if(vs) grand += vs.total;
                rows += `<tr>
                    <td style="${tdS}color:#9ca3af">${i+1}</td>
                    <td style="${tdS}font-weight:600">${it.item_name||it.name||'—'}</td>
                    <td style="${tdS}color:#6b7280;font-size:11.5px">${it.specification||'—'}</td>
                    <td style="${tdS}text-align:right;font-weight:600">${it.quantity}</td>
                    <td style="${tdS}color:#6b7280">${it.unit}</td>
                    ${hasVS?`<td style="${tdS}font-family:monospace;font-weight:600">${vs?fmtRp(vs.unit_price):'—'}</td>
                    <td style="${tdS}font-family:monospace;font-weight:700">${vs?fmtRp(vs.total):'—'}</td>
                    <td style="${tdS}">${vs?`<span style="padding:2px 8px;background:#e0f2fe;border-radius:4px;font-size:11px;font-weight:600;color:#0369a1">${vs.vendor}</span>`:'—'}</td>`:''}
                </tr>`;
            });
        });
        const svcTotal = hasVS&&grand>0?`<tr style="background:#f9fafb"><td colspan="5" style="padding:9px 10px;text-align:right;font-size:12px;font-weight:700;color:#374151">Total Request Value</td><td colspan="3" style="padding:9px 10px;text-align:right;font-family:monospace;font-size:13px;font-weight:800;color:#111827">${fmtRp(grand)}</td></tr>`:'';
        const svcTh = hasVS?`<th style="${thS}text-align:right">UNIT PRICE (RP)</th><th style="${thS}text-align:right">TOTAL (RP)</th><th style="${thS}">VENDOR</th>`:'';
        tableHtml = `<div style="border:1px solid #e5e7eb;border-radius:8px;overflow:hidden;margin-bottom:4px"><div style="overflow-x:auto">
            <table style="width:100%;border-collapse:collapse;font-size:12px;min-width:${hasVS?'640px':'320px'}">
                <thead><tr><th style="${thS}width:36px">NO</th><th style="${thS}">ITEM NAME</th><th style="${thS}">SPEC</th><th style="${thS}text-align:right;width:60px">QTY</th><th style="${thS}width:55px">UNIT</th>${svcTh}</tr></thead>
                <tbody>${rows||'<tr><td colspan="5" style="text-align:center;padding:16px;color:#9ca3af">No items</td></tr>'}</tbody>
                ${svcTotal?`<tfoot>${svcTotal}</tfoot>`:''}
            </table></div></div>`;
    } else {
        (pr.items||[]).forEach((it,i) => {
            const vs = itemVS[it.id]; if(vs) grand += vs.total;
            rows += `<tr>
                <td style="${tdS}">${i+1}</td>
                <td style="${tdS}font-family:monospace;color:#3b5bdb;font-weight:600">${it.item_id||'—'}</td>
                <td style="${tdS}font-weight:500">${it.item_name||it.name||'—'}</td>
                <td style="${tdS}color:#6b7280;font-size:11.5px">${it.item_notes||'—'}</td>
                <td style="${tdS}color:#6b7280;font-size:11.5px">${it.specification||'—'}</td>
                <td style="${tdS}text-align:right;font-weight:600">${it.quantity}</td>
                <td style="${tdS}color:#6b7280">${it.unit}</td>
                ${hasVS?`<td style="${tdS}font-family:monospace;font-weight:600">${vs?fmtRp(vs.unit_price):'—'}</td>
                <td style="${tdS}font-family:monospace;font-weight:700">${vs?fmtRp(vs.total):'—'}</td>
                <td style="${tdS}">${vs?`<span style="padding:2px 8px;background:#e0f2fe;border-radius:4px;font-size:11px;font-weight:600;color:#0369a1;white-space:nowrap">${vs.vendor}</span>`:'—'}</td>`:''}
            </tr>`;
        });
        const gTh = hasVS?`<th style="${thS}text-align:right">UNIT PRICE (RP)</th><th style="${thS}text-align:right">TOTAL (RP)</th><th style="${thS}">VENDOR</th>`:'';
        const gTotal = hasVS&&grand>0?`<tr style="background:#f9fafb"><td colspan="7" style="padding:9px 10px;text-align:right;font-size:12px;font-weight:700;color:#374151">Total Request Value</td><td colspan="3" style="padding:9px 10px;text-align:right;font-family:monospace;font-size:13px;font-weight:800;color:#111827">${fmtRp(grand)}</td></tr>`:'';
        tableHtml = `<div style="border:1px solid #e5e7eb;border-radius:8px;overflow:hidden;margin-bottom:4px"><div style="overflow-x:auto">
            <table style="width:100%;border-collapse:collapse;font-size:12px;min-width:${hasVS?'780px':'460px'}">
                <thead><tr><th style="${thS}">NO</th><th style="${thS}">ITEM ID</th><th style="${thS}">ITEM NAME</th><th style="${thS}">DESCRIPTION</th><th style="${thS}">SPEC</th><th style="${thS}text-align:right">QTY</th><th style="${thS}">UNIT</th>${gTh}</tr></thead>
                <tbody>${rows||'<tr><td colspan="7" style="text-align:center;padding:16px;color:#9ca3af">No items</td></tr>'}</tbody>
                ${gTotal?`<tfoot>${gTotal}</tfoot>`:''}
            </table></div></div>`;
    }

    // === Vendor Purchase Summary ===
    let vSumHtml = '';
    if (hasVS && Object.keys(vendorTotals).length > 0) {
        vSumHtml = `<div style="font-size:10px;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.06em;margin-top:18px;margin-bottom:8px;padding-bottom:5px;border-bottom:2px solid #e5e7eb">Vendor Purchase Summary</div>
        <div style="display:flex;gap:10px;flex-wrap:wrap">`+Object.values(vendorTotals).map(v=>`
            <div style="flex:1;min-width:180px;border:1px solid #e5e7eb;border-radius:8px;overflow:hidden">
                <div style="padding:10px 12px;background:#f8fafc;border-bottom:1px solid #e5e7eb">
                    <div style="font-size:13px;font-weight:700;color:#1e3a5f">${v.name}</div>
                    <div style="font-size:13px;font-weight:800;color:#111827;margin-top:3px;font-family:monospace">${fmtRp(v.total)}</div>
                </div>
                <div style="padding:8px 12px;display:flex;flex-direction:column;gap:5px">
                    ${v.items.map(si=>`<div style="display:flex;justify-content:space-between;font-size:11.5px">
                        <span style="color:#6b7280">${si.item_name} — ${si.qty} × ${fmtRp(si.unit_price)}</span>
                        <span style="font-family:monospace;font-weight:600">${fmtRp(si.subtotal)}</span>
                    </div>`).join('')}
                </div>
            </div>`).join('')+'</div>';
    }

    // === Activity Log — show real rfq history only (no synthetic events) ===
    const histories = rfq ? (rfq.histories||[]) : [];
    let activityHtml = '';
    if (histories.length > 0) {
        const logItems = histories.slice().reverse().map(h => {
            const actor = h.user?.name || 'System';
            const time  = h.action_date ? new Date(h.action_date).toLocaleDateString('id-ID',{day:'2-digit',month:'short',year:'numeric',hour:'2-digit',minute:'2-digit'}) : '';
            return `<div style="display:flex;gap:8px;font-size:12px;margin-bottom:8px">
                <span style="width:6px;height:6px;border-radius:50%;background:#3b5bdb;margin-top:5px;flex-shrink:0"></span>
                <div>
                    <span style="font-weight:600;color:#111827">${h.action||'Action'}</span>
                    ${h.notes?`<span style="color:#6b7280"> — ${h.notes}</span>`:''}
                    <div style="font-size:11px;color:#9ca3af;margin-top:1px">${time} — ${actor}</div>
                </div>
            </div>`;
        }).join('');
        activityHtml = logItems;
    } else {
        const subDate2 = new Date(pr.submission_date||pr.created_at).toLocaleDateString('id-ID',{day:'2-digit',month:'short',year:'numeric'});
        activityHtml = `<div style="display:flex;gap:8px;font-size:12px">
            <span style="width:6px;height:6px;border-radius:50%;background:#22c55e;margin-top:5px;flex-shrink:0"></span>
            <div><span style="font-weight:600;color:#111827">${isService?'SR':'PR'} created and submitted</span>
            <div style="font-size:11px;color:#9ca3af;margin-top:1px">${subDate2} — ${pr.user?.name||'User'}</div></div>
        </div>`;
    }

    // === Assemble ===
    document.getElementById('detail-body').innerHTML = `
        <div style="font-size:10px;font-weight:700;color:#9ca3af;text-transform:uppercase;margin-bottom:10px">Progress Status</div>
        ${buildProgressBar(pr.status)}
        <div style="font-size:10px;font-weight:700;color:#9ca3af;text-transform:uppercase;margin-bottom:10px">Request Information</div>
        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin-bottom:20px;background:#f9fafb;border-radius:8px;padding:12px 14px">
            <div><div style="font-size:10px;color:#9ca3af;text-transform:uppercase;font-weight:600;margin-bottom:3px">Submission Date</div><div style="font-weight:500;font-size:12.5px">${subDate}</div></div>
            <div><div style="font-size:10px;color:#9ca3af;text-transform:uppercase;font-weight:600;margin-bottom:3px">Department</div><div style="font-weight:500;font-size:12.5px">${pr.department||'—'}</div></div>
            <div><div style="font-size:10px;color:#9ca3af;text-transform:uppercase;font-weight:600;margin-bottom:3px">Requested Date</div><div style="font-weight:500;font-size:12.5px">${reqDate}</div></div>
            <div><div style="font-size:10px;color:#9ca3af;text-transform:uppercase;font-weight:600;margin-bottom:3px">Plant</div><div style="font-weight:500;font-size:12.5px">${pr.plant||'—'}</div></div>
        </div>
        <div style="font-size:10px;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.06em;margin-bottom:8px;padding-bottom:5px;border-bottom:2px solid #e5e7eb">Item List</div>
        ${tableHtml}
        ${vSumHtml}
        <div style="font-size:10px;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.06em;margin-top:18px;margin-bottom:8px;padding-bottom:5px;border-bottom:2px solid #e5e7eb">Activity Log</div>
        ${activityHtml}`;

    document.getElementById('pr-detail-modal').style.display = 'flex';
}

function closePRDetail() { document.getElementById('pr-detail-modal').style.display = 'none'; }

document.addEventListener('DOMContentLoaded', applyFilters);
</script>
@endsection