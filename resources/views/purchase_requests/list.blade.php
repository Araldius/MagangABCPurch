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
    <div style="display:flex;align-items:center;justify-content:space-between;padding:16px 20px;border-bottom:1px solid #f3f4f6;gap:10px;flex-wrap:wrap">
        <span style="font-size:14px;font-weight:700;color:#111827">All Requests</span>
        <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap">
            @if($isPurchasing)
            <div style="position:relative">
                <select id="dept-filter" onchange="applyFilters()" style="padding:6px 28px 6px 10px;border:1px solid #d1d5db;border-radius:7px;font-size:12px;color:#374151;background:#fff;appearance:none;cursor:pointer;min-width:120px;font-family:inherit">
                    <option value="">All Dept.</option>
                    @foreach($allRequests->pluck('department')->unique()->filter()->sort()->values() as $dept)
                    <option value="{{ $dept }}">{{ $dept }}</option>
                    @endforeach
                </select>
                <svg style="position:absolute;right:8px;top:50%;transform:translateY(-50%);pointer-events:none;color:#6b7280" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 9l6 6 6-6" stroke-linecap="round"/></svg>
            </div>
            @endif
            <div style="position:relative">
                <select id="status-filter" onchange="applyFilters()" style="padding:6px 28px 6px 10px;border:1px solid #d1d5db;border-radius:7px;font-size:12px;color:#374151;background:#fff;appearance:none;cursor:pointer;min-width:140px;font-family:inherit">
                    <option value="">All Status</option>
                    <option value="awaiting_approval">Awaiting Approval</option>
                    <option value="in_process">In Process</option>
                    <option value="approved">Approved</option>
                    <option value="completed">Completed</option>
                    <option value="rejected">Rejected</option>
                    <option value="cancelled">Cancelled</option>
                </select>
                <svg style="position:absolute;right:8px;top:50%;transform:translateY(-50%);pointer-events:none;color:#6b7280" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 9l6 6 6-6" stroke-linecap="round"/></svg>
            </div>
            <a href="{{ route('purchase_requests.create') }}" style="display:inline-flex;align-items:center;gap:5px;padding:6px 14px;background:#111827;color:#fff;border-radius:7px;font-size:12.5px;font-weight:600;text-decoration:none" onmouseover="this.style.background='#1f2937'" onmouseout="this.style.background='#111827'">+ New Request</a>
        </div>
    </div>
    <div style="overflow-x:auto">
        <table id="pr-table" style="width:100%;border-collapse:collapse;font-size:12.5px">
            <thead>
                <tr style="background:#f9fafb">
                    <th style="padding:9px 20px;text-align:left;font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.06em;white-space:nowrap">DOC NO.</th>
                    <th style="padding:9px 14px;text-align:left;font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.06em">DESCRIPTION</th>
                    @if($isPurchasing)<th style="padding:9px 14px;text-align:left;font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.06em">REQUESTER</th>@endif
                    <th style="padding:9px 14px;text-align:left;font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.06em">TYPE/ITEMS</th>
                    <th style="padding:9px 14px;text-align:left;font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.06em">STATUS</th>
                    <th style="padding:9px 14px;text-align:left;font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.06em;white-space:nowrap">SUBMITTED</th>
                    <th style="padding:9px 14px;text-align:left;font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.06em;white-space:nowrap">LAST UPDATE</th>
                    <th style="padding:9px 14px;text-align:left;font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.06em">ACTION</th>
                </tr>
            </thead>
            <tbody>
                @forelse($allRequests as $pr)
                @php
                    [$sLabel,$sBg,$sText,$sDot]=$statusCfg[$pr->status]??[ucfirst(str_replace('_',' ',$pr->status)),'#f3f4f6','#374151','#9ca3af'];
                    $upd=$pr->updated_at;
                    $lu=$upd->isToday()?'Today, '.$upd->format('H:i'):($upd->isYesterday()?'Yesterday, '.$upd->format('H:i'):$upd->format('d M').', '.$upd->format('H:i'));
                @endphp
                <tr data-status="{{ $pr->status }}" data-dept="{{ $pr->department ?? 'General' }}" style="border-bottom:1px solid #f3f4f6" onmouseover="this.style.background='#fafafa'" onmouseout="this.style.background='transparent'">
                    <td style="padding:13px 20px">
                        <span style="font-family:'Courier New',monospace;font-size:12px;font-weight:600;color:#111827">{{ $pr->display_doc }}</span>
                        @if($pr->type === 'service')
                            <span style="display:inline-block;margin-left:6px;padding:2px 6px;border-radius:4px;background:#e0e7ff;color:#3730a3;font-size:10px;font-weight:700;">SERVICE</span>
                        @endif
                    </td>
                    <td style="padding:13px 14px;max-width:200px">
                        <div style="font-size:12.5px;font-weight:500;color:#111827">{{ $pr->display_title }}</div>
                        <div style="font-size:11px;color:#9ca3af;margin-top:1px">{{ $pr->plant }}</div>
                    </td>
                    @if($isPurchasing)
                    <td style="padding:13px 14px">
                        <div style="font-size:12.5px;font-weight:500;color:#111827">{{ optional($pr->user)->name??'—' }}</div>
                        <div style="font-size:11px;color:#9ca3af;margin-top:1px">{{ $pr->department ?? '—' }}</div>
                    </td>
                    @endif
                    <td style="padding:13px 14px;font-size:12px;color:#374151">{{ $pr->item_count }} item(s)</td>
                    <td style="padding:13px 14px">
                        <span style="display:inline-flex;align-items:center;gap:4px;padding:3px 9px;border-radius:999px;background:{{ $sBg }};font-size:11.5px;font-weight:600;color:{{ $sText }};white-space:nowrap">
                            <span style="width:5px;height:5px;border-radius:50%;background:{{ $sDot }};flex-shrink:0"></span>{{ $sLabel }}
                        </span>
                    </td>
                    <td style="padding:13px 14px;font-size:12px;color:#6b7280;white-space:nowrap">{{ \Carbon\Carbon::parse($pr->submission_date ?? $pr->created_at)->format('d M Y') }}</td>
                    <td style="padding:13px 14px;font-size:12px;color:#6b7280;white-space:nowrap">{{ $lu }}</td>
                    <td style="padding:13px 14px">
                        <button onclick="openPRDetail('{{ $pr->display_doc }}')" style="padding:4px 12px;border:1px solid #d1d5db;border-radius:6px;background:#fff;font-size:12px;font-weight:500;color:#374151;cursor:pointer" onmouseover="this.style.background='#f9fafb'" onmouseout="this.style.background='#fff'">Detail</button>
                    </td>
                </tr>
                @empty
                <tr><td colspan="{{ $isPurchasing?8:7 }}" style="text-align:center;padding:36px 20px;color:#9ca3af;font-size:12.5px">No requests found. <a href="{{ route('purchase_requests.create') }}" style="color:#3b5bdb;font-weight:600;text-decoration:none">Create one →</a></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
 
