@extends('layouts.app')
@php $pageTitle = 'Procurement History'; @endphp
@section('content')
 
<div style="margin-bottom:20px">
    <h1 style="font-size:20px;font-weight:700;color:#111827;margin:0 0 3px">Procurement History</h1>
    <p style="font-size:12.5px;color:#6b7280;margin:0">All selected vendors and completed procurement records.</p>
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
        <div style="font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.07em">PRs Completed</div>
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
        <div style="display:flex;gap:8px">
            <div style="position:relative">
                <select id="unit-filter" onchange="applyHFilters()" style="padding:6px 28px 6px 10px;border:1px solid #d1d5db;border-radius:7px;font-size:12px;color:#374151;background:#fff;appearance:none;cursor:pointer;min-width:110px;font-family:inherit">
                    <option value="">All Units</option>
                    @foreach($departments as $dept)<option value="{{ $dept }}">{{ $dept }}</option>@endforeach
                </select>
                <svg style="position:absolute;right:8px;top:50%;transform:translateY(-50%);pointer-events:none;color:#6b7280" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 9l6 6 6-6" stroke-linecap="round"/></svg>
            </div>
            <div style="position:relative">
                <select id="hist-status-filter" onchange="applyHFilters()" style="padding:6px 28px 6px 10px;border:1px solid #d1d5db;border-radius:7px;font-size:12px;color:#374151;background:#fff;appearance:none;cursor:pointer;min-width:110px;font-family:inherit">
                    <option value="">All Status</option>
                    <option value="completed">Completed</option>
                    <option value="in_process">In Process</option>
                    <option value="approved">Approved</option>
                </select>
                <svg style="position:absolute;right:8px;top:50%;transform:translateY(-50%);pointer-events:none;color:#6b7280" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 9l6 6 6-6" stroke-linecap="round"/></svg>
            </div>
        </div>
    </div>
    <div style="overflow-x:auto">
        <table id="history-table" style="width:100%;border-collapse:collapse;font-size:12.5px">
            <thead>
                <tr style="background:#f9fafb">
                    <th style="padding:9px 20px;text-align:left;font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.06em">DOC NO.</th>
                    <th style="padding:9px 14px;text-align:left;font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.06em">VENDOR NAME</th>
                    <th style="padding:9px 14px;text-align:left;font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.06em">UNIT / DEPT.</th>
                    <th style="padding:9px 14px;text-align:left;font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.06em">ITEM</th>
                    <th style="padding:9px 14px;text-align:left;font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.06em">QTY</th>
                    <th style="padding:9px 14px;text-align:left;font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.06em">VALUE (RP)</th>
                    <th style="padding:9px 14px;text-align:left;font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.06em">LEAD TIME</th>
                    <th style="padding:9px 14px;text-align:left;font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.06em">STATUS</th>
                </tr>
            </thead>
            <tbody>
                @forelse($records as $rec)
                @php
                    $sCfg=['completed'=>['Completed','#eff6ff','#1d4ed8','#3b82f6'],'in_process'=>['In Process','#f0f9ff','#0369a1','#0ea5e9'],'approved'=>['Approved','#f0fdf4','#15803d','#22c55e']];
                    [$sLabel,$sBg,$sText,$sDot]=$sCfg[$rec->status]??['Completed','#eff6ff','#1d4ed8','#3b82f6'];
                    $firstItem=$rec->items->first();
                    $deptColors=['Maintenance'=>['#e0f2fe','#0369a1'],'Produksi'=>['#dcfce7','#15803d'],'Production'=>['#dcfce7','#15803d'],'IT'=>['#ede9fe','#7c3aed'],'IT & Digital'=>['#ede9fe','#7c3aed'],'Finance'=>['#fef9c3','#92400e']];
                    [$dBg,$dText]=$deptColors[$rec->department]??['#f1f5f9','#475569'];
                @endphp
                <tr data-dept="{{ $rec->department }}" data-status="{{ $rec->status }}" style="border-bottom:1px solid #f3f4f6" onmouseover="this.style.background='#fafafa'" onmouseout="this.style.background='transparent'">
                    <td style="padding:13px 20px"><span style="font-family:'Courier New',monospace;font-size:12px;font-weight:600;color:#111827">{{ $rec->doc_number }}</span></td>
                    <td style="padding:13px 14px">
                        <div style="font-size:12.5px;font-weight:500;color:#111827">{{ $rec->vendor_name }}</div>
                        <div style="font-size:11px;color:#9ca3af;margin-top:1px">{{ $rec->vendor_city }}</div>
                    </td>
                    <td style="padding:13px 14px"><span style="display:inline-flex;align-items:center;padding:2px 9px;border-radius:6px;font-size:11.5px;font-weight:600;background:{{ $dBg }};color:{{ $dText }}">{{ $rec->department }}</span></td>
                    <td style="padding:13px 14px;font-size:12.5px;color:#111827">{{ optional($firstItem)->name??'—' }}</td>
                    <td style="padding:13px 14px;font-size:12px;color:#374151">{{ optional($firstItem)->quantity??'—' }} {{ optional($firstItem)->unit??'' }}</td>
                    <td style="padding:13px 14px;font-size:12.5px;font-weight:600;color:#111827">{{ number_format($rec->total_value,0,',','.') }}</td>
                    <td style="padding:13px 14px;font-size:12px;color:#6b7280">{{ $rec->lead_days?$rec->lead_days.' days':'–' }}</td>
                    <td style="padding:13px 14px"><span style="display:inline-flex;align-items:center;gap:4px;padding:3px 9px;border-radius:999px;background:{{ $sBg }};font-size:11.5px;font-weight:600;color:{{ $sText }};white-space:nowrap"><span style="width:5px;height:5px;border-radius:50%;background:{{ $sDot }}"></span>{{ $sLabel }}</span></td>
                </tr>
                @empty
                <tr><td colspan="8" style="text-align:center;padding:36px 20px;color:#9ca3af;font-size:12.5px">No procurement records found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<script>
function applyHFilters(){
    const d=document.getElementById('unit-filter').value;
    const s=document.getElementById('hist-status-filter').value;
    document.querySelectorAll('#history-table tbody tr[data-dept]').forEach(r=>{
        r.style.display=(!d||r.dataset.dept===d)&&(!s||r.dataset.status===s)?'':'none';
    });
}
</script>
@endsection