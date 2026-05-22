@extends('layouts.app')
@php $pageTitle = 'Vendor Selection'; @endphp
@section('content')
 
<div style="margin-bottom:20px">
    <h1 style="font-size:20px;font-weight:700;color:#111827;margin:0 0 3px">Vendor Selection</h1>
    <p style="font-size:12.5px;color:#6b7280;margin:0">Select vendor. Divide the quantity to several vendors if stock is insufficient.</p>
</div>
 
{{-- STEP 1: SELECT PR --}}
<div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:20px;margin-bottom:20px" id="step1-card">
    <div style="font-size:13.5px;font-weight:700;color:#111827;margin-bottom:14px">Select PR Number</div>
    <div style="display:flex;gap:10px;align-items:center">
        <div style="flex:1;position:relative">
            <select id="pr-select" onchange="loadPR(this.value)"
                style="width:100%;padding:9px 32px 9px 12px;border:1px solid #d1d5db;border-radius:8px;font-size:13px;color:#374151;background:#fff;appearance:none;cursor:pointer;font-family:inherit">
                <option value="">— Select PR number to view vendor offers —</option>
                @foreach($prs as $pr)
                <option value="{{ $pr->id }}">{{ $pr->document_number }} | {{ $pr->title }}</option>
                @endforeach
            </select>
            <svg style="position:absolute;right:10px;top:50%;transform:translateY(-50%);pointer-events:none;color:#6b7280" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 9l6 6 6-6" stroke-linecap="round"/></svg>
        </div>
        <button onclick="loadPR(document.getElementById('pr-select').value)"
            style="display:inline-flex;align-items:center;gap:6px;padding:9px 18px;background:#3b5bdb;color:#fff;border-radius:8px;font-size:13px;font-weight:600;border:none;cursor:pointer;white-space:nowrap"
            onmouseover="this.style.background='#3451c7'" onmouseout="this.style.background='#3b5bdb'">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35" stroke-linecap="round"/></svg>
            Show Vendors
        </button>
    </div>
</div>
 
{{-- STEP 2+: Selection workspace (shown after PR picked) --}}
<div id="selection-workspace" style="display:none">
 
    {{-- Header with PR info and back --}}
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;flex-wrap:wrap;gap:10px">
        <div>
            <h2 id="ws-title" style="font-size:16px;font-weight:700;color:#111827;margin:0"></h2>
            <p id="ws-sub" style="font-size:12px;color:#6b7280;margin:2px 0 0"></p>
        </div>
        <div style="display:flex;align-items:center;gap:10px">
            <span id="ws-status-badge" style="display:inline-flex;align-items:center;gap:5px;padding:4px 10px;border-radius:999px;background:#fff7ed;font-size:12px;font-weight:600;color:#c2410c">
                <span style="width:6px;height:6px;border-radius:50%;background:#f97316"></span>Awaiting Selection
            </span>
            <button onclick="backToStep1()" style="padding:6px 14px;border:1px solid #d1d5db;border-radius:7px;background:#fff;font-size:12.5px;font-weight:600;color:#374151;cursor:pointer">← Back</button>
        </div>
    </div>
 
    {{-- PR Item Requirements table --}}
    <div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;margin-bottom:14px">
        <div style="display:flex;align-items:center;justify-content:space-between;padding:14px 18px;border-bottom:1px solid #f3f4f6">
            <div>
                <div style="font-size:13.5px;font-weight:700;color:#111827">PR Item Requirements</div>
                <div id="ws-item-count" style="font-size:11.5px;color:#6b7280;margin-top:1px"></div>
            </div>
            <div style="font-size:12px;color:#6b7280">Items Fulfilled: <span id="sel-count" style="font-weight:700;color:#111827">0</span> of <span id="sel-total" style="font-weight:700;color:#111827">0</span></div>
        </div>
        <div style="overflow-x:auto">
            <table style="width:100%;border-collapse:collapse;font-size:12.5px">
                <thead>
                    <tr style="background:#f9fafb">
                        <th style="padding:8px 16px;text-align:left;font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase">NO</th>
                        <th style="padding:8px 16px;text-align:left;font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase">ITEM ID</th>
                        <th style="padding:8px 16px;text-align:left;font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase">ITEM NAME</th>
                        <th style="padding:8px 16px;text-align:right;font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase">TARGET QTY</th>
                        <th style="padding:8px 16px;text-align:left;font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase">STATUS PEMENUHAN</th>
                    </tr>
                </thead>
                <tbody id="items-requirement-tbody"></tbody>
            </table>
        </div>
    </div>
 
    {{-- Vendor cards (3 columns) --}}
    <div id="vendor-cards-grid" style="display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-bottom:14px"></div>
 
    {{-- Footer bar --}}
    <div style="background:#fff;border:1px solid #e5e7eb;border-radius:10px;padding:14px 18px;display:flex;align-items:center;justify-content:space-between">
        <div>
            <div style="font-size:12.5px;font-weight:600;color:#111827">Target Quantity fulfilled: <span id="footer-sel">0</span> / <span id="footer-total">0</span> items</div>
            <div style="font-size:11.5px;color:#9ca3af;margin-top:1px">Sistem akan memperingatkan jika Anda submit sebelum semua quantity terpenuhi</div>
        </div>
        <button id="show-result-btn" onclick="showSelectionResult()"
            style="display:inline-flex;align-items:center;gap:6px;padding:8px 18px;background:#111827;color:#fff;border-radius:8px;font-size:12.5px;font-weight:600;border:none;cursor:pointer;opacity:.4;pointer-events:none"
            onmouseover="this.style.opacity='1'">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="1"/></svg>
            Review & Submit
        </button>
    </div>
