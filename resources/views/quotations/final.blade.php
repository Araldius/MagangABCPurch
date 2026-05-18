@extends('layouts.app')
@php $pageTitle = 'Quotation Final'; @endphp

@section('content')
<div class="page-header flex-between">
    <div>
        <div class="page-title">Finalisasi Quotation</div>
        <div class="page-desc">Input harga final dari vendor terpilih untuk RFQ <strong>{{ $rfq->rfq_number ?? '#'.$rfq->id }}</strong></div>
    </div>
    <span class="badge badge-vendor" style="font-size:13px;padding:6px 14px;">Final Stage</span>
</div>

{{-- Summary --}}
<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:24px;">
    <div class="stat-card">
        <div class="stat-label">No. RFQ</div>
        <div style="font-family:monospace;font-weight:700;font-size:14px;margin-top:6px;color:#2563eb;">{{ $rfq->rfq_number ?? '#'.$rfq->id }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Vendor Terpilih</div>
        <div style="font-weight:700;font-size:14px;margin-top:6px;">{{ $rfq->vendor->name ?? '—' }}</div>
        <div class="stat-sub">{{ $rfq->vendor->location ?? '' }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Jumlah Item</div>
        <div style="font-weight:700;font-size:24px;margin-top:6px;color:#7c3aed;">{{ $items->count() }}</div>
        <div class="stat-sub">Perlu konfirmasi harga</div>
    </div>
</div>

<form action="{{ route('quotations.storeFinal', $rfq) }}" method="post">
    @csrf

    {{-- Item Pricing Table --}}
    <div class="card" style="margin-bottom:20px;">
        <div class="card-header">
            <div>
                <div class="card-title">Harga Per Item</div>
                <div class="card-desc">Input harga penawaran dari vendor untuk setiap item yang diminta.</div>
            </div>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nama Item</th>
                        <th>Spesifikasi</th>
                        <th style="width:80px;">Qty PR</th>
                        <th>Satuan</th>
                        <th style="width:160px;">Harga / Unit (Rp)</th>
                        <th style="width:120px;">Qty Ditawarkan</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($items as $index => $item)
                    <tr>
                        <td class="text-muted text-sm">{{ $loop->iteration }}</td>
                        <td>
                            <div style="font-weight:600;">{{ $item->item_name ?: $item->name }}</div>
                            <div class="td-sub">{{ $item->item_code }}</div>
                        </td>
                        <td class="text-muted text-sm">{{ $item->specification ?? '—' }}</td>
                        <td style="font-weight:700;text-align:center;">{{ $item->quantity }}</td>
                        <td class="text-muted">{{ $item->unit }}</td>
                        <td>
                            <input type="number" step="1" min="0"
                                   name="items[{{ $index }}][offered_price_per_item]"
                                   class="form-control price-input"
                                   data-row="{{ $index }}"
                                   value="{{ old('items.'.$index.'.offered_price_per_item', optional($details->get($item->id))->offered_price_per_item) }}"
                                   placeholder="0"
                                   required>
                        </td>
                        <td>
                            <input type="number" min="1"
                                   name="items[{{ $index }}][offered_quantity]"
                                   class="form-control qty-input"
                                   data-row="{{ $index }}"
                                   value="{{ old('items.'.$index.'.offered_quantity', $item->quantity) }}"
                                   required>
                        </td>
                        <td style="font-weight:700;" id="subtotal-{{ $index }}">—</td>
                        <input type="hidden" name="items[{{ $index }}][purchase_request_item_id]" value="{{ $item->id }}">
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Footer: Notes + Total --}}
    <div style="display:grid;grid-template-columns:1fr 380px;gap:20px;margin-bottom:20px;">
        <div class="card">
            <div class="card-header"><div class="card-title">Catatan Final</div></div>
            <div class="card-body">
                <textarea name="note" class="form-control" rows="5" placeholder="Catatan harga, syarat pembayaran, jadwal pengiriman...">{{ $quotation->note ?? old('note') }}</textarea>
            </div>
        </div>
        <div class="card">
            <div class="card-header"><div class="card-title">Ringkasan Quotation</div></div>
            <div class="card-body">
                <div style="margin-bottom:16px;">
                    <div class="stat-label">Total Nilai Pengadaan</div>
                    <div id="grandTotal" style="font-size:24px;font-weight:800;color:#15803d;margin-top:6px;">Rp 0</div>
                </div>
                <div class="form-group" style="margin-bottom:16px;">
                    <label class="form-label">Total Manual (Override)</label>
                    <input type="number" step="1" name="total_price" id="totalPriceInput"
                           class="form-control"
                           value="{{ $quotation->total_price ?? old('total_price') }}"
                           placeholder="0" required>
                    <div class="form-hint">Nilai ini yang akan tersimpan. Auto-hitung dari tabel di atas.</div>
                </div>
                <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;padding:12px;">
                    ✓ Submit Quotation Final
                </button>
            </div>
        </div>
    </div>
</form>

<div class="flex-between" style="justify-content:flex-start;margin-top:8px;">
    <a href="{{ route('quotations.status', $rfq) }}" class="btn btn-ghost">← Kembali ke Status RFQ</a>
</div>

<script>
function formatRp(val) {
    return 'Rp ' + Math.round(val).toLocaleString('id-ID');
}

function recalcAll() {
    let grand = 0;
    document.querySelectorAll('.price-input').forEach(function(inp) {
        const row = inp.dataset.row;
        const qty = parseFloat(document.querySelector('.qty-input[data-row="'+row+'"]').value) || 0;
        const price = parseFloat(inp.value) || 0;
        const sub = price * qty;
        grand += sub;
        const cell = document.getElementById('subtotal-' + row);
        if (cell) cell.textContent = sub > 0 ? formatRp(sub) : '—';
    });
    document.getElementById('grandTotal').textContent = formatRp(grand);
    document.getElementById('totalPriceInput').value = Math.round(grand);
}

document.querySelectorAll('.price-input, .qty-input').forEach(function(inp) {
    inp.addEventListener('input', recalcAll);
});

// Run on load
window.addEventListener('DOMContentLoaded', recalcAll);
</script>
@endsection