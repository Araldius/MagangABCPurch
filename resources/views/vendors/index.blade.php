@extends('layouts.app')
@php $pageTitle = 'Vendor Selection'; @endphp

@section('content')
<div class="page-header">
    <div class="page-title">Vendor Management</div>
    <div class="page-desc">Daftar vendor aktif dan riwayat pengadaan per vendor.</div>
</div>

{{-- Stat Cards --}}
<div class="stat-grid" style="grid-template-columns: repeat(4,1fr); gap:16px; margin-bottom:24px;">
    @php
        $totalVendors   = $vendors->count();
        $activeVendors  = $vendors->where('status','active')->count();
        $inactiveVendors = $vendors->where('status','inactive')->count();
    @endphp
    <div class="stat-card">
        <div class="stat-label">Total Vendor</div>
        <div class="stat-value blue">{{ $totalVendors }}</div>
        <div class="stat-sub">Terdaftar di sistem</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Vendor Aktif</div>
        <div class="stat-value green">{{ $activeVendors }}</div>
        <div class="stat-sub">Siap menerima RFQ</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Vendor Nonaktif</div>
        <div class="stat-value" style="color:#6b7280;">{{ $inactiveVendors }}</div>
        <div class="stat-sub">Tidak aktif</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">RFQ Terbuka</div>
        <div class="stat-value orange">{{ \App\Models\Rfq::where('status','open')->count() }}</div>
        <div class="stat-sub">Menunggu vendor</div>
    </div>
</div>

{{-- Vendor Table --}}
<div class="card">
    <div class="card-header">
        <div>
            <div class="card-title">Daftar Vendor</div>
            <div class="card-desc">Semua vendor yang terdaftar dalam sistem pengadaan.</div>
        </div>
        @if(auth()->user()->role === 'purchasing')
        <a href="{{ route('rfqs.create') }}" class="btn btn-primary btn-sm">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14" stroke-linecap="round"/></svg>
            Buat RFQ Baru
        </a>
        @endif
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nama Vendor</th>
                    <th>Lokasi</th>
                    <th>Kontak</th>
                    <th>Status</th>
                    <th>Total RFQ</th>
                    <th>Terakhir Dipilih</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($vendors as $vendor)
                @php
                    $rfqCount = $vendor->rfqs->count();
                    $lastSel  = $vendor->vendorSelections->sortByDesc('decided_at')->first();
                @endphp
                <tr>
                    <td class="text-muted text-sm">{{ $loop->iteration }}</td>
                    <td>
                        <div style="font-weight:600;">{{ $vendor->vendor_name }}</div>
                        <div class="td-sub">{{ $vendor->location }}</div>
                    </td>
                    <td class="text-muted">{{ $vendor->location ?? '—' }}</td>
                    <td class="text-muted text-sm">{{ $vendor->contact ?? '—' }}</td>
                    <td>
                        @if($vendor->status === 'active')
                            <span class="badge badge-approved">Active</span>
                        @else
                            <span class="badge badge-cancelled">Inactive</span>
                        @endif
                    </td>
                    <td>
                        <span class="tag tag-blue">{{ $rfqCount }} RFQ</span>
                    </td>
                    <td class="text-muted text-sm">
                        {{ $lastSel ? \Carbon\Carbon::parse($lastSel->decided_at)->format('d M Y') : '—' }}
                    </td>
                    <td>
                        @if(auth()->user()->role === 'purchasing')
                        <button class="btn btn-outline btn-sm" onclick="openVendorModal({{ json_encode(['name'=>$vendor->name,'location'=>$vendor->location,'contact'=>$vendor->contact,'status'=>$vendor->status,'rfq_count'=>$rfqCount]) }})">
                            Detail
                        </button>
                        @else
                        <span class="text-muted text-sm">—</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="text-align:center;padding:40px;color:var(--text-muted);">
                        Belum ada vendor terdaftar.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Open RFQs Section --}}
@if(auth()->user()->role === 'purchasing')
<div class="card mt-6">
    <div class="card-header">
        <div>
            <div class="card-title">RFQ Membutuhkan Vendor</div>
            <div class="card-desc">RFQ yang belum memiliki vendor terpilih.</div>
        </div>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>No. RFQ</th>
                    <th>Purchase Request</th>
                    <th>Departemen</th>
                    <th>Status RFQ</th>
                    <th>Dibuka</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse(\App\Models\Rfq::with('purchaseRequest.user')->where('status','open')->latest()->get() as $rfq)
                <tr>
                    <td class="td-doc">{{ $rfq->rfq_number ?? 'RFQ-#'.$rfq->id }}</td>
                    <td>
                        <div style="font-weight:600;">{{ $rfq->purchaseRequest->document_number }}</div>
                        <div class="td-sub">{{ $rfq->purchaseRequest->title ?? '—' }}</div>
                    </td>
                    <td><span class="tag tag-blue">{{ $rfq->purchaseRequest->department ?? '—' }}</span></td>
                    <td><span class="badge badge-rfq">Open</span></td>
                    <td class="text-muted text-sm">{{ \Carbon\Carbon::parse($rfq->opened_at)->format('d M Y') }}</td>
                    <td>
                        <a href="{{ route('vendors.select', $rfq) }}" class="btn btn-primary btn-sm">Pilih Vendor</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align:center;padding:32px;color:var(--text-muted);">
                        Semua RFQ sudah memiliki vendor. 🎉
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endif

{{-- Vendor Detail Modal --}}
<div class="modal-overlay" id="vendorModal">
    <div class="modal" style="max-width:460px;">
        <div class="modal-header">
            <div>
                <div class="modal-title" id="mVendorName">Vendor Detail</div>
                <div class="modal-desc" id="mVendorLocation">—</div>
            </div>
            <button class="modal-close" onclick="closeVendorModal()">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M18 6L6 18M6 6l12 12" stroke-linecap="round"/></svg>
            </button>
        </div>
        <div class="modal-body">
            <div style="display:grid;gap:16px;">
                <div style="display:flex;gap:16px;">
                    <div style="flex:1;background:#f9fafb;border-radius:8px;padding:14px;">
                        <div class="stat-label">Kontak</div>
                        <div id="mContact" style="font-weight:600;margin-top:4px;">—</div>
                    </div>
                    <div style="flex:1;background:#f9fafb;border-radius:8px;padding:14px;">
                        <div class="stat-label">Status</div>
                        <div id="mStatus" style="margin-top:4px;">—</div>
                    </div>
                </div>
                <div style="background:#f9fafb;border-radius:8px;padding:14px;">
                    <div class="stat-label">Total RFQ Diikuti</div>
                    <div id="mRfqCount" style="font-size:20px;font-weight:700;color:#2563eb;margin-top:4px;">—</div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-outline" onclick="closeVendorModal()">Tutup</button>
        </div>
    </div>
</div>

<script>
function openVendorModal(data) {
    document.getElementById('mVendorName').textContent = data.name;
    document.getElementById('mVendorLocation').textContent = data.location || 'Lokasi tidak tersedia';
    document.getElementById('mContact').textContent = data.contact || '—';
    document.getElementById('mStatus').innerHTML = data.status === 'active'
        ? '<span class="badge badge-approved">Active</span>'
        : '<span class="badge badge-cancelled">Inactive</span>';
    document.getElementById('mRfqCount').textContent = data.rfq_count + ' RFQ';
    document.getElementById('vendorModal').classList.add('open');
}
function closeVendorModal() {
    document.getElementById('vendorModal').classList.remove('open');
}
document.getElementById('vendorModal').addEventListener('click', function(e) {
    if (e.target === this) closeVendorModal();
});
</script>
@endsection