</div>
 
{{-- STEP 3: Selection Result / Summary --}}
<div id="result-workspace" style="display:none">
 
    {{-- Vendor total tabs --}}
    <div id="vendor-total-tabs" style="display:flex;gap:0;border-bottom:2px solid #e5e7eb;margin-bottom:16px"></div>
 
    {{-- Header --}}
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px">
        <div>
            <div id="res-pr-label" style="font-size:14px;font-weight:700;color:#111827;margin-top:3px"></div>
            <div style="font-size:12px;color:#6b7280;margin-top:2px">Review final split PO Anda sebelum diproses oleh Purchasing</div>
        </div>
        <button onclick="document.getElementById('selection-workspace').style.display='block'; document.getElementById('result-workspace').style.display='none';"
            style="padding:7px 16px;background:#f3f4f6;color:#374151;border:1px solid #d1d5db;border-radius:8px;font-size:12.5px;font-weight:600;cursor:pointer">
            ← Edit Selection
        </button>
    </div>
 
    {{-- Selected Items table --}}
    <div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;margin-bottom:14px">
        <div style="display:flex;align-items:center;justify-content:space-between;padding:14px 18px;border-bottom:1px solid #f3f4f6">
            <div style="font-size:13.5px;font-weight:700;color:#111827">Selected Items</div>
        </div>
        <div style="overflow-x:auto">
            <table style="width:100%;border-collapse:collapse;font-size:12px">
                <thead><tr style="background:#f9fafb">
                    <th style="padding:8px 14px;text-align:left;font-size:10px;font-weight:600;color:#6b7280;text-transform:uppercase">NO</th>
                    <th style="padding:8px 14px;text-align:left;font-size:10px;font-weight:600;color:#6b7280;text-transform:uppercase">ITEM NAME</th>
                    <th style="padding:8px 14px;text-align:left;font-size:10px;font-weight:600;color:#6b7280;text-transform:uppercase">VENDOR</th>
                    <th style="padding:8px 14px;text-align:right;font-size:10px;font-weight:600;color:#6b7280;text-transform:uppercase">BUY QTY</th>
                    <th style="padding:8px 14px;text-align:left;font-size:10px;font-weight:600;color:#6b7280;text-transform:uppercase">UNIT</th>
                    <th style="padding:8px 14px;text-align:right;font-size:10px;font-weight:600;color:#6b7280;text-transform:uppercase">PRICE (RP)</th>
                    <th style="padding:8px 14px;text-align:right;font-size:10px;font-weight:600;color:#6b7280;text-transform:uppercase">SUBTOTAL (RP)</th>
                </tr></thead>
                <tbody id="selected-items-tbody"></tbody>
                <tfoot>
                    <tr style="background:#f9fafb">
                        <td colspan="6" style="padding:10px 14px;text-align:right;font-weight:700;font-size:12.5px;color:#374151;border-top:1px solid #e5e7eb">Grand Total:</td>
                        <td id="grand-total-cell" style="padding:10px 14px;font-weight:800;font-size:13px;color:#111827;border-top:1px solid #e5e7eb;text-align:right">Rp 0</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
 
    {{-- Vendor Purchase Summary cards --}}
    <div style="margin-bottom:14px">
        <div style="font-size:11.5px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.07em;margin-bottom:10px">Total per Vendor</div>
        <div id="vendor-summary-cards" style="display:flex;gap:12px;flex-wrap:wrap"></div>
    </div>
 
    {{-- Confirm button --}}
    <div style="display:flex;justify-content:flex-end;margin-top:20px">
        <button onclick="openSubmitModal()"
            style="display:inline-flex;align-items:center;gap:6px;padding:12px 24px;background:#16a34a;color:#fff;border-radius:8px;font-size:14px;font-weight:700;border:none;cursor:pointer;box-shadow:0 4px 6px -1px rgba(22,163,74,.2)"
            onmouseover="this.style.background='#15803d'" onmouseout="this.style.background='#16a34a'">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
            Confirm &amp; Submit to Purchasing
        </button>
    </div>