{{-- DETAIL MODAL --}}
<div id="pr-detail-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:200;align-items:center;justify-content:center;padding:20px">
    <div style="background:#fff;border-radius:14px;width:100%;max-width:700px;max-height:88vh;display:flex;flex-direction:column;box-shadow:0 8px 40px rgba(0,0,0,.12)">
        <div style="padding:18px 22px;border-bottom:1px solid #f3f4f6;display:flex;align-items:flex-start;justify-content:space-between;gap:12px">
            <div>
                <div id="detail-title" style="font-size:15px;font-weight:700;color:#111827"></div>
                <div id="detail-sub" style="font-size:12px;color:#3b5bdb;font-weight:500;margin-top:2px"></div>
            </div>
            <button onclick="closePRDetail()" style="background:none;border:none;cursor:pointer;color:#9ca3af;padding:4px;border-radius:6px" onmouseover="this.style.background='#f3f4f6'" onmouseout="this.style.background='none'">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M18 6L6 18M6 6l12 12" stroke-linecap="round"/></svg>
            </button>
        </div>
        <div id="detail-body" style="padding:18px 22px;overflow-y:auto;flex:1;font-size:12.5px"></div>
        <div style="padding:14px 22px;border-top:1px solid #f3f4f6;display:flex;justify-content:flex-end;gap:10px">
            <button onclick="closePRDetail()" style="padding:7px 16px;border:1px solid #d1d5db;border-radius:7px;background:#fff;font-size:12.5px;font-weight:600;color:#374151;cursor:pointer">Close</button>
            @if($isPurchasing)
            <a id="detail-rfq-btn" href="#" style="display:inline-flex;align-items:center;gap:5px;padding:7px 16px;background:#111827;color:#fff;border-radius:7px;font-size:12.5px;font-weight:600;text-decoration:none">Select Vendor</a>
            @endif
        </div>
    </div>
