@extends('layouts.app')
@php $pageTitle = 'Pilih Vendor'; @endphp

@section('content')
<div class="page-header flex-between">
    <div>
        <div class="page-title">Pemilihan Vendor</div>
        <div class="page-desc">Pilih vendor untuk RFQ <strong>{{ $rfq->rfq_number ?? 'RFQ-#'.$rfq->id }}</strong></div>
    </div>
    <span class="badge badge-rfq" style="font-size:13px;padding:6px 14px;">RFQ {{ ucfirst($rfq->status) }}</span>
</div>

{{-- RFQ Summary --}}
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:24px;">
    <div class="stat-card">
        <div class="stat-label">No. RFQ</div>
        <div style="font-family:monospace;font-size:15px;font-weight:700;margin-top:6px;color:#2563eb;">{{ $rfq->rfq_number ?? '#'.$rfq->id }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Purchase Request</div>
        <div style="font-weight:700;margin-top:6px;font-size:14px;">{{ $rfq->purchaseRequest->document_number }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Departemen</div>
        <div style="font-weight:600;margin-top:6px;">{{ $rfq->purchaseRequest->department ?? '—' }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Need Date</div>
        <div style="font-weight:600;margin-top:6px;">{{ $rfq->purchaseRequest->need_date ? \Carbon\Carbon::parse($rfq->purchaseRequest->need_date)->format('d M Y') : '—' }}</div>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 360px;gap:20px;align-items:flex-start;">

    {{-- Item List --}}
    <div>
        <div class="card" style="margin-bottom:20px;">
            <div class="card-header">
                <div>
                    <div class="card-title">Item yang Diminta</div>
                    <div class="card-desc">{{ $rfq->purchaseRequest->items->count() }} item dalam PR ini.</div>
                </div>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Nama Item</th>
                            <th>Spesifikasi</th>
                            <th>Qty</th>
                            <th>Satuan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rfq->purchaseRequest->items as $item)
                        <tr>
                            <td class="td-doc" style="font-size:12px;">{{ $item->item_code }}</td>
                            <td>
                                <div style="font-weight:600;">{{ $item->item_name ?: $item->name }}</div>
                                @if($item->item_notes ?: $item->note)
                                    <div class="td-sub">{{ $item->item_notes ?: $item->note }}</div>
                                @endif
                            </td>
                            <td class="text-muted text-sm">{{ $item->specification ?? '—' }}</td>
                            <td style="font-weight:700;">{{ $item->quantity }}</td>
                            <td class="text-muted">{{ $item->unit }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Existing Vendors --}}
        @if($rfq->vendorQuotations->count())
        <div class="card">
            <div class="card-header">
                <div class="card-title">Vendor yang Sudah Diundang</div>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr><th>Vendor</th><th>Status</th><th>Submit</th></tr>
                    </thead>
                    <tbody>
                        @foreach($rfq->vendorQuotations->load('vendor') as $vq)
                        <tr>
                            <td>
                                <div style="font-weight:600;">{{ $vq->vendor->vendor_name }}</div>
                                <div class="td-sub">{{ $vq->vendor->location }}</div>
                            </td>
                            <td>
                                @if($vq->status === 'submitted')
                                    <span class="badge badge-approved">Submitted</span>
                                @else
                                    <span class="badge badge-pending">Draft</span>
                                @endif
                            </td>
                            <td class="text-muted text-sm">{{ $vq->submitted_at ? \Carbon\Carbon::parse($vq->submitted_at)->format('d M Y') : '—' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    </div>

    {{-- Vendor Selection Form --}}
    <div>
        <div class="card">
            <div class="card-header">
                <div class="card-title">Pilih Vendor</div>
                <div class="card-desc">Pilih dari daftar atau tambah vendor baru.</div>
            </div>
            <div class="card-body">
                <form action="{{ route('vendors.store', $rfq) }}" method="post">
                    @csrf

                    {{-- Toggle --}}
                    <div style="display:flex;gap:0;margin-bottom:20px;border:1px solid var(--border);border-radius:8px;overflow:hidden;">
                        <button type="button" class="toggle-btn active" id="btnExisting" onclick="switchMode('existing')"
                            style="flex:1;padding:9px;font-size:13px;font-weight:600;border:none;cursor:pointer;background:#eef2ff;color:#3b5bdb;transition:.15s;">
                            Vendor Ada
                        </button>
                        <button type="button" class="toggle-btn" id="btnNew" onclick="switchMode('new')"
                            style="flex:1;padding:9px;font-size:13px;font-weight:600;border:none;cursor:pointer;background:white;color:var(--text-muted);transition:.15s;">
                            Tambah Baru
                        </button>
                    </div>

                    {{-- Existing Vendor --}}
                    <div id="modeExisting">
                        <div class="form-group" style="margin-bottom:16px;">
                            <label class="form-label">Pilih Vendor <span class="req">*</span></label>
                            <select name="vendor_id" class="form-control" id="vendorSelect">
                                <option value="">— Pilih vendor —</option>
                                @foreach($vendors->where('status','active') as $vendor)
                                <option value="{{ $vendor->id }}" {{ old('vendor_id') == $vendor->id ? 'selected' : '' }}>
                                    {{ $vendor->vendor_name }} ({{ $vendor->location }})
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- New Vendor --}}
                    <div id="modeNew" style="display:none;">
                        <div class="form-group" style="margin-bottom:14px;">
                            <label class="form-label">Nama Vendor <span class="req">*</span></label>
                            <input type="text" name="vendor_name" class="form-control" value="{{ old('vendor_name') }}" placeholder="PT / CV ...">
                        </div>
                        <div class="form-group" style="margin-bottom:14px;">
                            <label class="form-label">Lokasi</label>
                            <input type="text" name="location" class="form-control" value="{{ old('location') }}" placeholder="Kota">
                        </div>
                        <div class="form-group" style="margin-bottom:14px;">
                            <label class="form-label">Kontak</label>
                            <input type="text" name="contact" class="form-control" value="{{ old('contact') }}" placeholder="No. telepon / email">
                        </div>
                    </div>

                    <div class="form-group" style="margin-bottom:20px;">
                        <label class="form-label">Catatan Pemilihan</label>
                        <textarea name="note" class="form-control" rows="3" placeholder="Alasan pemilihan vendor...">{{ old('note') }}</textarea>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;">
                        Konfirmasi Vendor & Lanjut
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function switchMode(mode) {
    const isExisting = mode === 'existing';
    document.getElementById('modeExisting').style.display = isExisting ? 'block' : 'none';
    document.getElementById('modeNew').style.display = isExisting ? 'none' : 'block';
    document.getElementById('btnExisting').style.cssText = 'flex:1;padding:9px;font-size:13px;font-weight:600;border:none;cursor:pointer;transition:.15s;' + (isExisting ? 'background:#eef2ff;color:#3b5bdb;' : 'background:white;color:var(--text-muted);');
    document.getElementById('btnNew').style.cssText = 'flex:1;padding:9px;font-size:13px;font-weight:600;border:none;cursor:pointer;transition:.15s;' + (!isExisting ? 'background:#eef2ff;color:#3b5bdb;' : 'background:white;color:var(--text-muted);');
    // Toggle required attributes
    document.getElementById('vendorSelect').required = isExisting;
}
</script>
@endsection