</div>

{{-- MODAL PERINGATAN (WARNING) JIKA ITEM KURANG --}}
<div id="warning-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:400;align-items:center;justify-content:center;padding:20px;backdrop-filter:blur(2px)">
    <div style="background:#fff;border-radius:12px;width:100%;max-width:440px;box-shadow:0 10px 40px rgba(0,0,0,.2);overflow:hidden">
        <div style="background:#fef2f2;padding:20px;border-bottom:1px solid #fee2e2;display:flex;align-items:center;gap:14px">
            <div style="width:44px;height:44px;background:#fee2e2;border-radius:50%;display:flex;align-items:center;justify-content:center;color:#ef4444;flex-shrink:0">
                <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
            <div>
                <div style="font-size:16px;font-weight:700;color:#991b1b;line-height:1.2">Peringatan Kuantitas</div>
                <div style="font-size:12.5px;color:#b91c1c;margin-top:2px">Target Qty PR belum sepenuhnya terpenuhi</div>
            </div>
        </div>
        <div style="padding:22px;font-size:13.5px;color:#374151;line-height:1.6">
            Masih ada item yang kuantitasnya <strong>BELUM TERPENUHI</strong><br>
            Jika Anda melanjutkan, item yang kurang mungkin harus di-PO kembali secara terpisah nanti. Apakah Anda yakin ingin mengabaikannya dan melanjutkan?
        </div>
        <div style="padding:16px 22px;border-top:1px solid #f3f4f6;background:#f9fafb;display:flex;justify-content:flex-end;gap:10px">
            <button onclick="closeWarningModal()" style="padding:9px 18px;border:1px solid #d1d5db;border-radius:8px;background:#fff;font-size:13px;font-weight:600;color:#374151;cursor:pointer">Batalkan</button>
            <button onclick="forceShowSelectionResult()" style="padding:9px 18px;background:#ef4444;color:#fff;border-radius:8px;font-size:13px;font-weight:600;border:none;cursor:pointer;box-shadow:0 4px 6px -1px rgba(239,68,68,.2)">Ya, Tetap Lanjutkan</button>
        </div>
    </div>
</div>
 
{{-- SUBMIT NOTES MODAL --}}
<div id="submit-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:200;align-items:center;justify-content:center;padding:20px">
    <div style="background:#fff;border-radius:12px;width:100%;max-width:440px;box-shadow:0 8px 40px rgba(0,0,0,.14)">
        <div style="padding:18px 20px;border-bottom:1px solid #f3f4f6;display:flex;align-items:flex-start;justify-content:space-between">
            <div>
                <div style="font-size:14px;font-weight:700;color:#111827">Submission Notes</div>
                <div style="font-size:12px;color:#6b7280;margin-top:2px">Tambahkan catatan khusus untuk tim Purchasing</div>
            </div>
            <button onclick="closeSubmitModal()" style="background:none;border:none;cursor:pointer;color:#9ca3af;padding:4px;border-radius:6px"><svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M18 6L6 18M6 6l12 12" stroke-linecap="round"/></svg></button>
        </div>
        <div style="padding:18px 20px">
            <textarea id="submit-notes" rows="4" placeholder="Misal: Vendor A dikirim ke site 1, Vendor B ke site 2..."
                style="width:100%;padding:9px 12px;border:1px solid #d1d5db;border-radius:8px;font-size:13px;font-family:inherit;resize:vertical;transition:border-color .15s;outline:none"
                onfocus="this.style.borderColor='#3b5bdb'" onblur="this.style.borderColor='#d1d5db'"></textarea>
        </div>
        <div style="padding:14px 20px;border-top:1px solid #f3f4f6;display:flex;justify-content:flex-end;gap:10px">
            <button onclick="closeSubmitModal()" style="padding:7px 16px;border:1px solid #d1d5db;border-radius:7px;background:#fff;font-size:12.5px;font-weight:600;color:#374151;cursor:pointer">Cancel</button>
            <button onclick="submitToServer()"
                style="display:inline-flex;align-items:center;gap:5px;padding:7px 18px;background:#16a34a;color:#fff;border-radius:7px;font-size:12.5px;font-weight:600;border:none;cursor:pointer">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                Final Submit
            </button>
        </div>
    </div>