</div>
 
<script>
const allPRs=@json($allRequests->keyBy('display_doc')->toArray());
const isPurchasing={{ $isPurchasing?'true':'false' }};
const statusCfg={
    awaiting_approval:['Awaiting Approval','#fff7ed','#c2410c','#f97316'],
    in_process:['In Process','#f0f9ff','#0369a1','#0ea5e9'],
    approved:['Approved','#f0fdf4','#15803d','#22c55e'],
    completed:['Completed','#eff6ff','#1d4ed8','#3b82f6'],
    rejected:['Rejected','#fef2f2','#b91c1c','#ef4444'],
    cancelled:['Cancelled','#f3f4f6','#374151','#9ca3af']
};
const steps=[{label:'Submitted'},{label:'Vendor\nSearch\n(Purchasing)'},{label:'Vendor\nSelection'},{label:'Completed'}];
 
function getStep(s){
    if(s==='completed') return 4;
    if(s==='approved') return 3;
    if(s==='in_process') return 2;
    return 1;
}
 
function buildProgressBar(status){
    const cur = getStep(status);
    const isFail = (status === 'rejected' || status === 'cancelled');
    
    return `<div style="display:flex;align-items:flex-start">${steps.map((s,i)=>{
        const n = i+1;
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

        return `<div style="display:flex;flex-direction:column;align-items:center;flex:1;position:relative">${i<3?`<div style="position:absolute;top:13px;left:50%;width:100%;height:2px;background:${done?'#22c55e':'#e5e7eb'};z-index:0"></div>`:''}
            <div style="width:26px;height:26px;border-radius:50%;background:${cb};color:${cc};font-size:10.5px;font-weight:700;display:flex;align-items:center;justify-content:center;position:relative;z-index:1;flex-shrink:0">${circleText}</div>
            <div style="font-size:10px;font-weight:600;color:${lc};text-align:center;margin-top:4px;line-height:1.3;white-space:pre-line">${s.label}</div>
        </div>`;
    }).join('')}</div>`;
}
 
function applyFilters(){
    const s=document.getElementById('status-filter').value;
    const d=isPurchasing?document.getElementById('dept-filter').value:'';
    document.querySelectorAll('#pr-table tbody tr[data-status]').forEach(r=>{
        r.style.display=(!s||r.dataset.status===s)&&(!d||r.dataset.dept===d)?'':'none';
    });
}
 
