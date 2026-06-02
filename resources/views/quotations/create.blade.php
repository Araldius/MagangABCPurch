```html
@extends('layouts.app')
@php 
    $pageTitle = 'Manual Quotation Entry';
    $isService = $rfq->service_request_id ? true : false;
    $requestTitle = $isService ? $rfq->serviceRequest->service_name : $rfq->purchaseRequest->title;
    $docNumber = $isService ? ($rfq->serviceRequest->document_number ?? 'SR-'.$rfq->serviceRequest->id) : $rfq->purchaseRequest->document_number;
@endphp
@section('content')

<style>
:root {
    --primary:        #111827;
    --primary-light:  #f0f4ff;
    --border:         #e5e7eb;
    --border-strong:  #d1d5db;
    --text:           #111827;
    --text-muted:     #6b7280;
    --radius:         12px;
    --shadow-sm:      0 1px 3px 0 rgb(0 0 0/.08), 0 1px 2px -1px rgb(0 0 0/.06);
    --req-color:      #ef4444;
}
.page-header   { margin-bottom: 24px; }
.page-title    { font-size: 20px; font-weight: 700; color: var(--text); margin-bottom: 4px; }
.page-desc     { font-size: 13.5px; color: var(--text-muted); }
.card          { background: #fff; border: 1px solid var(--border); border-radius: var(--radius); box-shadow: var(--shadow-sm); overflow: hidden; }
.card-header   { padding: 16px 20px; border-bottom: 1px solid var(--border); background: #fafafa; }
.card-body     { padding: 20px; }
.card-title    { font-size: 14px; font-weight: 700; color: var(--text); line-height: 1.3; }
.card-desc     { font-size: 12px; color: var(--text-muted); margin-top: 2px; }
.form-section-icon { width: 34px; height: 34px; border-radius: 8px; background: var(--primary); color: white; display: flex; align-items: center; justify-content: center; margin-right: 12px; flex-shrink: 0; }
.flex-center   { display: flex; align-items: center; }
.form-group    { display: flex; flex-direction: column; margin-bottom: 16px; }
.form-row      { display: grid; gap: 16px; }
.form-row-2    { grid-template-columns: 1fr 1fr; }
.form-label    { font-size: 13px; font-weight: 600; color: var(--text); margin-bottom: 6px; }
.req           { color: var(--req-color); margin-left: 2px; }
.form-control  { width: 100%; box-sizing: border-box; padding: 8px 12px; font-size: 13.5px; border: 1px solid var(--border-strong); border-radius: 8px; background: #fff; color: var(--text); outline: none; }
.form-control:focus  { border-color: #6366f1; box-shadow: 0 0 0 3px rgb(99 102 241/.12); }
.mt-4 { margin-top: 24px; }
.flex-between  { display: flex; align-items: center; justify-content: space-between; }

/* Buttons */
.btn { display: inline-flex; align-items: center; justify-content: center; gap: 6px; padding: 9px 18px; border-radius: 8px; font-size: 13.5px; font-weight: 600; cursor: pointer; border: 1px solid transparent; text-decoration: none; }
.btn-primary  { background: #111827; color: #fff; }
.btn-primary:hover { background: #1f2937; }
.btn-outline  { background: #fff; color: var(--text); border-color: var(--border-strong); }
.btn-outline:hover { background: #f9fafb; }

/* Tables */
.item-table { width: 100%; border-collapse: collapse; font-size: 13px; }
.item-table th { text-align: left; padding: 8px 10px; color: var(--text-muted); border-bottom: 1px solid var(--border); font-weight: 600; font-size: 11px; text-transform: uppercase; }
.item-table td { padding: 10px; border-bottom: 1px solid #f3f4f6; vertical-align: middle; }

/* Modals & Catalog */
.modal-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.4); z-index: 1000; align-items: center; justify-content: center; padding: 16px; }
.modal-overlay.open { display: flex; }
.modal { background: #fff; border-radius: 14px; width: 100%; max-width: 560px; display: flex; flex-direction: column; overflow: hidden; }
.modal-header { padding: 18px 20px; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; }
.modal-title  { font-size: 15px; font-weight: 700; }
.modal-desc   { font-size: 12.5px; color: var(--text-muted); }
.modal-body   { padding: 20px; overflow-y: auto; max-height: 65vh; }
.modal-footer { padding: 14px 20px; border-top: 1px solid var(--border); display: flex; justify-content: flex-end; gap: 10px; }
.modal-close  { background: none; border: none; cursor: pointer; color: var(--text-muted); font-size: 18px; }
.item-option { padding:12px 14px;border-radius:8px;cursor:pointer;border:1px solid var(--border);transition:background .1s; margin-bottom: 6px; }
.item-option:hover { background:#f9fafb; }
.item-option.selected { background:var(--primary-light);border-color:var(--primary); }
.item-option-name { font-size:13.5px;font-weight:600;color:var(--text); }
.item-option-desc { font-size:12px;color:var(--text-muted); margin-top: 4px;}
</style>

<div class="page-header">
    <div class="page-title">Add Manual Quotation for {{ $docNumber }}</div>
    <div class="page-desc">Enter the vendor details and quoted prices for {{ $requestTitle }}</div>
</div>

<form action="{{ route('quotations.store', $rfq->id) }}" method="post" id="quote-form">
@csrf

<div class="card">
    <div class="card-header">
        <div class="flex-center">
            <div class="form-section-icon"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg></div>
            <div>
                <div class="card-title">Vendor Information</div>
                <div class="card-desc">Select an existing vendor from the database or register a new one.</div>
            </div>
        </div>
    </div>
    <div class="card-body">
        <input type="hidden" name="vendor_id" id="hidden_vendor_id" value="new">
        
        <div class="form-group flex-1">
            <label class="form-label flex-between" style="width:100%">
                <span>Vendor Name <span class="req">*</span></span>
                <a href="#" onclick="openVendorModal(); return false;" style="font-size:12px; color:#3b5bdb; text-decoration:none;">🔍 Select from Catalog</a>
            </label>
            <input class="form-control" name="new_vendor_name" id="new_vendor_name" placeholder="Enter vendor name manually or select from catalog" required>
        </div>

        <div class="form-row form-row-2" style="margin-top:16px;">
            <div class="form-group flex-1" style="margin-bottom:0;">
                <label class="form-label">Location / Address</label>
                <input class="form-control" name="new_vendor_location" id="vendor_location" placeholder="Vendor location">
            </div>
            <div class="form-group flex-1" style="margin-bottom:0;">
                <label class="form-label">Contact Person</label>
                <input class="form-control" name="new_vendor_contact" id="vendor_contact" placeholder="Vendor contact">
            </div>
        </div>
    </div>
</div>

<div class="card mt-4">
    <div class="card-header">
        <div class="flex-center">
            <div class="form-section-icon"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20 7H4a2 2 0 00-2 2v10a2 2 0 002 2h16a2 2 0 002-2V9a2 2 0 00-2-2z"/><path d="M16 7V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v2" stroke-linecap="round"/></svg></div>
            <div>
                <div class="card-title">Quoted Items & Prices</div>
                <div class="card-desc">The items below are automatically loaded from the {{ $isService ? 'Service Request' : 'Purchase Request' }}.</div>
            </div>
        </div>
    </div>
    <div class="card-body" style="padding: 0;">
        <div style="overflow-x:auto;">
            <table class="item-table">
                <thead style="background:#f9fafb;">
                    <tr>
                        <th style="width:50px;">NO</th>
                        <th>ITEM NAME</th>
                        <th>SPEC / NOTES</th>
                        <th style="width:100px;">REQUESTED QTY</th>
                        <th style="width:150px;">UNIT PRICE (Rp) <span class="req">*</span></th>
                        <th style="width:150px;text-align:right;">SUBTOTAL (Rp)</th>
                    </tr>
                </thead>
                <tbody>
                    @php $idx = 0; @endphp
                    @if($isService)
                        @foreach($rfq->serviceRequest->jobs as $job)
                            <tr><td colspan="6" style="background:#f0f4f8; font-weight:700; color:#374151;">💼 {{ $job->description ?? $job->job_description }}</td></tr>
                            @foreach($job->items as $item)
                                <tr>
                                    <td>{{ ++$idx }}</td>
                                    <td style="font-weight:600; color:var(--primary);">
                                        {{ $item->name ?? $item->item_name }}
                                        <input type="hidden" name="items[{{ $idx }}][item_id]" value="{{ $item->id }}">
                                    </td>
                                    <td style="color:var(--text-muted); font-size:12px;">{{ $item->specification ?? '-' }}</td>
                                    <td>
                                        <div style="display:flex;align-items:center;gap:6px;">
                                            <input type="number" step="0.01" class="form-control qty-input" name="items[{{ $idx }}][quantity]" value="{{ $item->quantity }}" required style="width:80px; text-align:center;">
                                            <span style="font-size:11.5px;color:var(--text-muted);">{{ $item->unit }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" class="form-control price-input" name="items[{{ $idx }}][price]" required min="0" placeholder="0">
                                    </td>
                                    <td class="subtotal-cell" style="font-weight:700; font-family:monospace; font-size:14px; text-align:right;">Rp 0</td>
                                </tr>
                            @endforeach
                        @endforeach
                    @else
                        @foreach($rfq->purchaseRequest->items as $item)
                            <tr>
                                <td>{{ ++$idx }}</td>
                                <td style="font-weight:600; color:var(--primary);">
                                    {{ $item->name ?? $item->item_name }}
                                    <input type="hidden" name="items[{{ $idx }}][item_id]" value="{{ $item->id }}">
                                </td>
                                <td style="color:var(--text-muted); font-size:12px;">{{ $item->specification ?? '-' }}</td>
                                <td>
                                    <div style="display:flex;align-items:center;gap:6px;">
                                        <input type="number" step="0.01" class="form-control qty-input" name="items[{{ $idx }}][quantity]" value="{{ $item->quantity }}" required style="width:80px; text-align:center;">
                                        <span style="font-size:11.5px;color:var(--text-muted);">{{ $item->unit }}</span>
                                    </div>
                                </td>
                                <td>
                                    <input type="number" step="0.01" class="form-control price-input" name="items[{{ $idx }}][price]" required min="0" placeholder="0">
                                </td>
                                <td class="subtotal-cell" style="font-weight:700; font-family:monospace; font-size:14px; text-align:right;">Rp 0</td>
                            </tr>
                        @endforeach
                    @endif
                </tbody>
                <tfoot>
                    <tr style="background:#f9fafb;">
                        <td colspan="5" style="text-align:right; font-weight:700; color:var(--text-muted);">Grand Total</td>
                        <td id="grand-total" style="font-weight:800; font-size:16px; color:#111827; font-family:monospace; text-align:right;">Rp 0</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<div style="display:flex;justify-content:flex-end;gap:12px;margin-top:24px;">
    <a href="{{ route('dashboard') }}" class="btn btn-outline">Cancel</a>
    <button type="submit" class="btn btn-primary">Save Quotation</button>
</div>

</form>

<div class="modal-overlay" id="vendor-modal">
    <div class="modal">
        <div class="modal-header">
            <div><div class="modal-title">Vendor Catalog</div><div class="modal-desc">Search and select registered vendors</div></div>
            <button type="button" class="modal-close" onclick="closeVendorModal()">&times;</button>
        </div>
        <div style="padding: 16px 20px 12px; border-bottom: 1px solid var(--border); background: #fafafa;">
            <input class="form-control mb-2" id="vendor-search" placeholder="Search vendor name..." oninput="filterVendors(this.value)">
        </div>
        <div class="modal-body" style="padding-top: 12px;">
            <div id="vendor-list" style="display:flex;flex-direction:column;"></div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline" onclick="closeVendorModal()">Cancel</button>
            <button type="button" class="btn btn-primary" onclick="addSelectedVendor()">Select Vendor</button>
        </div>
    </div>
</div>

<script>
    let vendors = [];
    let selectedVendorId = null;

    // Fetch vendors
    fetch('/api/vendors')
        .then(res => res.json())
        .then(data => {
            vendors = data;
        });

    function resetVendorId() {
        document.getElementById('hidden_vendor_id').value = 'new';
    }

    // Auto-caps and Autofill logic
    const vendorNameInput = document.getElementById('new_vendor_name');

    // Handle Enter key to prevent form submit and move to next field
    vendorNameInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            this.blur();
            document.getElementById('vendor_location').focus();
        }
    });

    // Format immediately as they type, autofill if match
    vendorNameInput.addEventListener('input', function() {
        let start = this.selectionStart;
        let end = this.selectionEnd;
        let val = this.value;
        
        let words = val.split(' ');
        for (let i = 0; i < words.length; i++) {
            let w = words[i];
            if (!w) continue;
            let wl = w.toLowerCase();
            if (['pt', 'pt.', 'cv', 'cv.', 'ud', 'ud.', 'tbk', 'tbk.'].includes(wl)) {
                words[i] = w.toUpperCase();
            } else {
                words[i] = w.charAt(0).toUpperCase() + w.slice(1).toLowerCase();
            }
        }
        let newVal = words.join(' ');
        
        if (this.value !== newVal) {
            this.value = newVal;
            this.setSelectionRange(start, end);
        }

        // Autofill logic
        if (!newVal.trim()) {
            resetVendorId();
            return;
        }

        const match = vendors.find(v => v.vendor_name.toLowerCase() === newVal.trim().toLowerCase());
        if (match) {
            document.getElementById('hidden_vendor_id').value = match.id;
            document.getElementById('vendor_location').value = match.location || '';
            document.getElementById('vendor_contact').value = match.contact || '';
        } else {
            resetVendorId();
        }
    });

    function filterVendors(q) { renderVendorList(q.toLowerCase()); }
    function renderVendorList(q='') {
        const filtered = vendors.filter(v => !q || v.vendor_name.toLowerCase().includes(q));
        document.getElementById('vendor-list').innerHTML = filtered.map(v => {
            const isSelected = String(selectedVendorId) === String(v.id);
            return `
            <div class="item-option ${isSelected ? 'selected' : ''}" onclick="selectVendorModal('${v.id}')">
                <div class="item-option-name">${v.vendor_name}</div>
                <div class="item-option-desc">${v.location || '-'} | ${v.contact || '-'}</div>
            </div>`;
        }).join('');
    }

    function selectVendorModal(id) { 
        selectedVendorId = id; 
        renderVendorList(document.getElementById('vendor-search').value.toLowerCase()); 
    }
    
    function openVendorModal() { 
        selectedVendorId = null; 
        document.getElementById('vendor-search').value = ''; 
        renderVendorList(); 
        document.getElementById('vendor-modal').classList.add('open'); 
    }
    
    function closeVendorModal() { 
        document.getElementById('vendor-modal').classList.remove('open'); 
    }
    
    function addSelectedVendor() {
        if(!selectedVendorId){ alert('Please select a vendor.'); return; }
        const v = vendors.find(x => x.id == selectedVendorId);
        if(!v) return;
        
        document.getElementById('hidden_vendor_id').value = v.id;
        document.getElementById('new_vendor_name').value = v.vendor_name;
        document.getElementById('vendor_location').value = v.location || '';
        document.getElementById('vendor_contact').value = v.contact || '';
        
        closeVendorModal();
    }
    
    // Close modal when clicking outside
    document.getElementById('vendor-modal').addEventListener('click', function(e) {
        if(e.target === this) this.classList.remove('open');
    });

    // Calculate totals
    const rows = document.querySelectorAll('tbody tr:not([style*="background:#f0f4f8"])');
    rows.forEach(row => {
        const qty = row.querySelector('.qty-input');
        const price = row.querySelector('.price-input');
        const sub = row.querySelector('.subtotal-cell');

        const update = () => {
            const q = parseFloat(qty.value) || 0;
            const p = parseFloat(price.value) || 0;
            sub.textContent = 'Rp ' + (q * p).toLocaleString('id-ID');
            updateGrandTotal();
        };

        if(qty) qty.addEventListener('input', update);
        if(price) price.addEventListener('input', update);
    });

    function updateGrandTotal() {
        let total = 0;
        rows.forEach(row => {
            const qty = row.querySelector('.qty-input');
            const price = row.querySelector('.price-input');
            if(qty && price) {
                total += (parseFloat(qty.value)||0) * (parseFloat(price.value)||0);
            }
        });
        document.getElementById('grand-total').textContent = 'Rp ' + total.toLocaleString('id-ID');
    }
</script>
@endsection