</div>
 
{{-- SUCCESS POPUP --}}
<div id="success-popup" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:300;align-items:center;justify-content:center;padding:20px">
    <div style="background:#fff;border-radius:12px;padding:32px;width:100%;max-width:400px;text-align:center;box-shadow:0 8px 40px rgba(0,0,0,.14)">
        <div style="font-size:22px;font-weight:700;color:#16a34a;margin-bottom:12px">Success!</div>
        <div style="font-size:13px;color:#374151;margin-bottom:4px">PR: <span id="popup-pr" style="font-weight:700"></span></div>
        <button onclick="closeSuccess()" style="margin-top:20px;padding:8px 24px;border:1px solid #d1d5db;border-radius:8px;background:#fff;font-size:13px;font-weight:600;cursor:pointer">Close</button>
    </div>
</div>
 
<script>
/* ─── Data from server ───────────────────────────────────────────────────── */
const serverPRs = @json($prs->load('items')->keyBy('id'));
const serverVendors = @json($vendors);
 
/* ─── State ──────────────────────────────────────────────────────────────── */
let currentPR = null;
/* Struktur selections: { "vendorId_itemId" : { vendor_id, item_id, item_name, unit_price, quantity, unit, subtotal } } */
let selections = {}; 
 
/* ─── Vendor mock-offers ─────────────────────────────────────────────────── */
function mockOffers(prItems, vendors) {
    const offers = {};
    vendors.forEach(v => {
        offers[v.id] = { vendor: v, lead_days: Math.floor(Math.random()*8)+2, items: {} };
        prItems.forEach(item => {
            const offered = Math.random()>0.1; /* 90% chance vendor offers item */
            if (!offered) return;
            const qtyOff = Math.random()>0.5 ? item.quantity : Math.floor(Math.random() * (item.quantity - 1)) + 1;
            const basePrice = Math.floor(Math.random()*500+50)*1000;
            offers[v.id].items[item.id] = {
                qty_offered: qtyOff,
                unit_price: basePrice,
                notes: qtyOff < item.quantity ? 'Stok terbatas!' : '',
                warranty: Math.random()>0.5 ? '1 yr' : '2 yrs',
            };
        });
    });
    return offers;
}
 
let vendorOffers = {};
 
function fmt(n){return 'Rp '+Number(n).toLocaleString('id-ID');}
 
/* ─── Load PR ────────────────────────────────────────────────────────────── */
function loadPR(prId) {
    if (!prId) return;
    currentPR = serverPRs[prId];
    if (!currentPR) return;
    selections = {};
    vendorOffers = mockOffers(currentPR.items, serverVendors);
 
    document.getElementById('selection-workspace').style.display='block';
    document.getElementById('result-workspace').style.display='none';
    document.getElementById('ws-title').textContent='Vendor Selection: '+currentPR.document_number;
    document.getElementById('ws-sub').textContent=currentPR.document_number+' | '+currentPR.title;
    document.getElementById('ws-item-count').textContent=currentPR.items.length+' items required';
    document.getElementById('sel-total').textContent=currentPR.items.length;
    document.getElementById('footer-total').textContent=currentPR.items.length;
    renderRequirementsTable();
    renderVendorCards();
    updateCounts();
}
 
function backToStep1(){
    document.getElementById('selection-workspace').style.display='none';
    document.getElementById('result-workspace').style.display='none';
    currentPR=null; selections={};
}
 