function openPRDetail(docId){
    const pr=allPRs[docId]; if(!pr)return;
    document.getElementById('detail-title').textContent = pr.display_title || 'Request Detail';
    document.getElementById('detail-sub').textContent = (pr.display_doc||'') + ' | ' + (pr.plant||'');
    
    let itemRows = '';
    if (pr.type === 'goods') {
        const items = pr.items || [];
        itemRows = items.map((item,i)=>`<tr>
            <td style="padding:9px 12px;border-bottom:1px solid #f9fafb;color:#6b7280">${i+1}</td>
            <td style="padding:9px 12px;border-bottom:1px solid #f9fafb;font-family:'Courier New',monospace;font-size:11.5px;color:#3b5bdb;font-weight:600">${item.item_id||item.item_id||'—'}</td>
            <td style="padding:9px 12px;border-bottom:1px solid #f9fafb;font-weight:500;color:#111827">${item.item_name || item.name || '—'}</td>
            <td style="padding:9px 12px;border-bottom:1px solid #f9fafb;font-size:11.5px;color:#6b7280">${item.specification||'—'}</td>
            <td style="padding:9px 12px;border-bottom:1px solid #f9fafb;color:#374151">${item.quantity}</td>
            <td style="padding:9px 12px;border-bottom:1px solid #f9fafb;color:#374151">${item.unit}</td>
            <td style="padding:9px 12px;border-bottom:1px solid #f9fafb;color:#9ca3af">—</td>
            <td style="padding:9px 12px;border-bottom:1px solid #f9fafb;color:#9ca3af">—</td>
        </tr>`).join('')||'<tr><td colspan="8" style="padding:16px;text-align:center;color:#9ca3af">No items</td></tr>';
    } else if (pr.type === 'service') {
        const jobs = pr.jobs || [];
        let counter = 1;
        jobs.forEach(job => {
            itemRows += `<tr><td colspan="8" style="background:#f3f4f6;padding:6px 12px;font-weight:700;font-size:11.5px;color:#111827;">💼 JOB: ${job.job_description}</td></tr>`;
            const items = job.items || [];
            items.forEach(item => {
                itemRows += `<tr>
                    <td style="padding:9px 12px;border-bottom:1px solid #f9fafb;color:#6b7280">${counter++}</td>
                    <td style="padding:9px 12px;border-bottom:1px solid #f9fafb;font-family:'Courier New',monospace;font-size:11.5px;color:#9ca3af;font-weight:600">—</td>
                    <td style="padding:9px 12px;border-bottom:1px solid #f9fafb;font-weight:500;color:#111827">${item.item_name || item.name || '—'}</td>
                    <td style="padding:9px 12px;border-bottom:1px solid #f9fafb;font-size:11.5px;color:#6b7280">${item.specification||'—'}</td>
                    <td style="padding:9px 12px;border-bottom:1px solid #f9fafb;color:#374151">${item.quantity}</td>
                    <td style="padding:9px 12px;border-bottom:1px solid #f9fafb;color:#374151">${item.unit}</td>
                    <td style="padding:9px 12px;border-bottom:1px solid #f9fafb;color:#9ca3af">—</td>
                    <td style="padding:9px 12px;border-bottom:1px solid #f9fafb;color:#9ca3af">—</td>
                </tr>`;
            });
        });
        if(!itemRows) itemRows = '<tr><td colspan="8" style="padding:16px;text-align:center;color:#9ca3af">No job scopes added</td></tr>';
    }

    const subDate = (pr.submission_date || pr.created_at) ? new Date((pr.submission_date || pr.created_at)).toLocaleDateString('id-ID',{day:'2-digit',month:'short',year:'numeric'}) : '—';
    const reqDate = pr.requested_date ? new Date(pr.requested_date).toLocaleDateString('id-ID',{day:'2-digit',month:'short',year:'numeric'}) : '—';
    const [sLabel,sBg,sText,sDot]=statusCfg[pr.status]||[pr.status,'#f3f4f6','#374151','#9ca3af'];
    
    document.getElementById('detail-body').innerHTML=`
        <div style="background:#f9fafb;border-radius:8px;padding:12px 14px;margin-bottom:14px">
            <div style="font-size:10px;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.08em;margin-bottom:10px">Progress Status</div>
            ${buildProgressBar(pr.status)}
        </div>
        <div style="background:#f9fafb;border-radius:8px;padding:12px 14px;margin-bottom:14px">
            <div style="font-size:10px;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.08em;margin-bottom:10px">Request Information</div>
            <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:10px">
                <div><div style="font-size:10px;color:#9ca3af;text-transform:uppercase;font-weight:600;margin-bottom:3px">Submission Date</div><div style="font-weight:500;font-size:12.5px">${subDate}</div></div>
                <div><div style="font-size:10px;color:#9ca3af;text-transform:uppercase;font-weight:600;margin-bottom:3px">Department</div><div style="font-weight:500;font-size:12.5px">${pr.department||'—'}</div></div>
                <div><div style="font-size:10px;color:#9ca3af;text-transform:uppercase;font-weight:600;margin-bottom:3px">Requested By</div><div style="font-weight:500;font-size:12.5px">${pr.user?.name||'You'}</div></div>
                <div><div style="font-size:10px;color:#9ca3af;text-transform:uppercase;font-weight:600;margin-bottom:3px">Requested Date</div><div style="font-weight:500;font-size:12.5px">${reqDate}</div></div>
                <div><div style="font-size:10px;color:#9ca3af;text-transform:uppercase;font-weight:600;margin-bottom:3px">Plant</div><div style="font-weight:500;font-size:12.5px">${pr.plant||'—'}</div></div>
                <div><div style="font-size:10px;color:#9ca3af;text-transform:uppercase;font-weight:600;margin-bottom:3px">Type</div><div style="font-weight:500;font-size:12.5px;text-transform:capitalize;">${pr.type} Request</div></div>
            </div>
        </div>
        <div style="font-size:10px;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.08em;margin-bottom:8px">Scope Details</div>
        <div style="border:1px solid #e5e7eb;border-radius:8px;overflow:hidden;margin-bottom:14px">
            <table style="width:100%;border-collapse:collapse;font-size:12px">
                <thead><tr style="background:#f9fafb">
                    <th style="padding:8px 12px;text-align:left;font-size:10px;color:#9ca3af;font-weight:700;text-transform:uppercase">NO</th>
                    <th style="padding:8px 12px;text-align:left;font-size:10px;color:#9ca3af;font-weight:700;text-transform:uppercase">ITEM ID</th>
                    <th style="padding:8px 12px;text-align:left;font-size:10px;color:#9ca3af;font-weight:700;text-transform:uppercase">ITEM NAME</th>
                    <th style="padding:8px 12px;text-align:left;font-size:10px;color:#9ca3af;font-weight:700;text-transform:uppercase">SPEC</th>
                    <th style="padding:8px 12px;text-align:left;font-size:10px;color:#9ca3af;font-weight:700;text-transform:uppercase">QTY</th>
                    <th style="padding:8px 12px;text-align:left;font-size:10px;color:#9ca3af;font-weight:700;text-transform:uppercase">UNIT</th>
                    <th style="padding:8px 12px;text-align:left;font-size:10px;color:#9ca3af;font-weight:700;text-transform:uppercase">UNIT PRICE (RP)</th>
                    <th style="padding:8px 12px;text-align:left;font-size:10px;color:#9ca3af;font-weight:700;text-transform:uppercase">TOTAL (RP)</th>
                </tr></thead>
                <tbody>${itemRows}</tbody>
                <tfoot><tr style="background:#f9fafb"><td colspan="7" style="padding:8px 12px;text-align:right;font-size:11.5px;font-weight:700;color:#374151;border-top:1px solid #e5e7eb">Total Value Estimation</td><td style="padding:8px 12px;font-weight:700;color:#374151;border-top:1px solid #e5e7eb">Rp —</td></tr></tfoot>
            </table>
        </div>
        <div style="font-size:10px;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.08em;margin-bottom:8px">Activity Log</div>
        <div style="display:flex;gap:9px;align-items:flex-start">
            <div style="width:6px;height:6px;background:#6b7280;border-radius:50%;margin-top:4px;flex-shrink:0"></div>
            <div><div style="font-size:12.5px;font-weight:500;color:#111827">Request created and submitted</div><div style="font-size:11.5px;color:#9ca3af;margin-top:1px">${subDate} — ${pr.user?.name||'You'}</div></div>
        </div>`;
    document.getElementById('pr-detail-modal').style.display='flex';
}
function closePRDetail(){document.getElementById('pr-detail-modal').style.display='none';}
document.getElementById('pr-detail-modal').addEventListener('click',function(e){if(e.target===this)closePRDetail();});
</script>
@endsection