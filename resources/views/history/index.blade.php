@extends('layouts.app')
@php $pageTitle = 'Procurement History'; @endphp
@section('content')

<div class="page-header">
    <div class="page-title">Procurement History</div>
    <div class="page-desc">All selected vendors and completed procurement records.</div>
</div>

<!-- STAT CARDS -->
<div class="stat-grid">
    <div class="stat-card">
        <div class="stat-label">Vendors Used</div>
        <div class="stat-value" style="color:#111827;">{{ $vendorsUsed }}</div>
        <div class="stat-sub">Throughout {{ now()->year }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Total Value</div>
        <div class="stat-value" style="font-size:20px;color:#111827;">Rp {{ number_format($totalValue/1000000, 0) }} Jt</div>
        <div class="stat-sub">Jan–{{ now()->format('M Y') }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">PR Completed</div>
        <div class="stat-value green">{{ $prsCompleted }}</div>
        <div class="stat-sub">Year {{ now()->year }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Avg. Lead Time</div>
        <div class="stat-value yellow">{{ $avgLeadDays }} Days</div>
        <div class="stat-sub">PR to goods received</div>
    </div>
</div>

<!-- TABLE -->
<div class="card mt-6">
    <div class="card-header">
        <div class="card-title">Vendor &amp; Procurement Records</div>
        <div class="flex-center gap-2">
            <!-- Unit/Dept Filter -->
            <div style="position:relative;">
                <select class="form-control" id="unit-filter" onchange="applyFilters()"
                    style="padding:7px 32px 7px 12px;font-size:13px;min-width:120px;appearance:none;cursor:pointer;">
                    <option value="">All Units</option>
                    @foreach($departments as $dept)
                    <option value="{{ $dept }}">{{ $dept }}</option>
                    @endforeach
                </select>
                <svg style="position:absolute;right:9px;top:50%;transform:translateY(-50%);pointer-events:none;color:#6b7280;" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 9l6 6 6-6" stroke-linecap="round"/></svg>
            </div>
        </div>
    </div>

    <div class="table-wrap">
        <table id="history-table">
            <thead>
                <tr>
                    <th>DOC NO.</th>
                    <th>VENDOR NAME</th>
                    <th>UNIT / DEPT.</th>
                    <th>ITEM</th>
                    <th>QTY</th>
                    <th>VALUE (RP)</th>
                    <th>LEAD TIME</th>
                    <th>STATUS</th>
                </tr>
            </thead>
            <tbody>
                @forelse($records as $rec)
                @php
                    $statusMap = [
                        'completed' => ['Completed','badge-completed'],
                        'in_progress' => ['In Progress','badge-inprocess'],
                        'cancelled' => ['Cancelled','badge-cancelled'],
                    ];
                    // Mengambil status langsung dari object $rec yang sudah di-map di controller
                    $txStatus = strtolower($rec->status ?? 'in_progress');
                    [$label, $cls] = $statusMap[$txStatus] ?? ['In Progress','badge-inprocess'];

                    // Mengambil item pertama dari collection items yang dilempar oleh controller
                    $firstItem = $rec->items->first();
                    $itemName = optional($firstItem)->name ?? '—';
                    $qty = optional($firstItem)->quantity ?? '—';
                    $unit = optional($firstItem)->unit ?? '';
                @endphp
                <tr data-dept="{{ $rec->department }}" data-status="{{ $txStatus }}">
                    <td class="td-doc font-mono" style="font-size:13px;">{{ $rec->doc_number }}</td>
                    <td>
                        <div style="font-weight:500;">{{ $rec->vendor_name }}</div>
                        <div class="td-sub">{{ $rec->vendor_city }}</div>
                    </td>
                    <td><span class="tag tag-blue">{{ $rec->department }}</span></td>
                    <td style="font-size:13.5px;">{{ $itemName }}</td>
                    <td class="text-sm">{{ $qty }} {{ $unit }}</td>
                    <td style="font-weight:600;">{{ number_format($rec->total_value ?? 0, 0, ',', '.') }}</td>
                    <td class="text-muted text-sm">{{ $rec->lead_days ? $rec->lead_days . ' days' : '-' }}</td>
                    <td><span class="badge {{ $cls }}">{{ $label }}</span></td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="text-align:center;padding:40px;color:#9ca3af;font-size:13.5px;">
                        No procurement records found.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
function applyFilters() {
    const dept = document.getElementById('unit-filter').value;
    const status = document.getElementById('hist-status-filter').value;
    document.querySelectorAll('#history-table tbody tr[data-dept]').forEach(row => {
        const deptMatch = !dept || row.dataset.dept === dept;
        const statusMatch = !status || row.dataset.status === status;
        row.style.display = (deptMatch && statusMatch) ? '' : 'none';
    });
}
</script>

@endsection