/* ─── Requirements table ─────────────────────────────────────────────────── */
function getItemStatus(itemId) {
    const item = currentPR.items.find(i => i.id == itemId);
    let totalSelectedQty = 0;
    
    for (let key in selections) {
        if (selections[key].item_id == itemId) {
            totalSelectedQty += selections[key].quantity;
        }
    }

    if (totalSelectedQty === 0) return ['Pending','#fff7ed','#c2410c','#f97316'];
    if (totalSelectedQty < item.quantity) return [`Partial (${totalSelectedQty}/${item.quantity})`,'#fef9c3','#854d0e','#eab308'];
    if (totalSelectedQty > item.quantity) return [`Over (${totalSelectedQty}/${item.quantity})`,'#dbeafe','#1d4ed8','#3b82f6'];
    return ['Full Match','#f0fdf4','#15803d','#22c55e'];
}

function renderRequirementsTable(){
    const tbody=document.getElementById('items-requirement-tbody');
    tbody.innerHTML=currentPR.items.map((item,i)=>{
        const [label,bg,tc,dot]=getItemStatus(item.id);
        return `<tr style="border-bottom:1px solid #f3f4f6">
            <td style="padding:11px 16px;color:#6b7280">${i+1}</td>
            <td style="padding:11px 16px;font-family:'Courier New',monospace;font-size:11.5px;color:#3b5bdb;font-weight:600">${item.item_code||'—'}</td>
            <td style="padding:11px 16px;font-weight:500;color:#111827">${item.name}</td>
            <td style="padding:11px 16px;font-weight:600;text-align:right">${item.quantity} ${item.unit}</td>
            <td style="padding:11px 16px"><span id="status-${item.id}" style="display:inline-flex;align-items:center;gap:4px;padding:3px 9px;border-radius:999px;background:${bg};font-size:11.5px;font-weight:600;color:${tc}"><span style="width:5px;height:5px;border-radius:50%;background:${dot}"></span>${label}</span></td>
        </tr>`;
    }).join('');
}
 
/* ─── Vendor cards ───────────────────────────────────────────────────────── */
function renderVendorCards(){
    const grid=document.getElementById('vendor-cards-grid');
    grid.innerHTML=serverVendors.map(v=>{
        const vName = v.vendor_name || v.name || 'Unknown Vendor';
        const off=vendorOffers[v.id];
        
        const itemCards=currentPR.items.map(item=>{
            const o=off.items[item.id];
            if(!o) return `<div style="background:#fff;border:1px solid #e5e7eb;border-radius:8px;padding:12px;margin-bottom:10px">
                <div style="font-size:14px;font-weight:700;color:#111827;margin-bottom:6px">${item.name}</div>
                <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:6px;padding:6px 10px;font-size:12px;color:#b91c1c;font-weight:600;text-align:center">❌ NOT OFFERED</div>
            </div>`;
            
            const selKey = `${v.id}_${item.id}`;
            const isSelected = !!selections[selKey];

            // Hitung Qty yang sudah terpenuhi secara global untuk item ini
            let totalSelectedQty = 0;
            for(let key in selections) {
                if (selections[key].item_id == item.id) totalSelectedQty += selections[key].quantity;
            }
            
            // Kunci checkbox jika item sudah "Full Match" DAN card ini belum di-select
            const isFullMatch = totalSelectedQty >= item.quantity;
            const disableSelection = isFullMatch && !isSelected;

            const buyQty = isSelected ? selections[selKey].quantity : Math.min(o.qty_offered, Math.max(1, item.quantity - totalSelectedQty));
            const subtotal = isSelected ? selections[selKey].subtotal : (buyQty * o.unit_price);
            const isBest = isBestPrice(v.id,item.id);
            const priceTag = isBest ? `<span style="padding:2px 5px;border-radius:4px;background:#fef9c3;color:#92400e;font-size:9.5px;font-weight:800;margin-left:4px">BEST</span>` : '';
            
            const stokBadge = o.qty_offered < item.quantity 
                ? `<span style="color:#ef4444;font-weight:700">(Stok hanya ${o.qty_offered})</span>` 
                : `<span style="color:#10b981;font-weight:700">(Stok Aman)</span>`;

            // PERUBAHAN UI: Warna Harga dan Subtotal disamakan logikanya
            return `<div style="background:#fff;border:2px solid ${isSelected?'#3b5bdb':'#e5e7eb'};border-radius:8px;padding:12px;margin-bottom:10px;cursor:${disableSelection?'not-allowed':'pointer'};opacity:${disableSelection?'0.5':'1'};transition:all .15s"
                ${disableSelection ? '' : `onclick="toggleSelect(${v.id}, ${item.id})"`}>
                <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:10px">
                    <div style="font-size:14px;font-weight:700;color:#111827;line-height:1.3">${item.name} <br><span style="font-size:11px;font-weight:500;margin-top:2px;display:inline-block">${stokBadge}</span></div>
                    <input type="checkbox" ${isSelected?'checked':''} ${disableSelection?'disabled':''} onclick="event.stopPropagation(); ${disableSelection ? '' : `toggleSelect(${v.id}, ${item.id})`}" style="width:18px;height:18px;accent-color:#3b5bdb;margin-top:2px;cursor:${disableSelection?'not-allowed':'pointer'}">
                </div>
                <div style="font-size:11.5px;color:#374151;display:grid;grid-template-columns:auto 1fr;gap:8px 10px;align-items:center">
                    <span style="color:#6b7280;font-weight:500">Harga</span>
                    <span style="font-weight:600;color:${isSelected?'#111827':'#6b7280'};font-size:12px">${fmt(o.unit_price)}${priceTag}</span>

                    <span style="color:${isSelected?'#3b5bdb':'#6b7280'};font-weight:${isSelected?'700':'500'}">Qty Beli</span>
                    <span style="display:flex;align-items:center;gap:5px">
                        <input type="number" 
                            onclick="event.stopPropagation()"
                            onchange="updateQty(${v.id}, ${item.id}, this.value)"
                            value="${buyQty}" 
                            min="1" max="${o.qty_offered}"
                            style="width:55px;padding:4px 6px;border:1px solid ${isSelected?'#3b5bdb':'#d1d5db'};border-radius:4px;font-size:12px;font-weight:600;background:${isSelected?'#eff6ff':'#f9fafb'};cursor:${disableSelection?'not-allowed':'auto'}"
                            ${!isSelected || disableSelection ? 'disabled' : ''}>
                        <span style="color:#6b7280;font-size:10.5px">/ ${item.quantity} (PR)</span>
                    </span>

                    <span style="color:#6b7280;font-weight:500">Subtotal</span>
                    <span style="font-weight:700;color:${isSelected?'#111827':'#6b7280'};font-size:12px">${fmt(subtotal)}</span>
                </div>
            </div>`;
        }).join('');
        
        return `<div style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:12px;overflow:hidden">
            <div style="padding:12px 14px;border-bottom:1px solid #e5e7eb;background:#fff">
                <div style="font-size:13.5px;font-weight:700;color:#111827">${vName}</div>
                <div style="font-size:11.5px;color:#9ca3af;margin-top:2px">${v.location||''}${off.lead_days?' • Lead: '+off.lead_days+' days':''}</div>
            </div>
            <div style="padding:10px 12px;max-height:650px;overflow-y:auto">${itemCards}</div>
            <div style="padding:10px 14px;border-top:1px solid #e5e7eb;background:#fff;font-size:12.5px;font-weight:700;color:#111827">Total Quote <span id="vendor-total-${v.id}" style="float:right">${fmt(0)}</span></div>
        </div>`;
    }).join('');
    updateVendorTotals();
}
 
function isBestPrice(vid,itemId){
    let best=Infinity,bestVid=null;
    serverVendors.forEach(v=>{
        const o=vendorOffers[v.id]?.items[itemId];
        if(o&&o.unit_price<best){best=o.unit_price;bestVid=v.id;}
    });
    return bestVid===vid;
}

/* Update Qty secara manual saat diketik */
function updateQty(vendorId, itemId, val) {
    const selKey = `${vendorId}_${itemId}`;
    if (selections[selKey]) {
        let q = parseInt(val) || 1;
        const max = vendorOffers[vendorId].items[itemId].qty_offered;
        if (q > max) q = max; 
        if (q < 1) q = 1;

        selections[selKey].quantity = q;
        selections[selKey].subtotal = q * selections[selKey].unit_price;

        renderRequirementsTable();
        renderVendorCards(); 
        updateCounts();
    }
}
 
/* Toggle selection. Auto-kalkulasi Qty yang belum terpenuhi jika split PO */
function toggleSelect(vendorId, itemId){
    const selKey = `${vendorId}_${itemId}`;

    if(selections[selKey]){
        delete selections[selKey];
    } else {
        const item = currentPR.items.find(i => i.id == itemId);
        const offer = vendorOffers[vendorId].items[itemId];
        if(item && offer) {
            let qtyAlreadySelected = 0;
            for(let key in selections) {
                if (selections[key].item_id == itemId) qtyAlreadySelected += selections[key].quantity;
            }
            
            // Proteksi berlapis, cegah select jika item sudah terpenuhi
            if (qtyAlreadySelected >= item.quantity) {
                return;
            }

            let remainingNeed = item.quantity - qtyAlreadySelected;
            if (remainingNeed < 1) remainingNeed = 1; 

            let defaultBuyQty = Math.min(remainingNeed, offer.qty_offered);

            selections[selKey] = {
                vendor_id: vendorId,
                item_id: itemId,
                item_name: item.item_name || item.name,
                unit_price: offer.unit_price,
                quantity: defaultBuyQty,
                unit: item.unit,
                notes: 'Selected'
            };
            selections[selKey].subtotal = defaultBuyQty * offer.unit_price;
        }
    }
    renderRequirementsTable();
    renderVendorCards(); 
    updateCounts();
}
 
function updateCounts(){
    let itemsMet = 0;
    const tot = currentPR ? currentPR.items.length : 0;
    
    if (currentPR) {
        currentPR.items.forEach(item => {
            let t = 0;
            for(let key in selections) {
                if(selections[key].item_id == item.id) t += selections[key].quantity;
            }
            if (t >= item.quantity) itemsMet++;
        });
    }

    document.getElementById('sel-count').textContent = itemsMet;
    document.getElementById('footer-sel').textContent = itemsMet;
    const btn = document.getElementById('show-result-btn');
    
    // Tombol submit selalu aktif asalkan minimal 1 item ada yang terpilih. 
    // Validasinya dipindah ke saat tombol diklik.
    if(Object.keys(selections).length > 0) {
        btn.style.opacity='1'; btn.style.pointerEvents='auto'; btn.style.background='#16a34a';
        btn.innerHTML = `<svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg> Review & Submit`;
    } else {
        btn.style.opacity='.4'; btn.style.pointerEvents='none'; btn.style.background='#111827';
        btn.innerHTML = `<svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="1"/></svg> Review & Submit`;
    }
}
 
function updateVendorTotals(){
    serverVendors.forEach(v=>{
        let t=0;
        currentPR.items.forEach(item=>{
            const o = vendorOffers[v.id]?.items[item.id];
            if(o) t += (o.qty_offered * o.unit_price);
        });
        const el=document.getElementById('vendor-total-'+v.id);
        if(el)el.textContent=fmt(t);
    });
}
 
/* ─── Pengecekan & Render Hasil Akhir ────────────────────────────────────── */
function showSelectionResult() {
    if (!currentPR) return;

    // VALIDASI JIKA TARGET QTY MASIH KURANG
    let itemsMet = 0;
    currentPR.items.forEach(item => {
        let t = 0;
        for(let key in selections) {
            if(selections[key].item_id == item.id) t += selections[key].quantity;
        }
        if (t >= item.quantity) itemsMet++;
    });

    // Jika masih ada item yang kurang, Tampilkan Custom Modal Warning
    if (itemsMet < currentPR.items.length) {
        document.getElementById('warning-modal').style.display = 'flex';
    } else {
        // Jika sudah terpenuhi semua, langsung ke workspace hasil
        renderResultWorkspace();
    }
}

/* ─── Fungsi Logika Navigasi Modal Warning ───────────────────────────────── */
function closeWarningModal() {
    document.getElementById('warning-modal').style.display = 'none';
}

function forceShowSelectionResult() {
    closeWarningModal();
    renderResultWorkspace();
}

function renderResultWorkspace() {
    document.getElementById('selection-workspace').style.display='none';
    document.getElementById('result-workspace').style.display='block';
 
    document.getElementById('res-pr-label').textContent='Summary Split PO untuk '+currentPR.document_number;
 
    /* Selected Items table */
    let grandTotal=0;
    let rowNum=1;
    
    const itemsArr = Object.values(selections).map((s) => {
        const item = currentPR.items.find(x => x.id == s.item_id);
        const v = serverVendors.find(x => x.id == s.vendor_id) || {};
        const vName = v.vendor_name || v.name || s.vendor_id;
        const total = s.subtotal;
        grandTotal += total;
        
        return `<tr style="border-bottom:1px solid #f3f4f6">
            <td style="padding:10px 14px;color:#6b7280">${rowNum++}</td>
            <td style="padding:10px 14px;font-weight:600;color:#111827">${s.item_name} <br><span style="font-family:'Courier New',monospace;font-size:10px;color:#6b7280">${item.item_code||'—'}</span></td>
            <td style="padding:10px 14px"><span style="padding:3px 8px;border-radius:6px;background:#dbeafe;color:#1d4ed8;font-size:11px;font-weight:700">${vName}</span></td>
            <td style="padding:10px 14px;text-align:right;font-weight:700;font-size:13px">${s.quantity}</td>
            <td style="padding:10px 14px">${s.unit}</td>
            <td style="padding:10px 14px;text-align:right">${Number(s.unit_price).toLocaleString('id-ID')}</td>
            <td style="padding:10px 14px;text-align:right;font-weight:700;color:#111827">${Number(total).toLocaleString('id-ID')}</td>
        </tr>`;
    }).join('');
    
    document.getElementById('selected-items-tbody').innerHTML = itemsArr;
    document.getElementById('grand-total-cell').textContent = fmt(grandTotal);
 
    /* Vendor summary cards */
    const vSummaries={};
    Object.values(selections).forEach(s => {
        const v = serverVendors.find(x => x.id == s.vendor_id) || {};
        const vName = v.vendor_name || v.name || s.vendor_id;
        if(!vSummaries[s.vendor_id]) { vSummaries[s.vendor_id] = { name:vName, items:[], total:0 }; }
        vSummaries[s.vendor_id].items.push({ name:s.item_name, qty:s.quantity, unit:s.unit, price:s.unit_price, sub:s.subtotal });
        vSummaries[s.vendor_id].total += s.subtotal;
    });

    document.getElementById('vendor-summary-cards').innerHTML = Object.values(vSummaries).map(vs=>`
        <div style="background:#fff;border:1px solid #e5e7eb;border-radius:10px;padding:14px;min-width:250px;flex:1">
            <div style="display:flex;justify-content:space-between;border-bottom:1px solid #f3f4f6;padding-bottom:10px;margin-bottom:10px">
                <div style="font-size:13.5px;font-weight:700;color:#1d4ed8">${vs.name}</div>
                <div style="font-size:13.5px;font-weight:800;color:#111827">${fmt(vs.total)}</div>
            </div>
            ${vs.items.map(it=>`<div style="margin-bottom:8px">
                <div style="font-size:12.5px;font-weight:600;color:#374151">${it.name}</div>
                <div style="font-size:11.5px;color:#9ca3af">${it.qty} ${it.unit} × Rp ${Number(it.price).toLocaleString('id-ID')} <span style="float:right;font-weight:700;color:#4b5563">${fmt(it.sub)}</span></div>
            </div>`).join('')}
        </div>
    `).join('');
}
 
/* ─── Submit modal ───────────────────────────────────────────────────────── */
function openSubmitModal(){document.getElementById('submit-modal').style.display='flex';}
function closeSubmitModal(){document.getElementById('submit-modal').style.display='none';}
 
function submitToServer(){
    const notes=document.getElementById('submit-notes').value.trim();
    const payload={
        pr_id:currentPR.id,
        selection_notes:notes,
        selections: Object.values(selections).map(s => ({
            vendor_id: s.vendor_id,
            item_id: s.item_id,
            unit_price: s.unit_price,
            quantity: s.quantity,
            notes: s.notes,
        })),
        _token:document.querySelector('meta[name=csrf-token]')?.content||'',
    };
    
    fetch('{{ route("vendors.store.selection") }}',{
        method:'POST',
        headers:{'Content-Type':'application/json','X-CSRF-TOKEN':payload._token},
        body:JSON.stringify(payload),
    }).then(r=>r.json()).then(data=>{
        closeSubmitModal();
        document.getElementById('popup-pr').textContent=data.pr_number||currentPR.document_number;
        document.getElementById('success-popup').style.display='flex';
    }).catch(()=>{
        closeSubmitModal();
        document.getElementById('popup-pr').textContent=currentPR.document_number;
        document.getElementById('success-popup').style.display='flex';
    });
}
 
function closeSuccess(){
    document.getElementById('success-popup').style.display='none';
    backToStep1();
}
 
/* ─── Overlay close ──────────────────────────────────────────────────────── */
['warning-modal', 'submit-modal', 'success-popup'].forEach(id=>{
    document.getElementById(id).addEventListener('click',function(e){if(e.target===this)this.style.display='none';});
});
</script>
@endsection