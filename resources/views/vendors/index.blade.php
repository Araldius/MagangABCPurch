@extends('layouts.app')
@php $pageTitle = 'Vendor Selection'; @endphp
@section('content')
 
<div style="margin-bottom:20px">
    <h1 style="font-size:20px;font-weight:700;color:#111827;margin:0 0 3px">Vendor Selection</h1>
    <p style="font-size:12.5px;color:#6b7280;margin:0">Select a PR, then choose items from each vendor offer.</p>
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
 
    {{-- Legend --}}
    <div style="background:#fff;border:1px solid #e5e7eb;border-radius:8px;padding:10px 16px;margin-bottom:14px;display:flex;align-items:center;gap:6px;flex-wrap:wrap;font-size:11.5px;color:#374151">
        <span style="font-weight:600;color:#6b7280;margin-right:4px">Legend:</span>
        <span style="display:inline-flex;align-items:center;gap:4px"><span style="width:10px;height:10px;border-radius:50%;background:#22c55e"></span> Perfect Match</span>
        <span style="display:inline-flex;align-items:center;gap:4px;margin-left:8px"><span style="width:10px;height:10px;border-radius:2px;background:#fde68a;border:1px solid #f59e0b"></span> Qty Less</span>
        <span style="display:inline-flex;align-items:center;gap:4px;margin-left:8px"><span style="width:10px;height:10px;border-radius:2px;background:#bfdbfe;border:1px solid #3b82f6"></span> Qty More</span>
        <span style="display:inline-flex;align-items:center;gap:4px;margin-left:8px"><span style="width:10px;height:10px;border-radius:2px;background:#ddd6fe;border:1px solid #7c3aed"></span> Unit Different</span>
        <span style="display:inline-flex;align-items:center;gap:4px;margin-left:8px"><span style="width:10px;height:10px;border-radius:2px;background:#fee2e2;border:1px solid #ef4444"></span> Not offered</span>
    </div>
 
    {{-- PR Item Requirements table --}}
    <div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;margin-bottom:14px">
        <div style="display:flex;align-items:center;justify-content:space-between;padding:14px 18px;border-bottom:1px solid #f3f4f6">
            <div>
                <div style="font-size:13.5px;font-weight:700;color:#111827">PR Item Requirements</div>
                <div id="ws-item-count" style="font-size:11.5px;color:#6b7280;margin-top:1px"></div>
            </div>
            <div style="font-size:12px;color:#6b7280">Selected: <span id="sel-count" style="font-weight:700;color:#111827">0</span> of <span id="sel-total" style="font-weight:700;color:#111827">0</span></div>
        </div>
        <div style="overflow-x:auto">
            <table style="width:100%;border-collapse:collapse;font-size:12.5px">
                <thead>
                    <tr style="background:#f9fafb">
                        <th style="padding:8px 16px;text-align:left;font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase">NO</th>
                        <th style="padding:8px 16px;text-align:left;font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase">ITEM ID</th>
                        <th style="padding:8px 16px;text-align:left;font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase">ITEM NAME</th>
                        <th style="padding:8px 16px;text-align:left;font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase">SPECIFICATION</th>
                        <th style="padding:8px 16px;text-align:left;font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase">REQUIRED QTY</th>
                        <th style="padding:8px 16px;text-align:left;font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase">UNIT</th>
                        <th style="padding:8px 16px;text-align:left;font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase">STATUS</th>
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
            <div style="font-size:12.5px;font-weight:600;color:#111827">Items selected: <span id="footer-sel">0</span> / <span id="footer-total">0</span></div>
            <div style="font-size:11.5px;color:#9ca3af;margin-top:1px">Select all required items to enable submit</div>
        </div>
        <button id="show-result-btn" onclick="showSelectionResult()"
            style="display:inline-flex;align-items:center;gap:6px;padding:8px 18px;background:#111827;color:#fff;border-radius:8px;font-size:12.5px;font-weight:600;border:none;cursor:pointer;opacity:.4;pointer-events:none"
            onmouseover="this.style.opacity='1'" onmouseout="">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="1"/></svg>
            Show Selection Result
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
            <div style="font-size:12px;color:#6b7280">Items selected: <span id="res-sel" style="font-weight:700;color:#111827">0</span> / <span id="res-total" style="font-weight:700;color:#111827">0</span></div>
            <div style="font-size:11.5px;color:#9ca3af;margin-top:1px">Select all required items to enable submit</div>
            <div id="res-pr-label" style="font-size:11.5px;color:#6b7280;margin-top:3px"></div>
        </div>
        <button onclick="showSelectionResult()"
            style="display:inline-flex;align-items:center;gap:6px;padding:7px 16px;background:#111827;color:#fff;border-radius:8px;font-size:12.5px;font-weight:600;border:none;cursor:pointer">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="1"/></svg>
            Show Selection Result
        </button>
    </div>
 
    {{-- Summary Information --}}
    <div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;margin-bottom:14px">
        <div style="display:flex;align-items:center;justify-content:space-between;padding:14px 18px;border-bottom:1px solid #f3f4f6">
            <div style="font-size:13.5px;font-weight:700;color:#111827">Summary Information</div>
            <span style="display:inline-flex;align-items:center;gap:5px;padding:4px 10px;border-radius:999px;background:#f0fdf4;font-size:12px;font-weight:600;color:#15803d"><span style="width:6px;height:6px;border-radius:50%;background:#22c55e"></span>Ready for Purchasing</span>
        </div>
        <table style="width:100%;border-collapse:collapse;font-size:12.5px">
            <thead><tr style="background:#f9fafb">
                <th style="padding:9px 18px;text-align:left;font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase;width:200px">FIELD</th>
                <th style="padding:9px 18px;text-align:left;font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase">DETAIL</th>
            </tr></thead>
            <tbody>
                <tr style="border-bottom:1px solid #f3f4f6"><td style="padding:11px 18px;font-weight:600;color:#374151">PR Number</td><td id="sum-pr-number" style="padding:11px 18px;color:#111827;font-family:'Courier New',monospace;font-weight:600"></td></tr>
                <tr style="border-bottom:1px solid #f3f4f6"><td style="padding:11px 18px;font-weight:600;color:#374151">Submission Date</td><td id="sum-sub-date" style="padding:11px 18px;color:#111827"></td></tr>
                <tr><td style="padding:11px 18px;font-weight:600;color:#374151">Selection Notes</td><td id="sum-notes" style="padding:11px 18px;color:#6b7280">—</td></tr>
            </tbody>
        </table>
    </div>
 
    {{-- Selected Items table --}}
    <div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;margin-bottom:14px">
        <div style="display:flex;align-items:center;justify-content:space-between;padding:14px 18px;border-bottom:1px solid #f3f4f6">
            <div style="font-size:13.5px;font-weight:700;color:#111827">Selected Items</div>
            <div id="sum-items-ready" style="font-size:12px;color:#6b7280"></div>
        </div>
        <div style="overflow-x:auto">
            <table style="width:100%;border-collapse:collapse;font-size:12px">
                <thead><tr style="background:#f9fafb">
                    <th style="padding:8px 14px;text-align:left;font-size:10px;font-weight:600;color:#6b7280;text-transform:uppercase">NO</th>
                    <th style="padding:8px 14px;text-align:left;font-size:10px;font-weight:600;color:#6b7280;text-transform:uppercase">ITEM ID</th>
                    <th style="padding:8px 14px;text-align:left;font-size:10px;font-weight:600;color:#6b7280;text-transform:uppercase">ITEM NAME</th>
                    <th style="padding:8px 14px;text-align:left;font-size:10px;font-weight:600;color:#6b7280;text-transform:uppercase">SPECIFICATION</th>
                    <th style="padding:8px 14px;text-align:left;font-size:10px;font-weight:600;color:#6b7280;text-transform:uppercase">QTY</th>
                    <th style="padding:8px 14px;text-align:left;font-size:10px;font-weight:600;color:#6b7280;text-transform:uppercase">UNIT</th>
                    <th style="padding:8px 14px;text-align:right;font-size:10px;font-weight:600;color:#6b7280;text-transform:uppercase">UNIT PRICE (RP)</th>
                    <th style="padding:8px 14px;text-align:right;font-size:10px;font-weight:600;color:#6b7280;text-transform:uppercase">TOTAL (RP)</th>
                    <th style="padding:8px 14px;text-align:left;font-size:10px;font-weight:600;color:#6b7280;text-transform:uppercase">VENDOR</th>
                    <th style="padding:8px 14px;text-align:left;font-size:10px;font-weight:600;color:#6b7280;text-transform:uppercase">NOTES</th>
                </tr></thead>
                <tbody id="selected-items-tbody"></tbody>
                <tfoot>
                    <tr style="background:#f9fafb">
                        <td colspan="7" style="padding:10px 14px;text-align:right;font-weight:700;font-size:12.5px;color:#374151;border-top:1px solid #e5e7eb">Grand Total:</td>
                        <td id="grand-total-cell" style="padding:10px 14px;font-weight:800;font-size:13px;color:#111827;border-top:1px solid #e5e7eb;text-align:right">Rp 0</td>
                        <td colspan="2" style="border-top:1px solid #e5e7eb"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
 
    {{-- Vendor Purchase Summary cards --}}
    <div style="margin-bottom:14px">
        <div style="font-size:11.5px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.07em;margin-bottom:10px">Vendor Purchase Summary</div>
        <div id="vendor-summary-cards" style="display:flex;gap:12px;flex-wrap:wrap"></div>
    </div>
 
    {{-- Confirm button --}}
    <div style="display:flex;justify-content:flex-end">
        <button onclick="openSubmitModal()"
            style="display:inline-flex;align-items:center;gap:6px;padding:10px 22px;background:#16a34a;color:#fff;border-radius:8px;font-size:13px;font-weight:600;border:none;cursor:pointer"
            onmouseover="this.style.background='#15803d'" onmouseout="this.style.background='#16a34a'">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
            Confirm &amp; Submit to Purchasing
        </button>
    </div>
</div>
 
{{-- SUBMIT NOTES MODAL --}}
<div id="submit-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:200;align-items:center;justify-content:center;padding:20px">
    <div style="background:#fff;border-radius:12px;width:100%;max-width:440px;box-shadow:0 8px 40px rgba(0,0,0,.14)">
        <div style="padding:18px 20px;border-bottom:1px solid #f3f4f6;display:flex;align-items:flex-start;justify-content:space-between">
            <div>
                <div style="font-size:14px;font-weight:700;color:#111827">Submission Notes</div>
                <div style="font-size:12px;color:#6b7280;margin-top:2px">Add remarks before submit to Purchasing</div>
            </div>
            <button onclick="closeSubmitModal()" style="background:none;border:none;cursor:pointer;color:#9ca3af;padding:4px;border-radius:6px"><svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M18 6L6 18M6 6l12 12" stroke-linecap="round"/></svg></button>
        </div>
        <div style="padding:18px 20px">
            <label style="font-size:11px;font-weight:600;color:#374151;text-transform:uppercase;letter-spacing:.05em;display:block;margin-bottom:6px">Notes / Remarks</label>
            <textarea id="submit-notes" rows="4" placeholder="e.g. Prioritize fast delivery items..."
                style="width:100%;padding:9px 12px;border:1px solid #d1d5db;border-radius:8px;font-size:13px;font-family:inherit;resize:vertical;transition:border-color .15s;outline:none"
                onfocus="this.style.borderColor='#3b5bdb'" onblur="this.style.borderColor='#d1d5db'"></textarea>
        </div>
        <div style="padding:14px 20px;border-top:1px solid #f3f4f6;display:flex;justify-content:flex-end;gap:10px">
            <button onclick="closeSubmitModal()" style="padding:7px 16px;border:1px solid #d1d5db;border-radius:7px;background:#fff;font-size:12.5px;font-weight:600;color:#374151;cursor:pointer">Cancel</button>
            <button onclick="submitToServer()"
                style="display:inline-flex;align-items:center;gap:5px;padding:7px 18px;background:#16a34a;color:#fff;border-radius:7px;font-size:12.5px;font-weight:600;border:none;cursor:pointer">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                Submit to Purchasing
            </button>
        </div>
    </div>
</div>
 
{{-- SUCCESS POPUP --}}
<div id="success-popup" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:300;align-items:center;justify-content:center;padding:20px">
    <div style="background:#fff;border-radius:12px;padding:32px;width:100%;max-width:400px;text-align:center;box-shadow:0 8px 40px rgba(0,0,0,.14)">
        <div style="font-size:22px;font-weight:700;color:#16a34a;margin-bottom:12px">Success!</div>
        <div style="font-size:13px;color:#374151;margin-bottom:4px">PR: <span id="popup-pr" style="font-weight:700"></span></div>
        <div style="font-size:13px;color:#374151">Notes: <span id="popup-notes" style="font-weight:700"></span></div>
        <button onclick="closeSuccess()" style="margin-top:20px;padding:8px 24px;border:1px solid #d1d5db;border-radius:8px;background:#fff;font-size:13px;font-weight:600;cursor:pointer">Close</button>
    </div>
</div>
 
<script>
/* ─── Data from server ───────────────────────────────────────────────────── */
const serverPRs = @json($prs->load('items')->keyBy('id'));
const serverVendors = @json($vendors);
 
/* ─── State ──────────────────────────────────────────────────────────────── */
let currentPR = null;
let selections = {}; /* {itemId: {vendor_id, vendor_name, price, qty, notes}} */
 
/* ─── Vendor mock-offers (in real system this comes from vendor_quotations) ─ */
function mockOffers(prItems, vendors) {
    const offers = {};
    vendors.forEach(v => {
        offers[v.id] = {
            vendor: v,
            lead_days: Math.floor(Math.random()*8)+2,
            items: {}
        };
        prItems.forEach(item => {
            const offered = Math.random()>0.1; /* 90% chance vendor offers item */
            if (!offered) { offers[v.id].items[item.id] = null; return; }
            const qtyOff = Math.random()>0.3 ? item.quantity : item.quantity - Math.ceil(item.quantity*0.3);
            const basePrice = Math.floor(Math.random()*500+50)*1000;
            offers[v.id].items[item.id] = {
                qty_offered: qtyOff,
                unit_price: basePrice,
                subtotal: basePrice * qtyOff,
                notes: '',
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
    if (!selections[itemId]) return ['Pending','#fff7ed','#c2410c','#f97316'];
    return ['Match','#f0fdf4','#15803d','#22c55e'];
}
function renderRequirementsTable(){
    const tbody=document.getElementById('items-requirement-tbody');
    tbody.innerHTML=currentPR.items.map((item,i)=>{
        const [label,bg,tc,dot]=getItemStatus(item.id);
        return `<tr style="border-bottom:1px solid #f3f4f6">
            <td style="padding:11px 16px;color:#6b7280">${i+1}</td>
            <td style="padding:11px 16px;font-family:'Courier New',monospace;font-size:11.5px;color:#3b5bdb;font-weight:600">${item.item_code||'—'}</td>
            <td style="padding:11px 16px;font-weight:500;color:#111827">${item.name}</td>
            <td style="padding:11px 16px;font-size:11.5px;color:#6b7280">${item.specification||'—'}</td>
            <td style="padding:11px 16px;font-weight:600">${item.quantity}</td>
            <td style="padding:11px 16px;color:#374151">${item.unit}</td>
            <td style="padding:11px 16px"><span id="status-${item.id}" style="display:inline-flex;align-items:center;gap:4px;padding:3px 9px;border-radius:999px;background:${bg};font-size:11.5px;font-weight:600;color:${tc}"><span style="width:5px;height:5px;border-radius:50%;background:${dot}"></span>${label}</span></td>
        </tr>`;
    }).join('');
}
 
/* ─── Vendor cards ───────────────────────────────────────────────────────── */
function renderVendorCards(){
    const grid=document.getElementById('vendor-cards-grid');
    grid.innerHTML=serverVendors.map(v=>{
        const off=vendorOffers[v.id];
        const itemCards=currentPR.items.map(item=>{
            const o=off.items[item.id];
            if(!o) return `<div style="background:#fff;border:1px solid #e5e7eb;border-radius:8px;padding:10px;margin-bottom:8px">
                <div style="font-size:12.5px;font-weight:600;color:#111827;margin-bottom:6px">${item.name}</div>
                <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:6px;padding:6px 10px;font-size:12px;color:#b91c1c;font-weight:600;text-align:center">❌ NOT OFFERED</div>
            </div>`;
            const isMatch=o.qty_offered>=item.quantity;
            const isBest=isBestPrice(v.id,item.id);
            const isSelected=selections[item.id]?.vendor_id===v.id;
            const qtyTag=isMatch
                ? `<span style="padding:2px 7px;border-radius:4px;background:#dcfce7;color:#15803d;font-size:10.5px;font-weight:700">MATCH</span>`
                : `<span style="padding:2px 7px;border-radius:4px;background:#fee2e2;color:#b91c1c;font-size:10.5px;font-weight:600">INSUFFICIENT (Need ${item.quantity-o.qty_offered} more)</span>`;
            const priceTag=isBest?`<span style="padding:2px 7px;border-radius:4px;background:#fef9c3;color:#92400e;font-size:10.5px;font-weight:700;margin-left:4px">BEST PRICE</span>`:'';
            return `<div style="background:#fff;border:2px solid ${isSelected?'#3b5bdb':'#e5e7eb'};border-radius:8px;padding:10px;margin-bottom:8px;cursor:pointer;transition:border-color .15s"
                onclick="toggleSelect('${v.id}','${item.id}','${item.name}',${o.unit_price},${o.qty_offered},'${item.unit}')">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px">
                    <div style="font-size:12.5px;font-weight:600;color:#111827">${item.name}</div>
                    <input type="checkbox" ${isSelected?'checked':''} onclick="event.stopPropagation();toggleSelect('${v.id}','${item.id}','${item.name}',${o.unit_price},${o.qty_offered},'${item.unit}')" style="width:15px;height:15px;accent-color:#3b5bdb">
                </div>
                <div style="font-size:11.5px;color:#374151;display:grid;grid-template-columns:auto 1fr;gap:2px 10px">
                    <span style="color:#9ca3af">Qty Offer</span><span>${o.qty_offered} / ${item.quantity} ${qtyTag}</span>
                    <span style="color:#9ca3af">Unit</span><span>${item.unit}</span>
                    <span style="color:#9ca3af">Unit Price</span><span style="font-weight:600">${fmt(o.unit_price)}${priceTag}</span>
                    <span style="color:#9ca3af">Notes</span><span style="color:#6b7280">${o.notes||'Add note...'}</span>
                    <span style="color:#9ca3af">Subtotal</span><span style="font-weight:700">${fmt(o.subtotal)}</span>
                    <span style="color:#9ca3af">Warranty</span><span>${o.warranty}</span>
                </div>
            </div>`;
        }).join('');
        const totalQ=currentPR.items.reduce((s,i)=>{const o=off.items[i.id];return s+(o?o.qty_offered:0);},0);
        return `<div style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:12px;overflow:hidden">
            <div style="padding:12px 14px;border-bottom:1px solid #e5e7eb;background:#fff">
                <div style="font-size:13.5px;font-weight:700;color:#111827">${v.name}</div>
                <div style="font-size:11.5px;color:#9ca3af;margin-top:2px">${v.location||''}${off.lead_days?' • Lead: '+off.lead_days+' days':''}</div>
            </div>
            <div style="padding:10px 12px;max-height:620px;overflow-y:auto">${itemCards}</div>
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
 
function toggleSelect(vendorId,itemId,itemName,price,qty,unit){
    if(selections[itemId]?.vendor_id===vendorId){
        delete selections[itemId];
    } else {
        selections[itemId]={vendor_id:vendorId,item_name:itemName,unit_price:price,quantity:qty,unit,notes:'Selected'};
    }
    renderRequirementsTable();
    renderVendorCards();
    updateCounts();
}
 
function updateCounts(){
    const sel=Object.keys(selections).length;
    const tot=currentPR?currentPR.items.length:0;
    document.getElementById('sel-count').textContent=sel;
    document.getElementById('footer-sel').textContent=sel;
    const btn=document.getElementById('show-result-btn');
    if(sel>=tot&&tot>0){btn.style.opacity='1';btn.style.pointerEvents='auto';}
    else{btn.style.opacity='.4';btn.style.pointerEvents='none';}
}
 
function updateVendorTotals(){
    serverVendors.forEach(v=>{
        let t=0;
        currentPR.items.forEach(item=>{const o=vendorOffers[v.id]?.items[item.id];if(o)t+=o.subtotal;});
        const el=document.getElementById('vendor-total-'+v.id);
        if(el)el.textContent=fmt(t);
    });
}
 
/* ─── Show result ────────────────────────────────────────────────────────── */
function showSelectionResult(){
    if(!currentPR)return;
    document.getElementById('selection-workspace').style.display='none';
    document.getElementById('result-workspace').style.display='block';
 
    document.getElementById('res-sel').textContent=Object.keys(selections).length;
    document.getElementById('res-total').textContent=currentPR.items.length;
    document.getElementById('res-pr-label').textContent='Summary Selection for '+currentPR.document_number;
    document.getElementById('sum-pr-number').textContent=currentPR.document_number;
    document.getElementById('sum-sub-date').textContent=new Date().toLocaleDateString('id-ID');
    document.getElementById('sum-items-ready').textContent=Object.keys(selections).length+' Items ready to process';
 
    /* Vendor total tabs */
    const byVendor={};
    Object.entries(selections).forEach(([itemId,sel])=>{
        const v=serverVendors.find(x=>x.id==sel.vendor_id)||{name:sel.vendor_id};
        if(!byVendor[sel.vendor_id]){byVendor[sel.vendor_id]={name:v.name,total:0};}
        byVendor[sel.vendor_id].total+=sel.unit_price*sel.quantity;
    });
    const tabs=Object.values(byVendor).map(v=>`<div style="padding:10px 18px;font-size:12.5px;font-weight:600;color:#374151;border-bottom:2px solid transparent">Total Quote&nbsp;&nbsp;<span style="font-weight:800">${fmt(v.total)}</span></div>`).join('');
    document.getElementById('vendor-total-tabs').innerHTML=tabs;
 
    /* Selected Items table */
    let grandTotal=0;
    const itemRows=currentPR.items.map((item,i)=>{
        const s=selections[item.id];
        if(!s)return '';
        const v=serverVendors.find(x=>x.id==s.vendor_id)||{name:s.vendor_id};
        const total=s.unit_price*s.quantity;
        grandTotal+=total;
        return `<tr style="border-bottom:1px solid #f3f4f6">
            <td style="padding:9px 14px;color:#6b7280">${i+1}</td>
            <td style="padding:9px 14px;font-family:'Courier New',monospace;font-size:11.5px;color:#3b5bdb;font-weight:600">${item.item_code||'—'}</td>
            <td style="padding:9px 14px;font-weight:500;color:#111827">${item.name}</td>
            <td style="padding:9px 14px;font-size:11.5px;color:#6b7280">${item.specification||'—'}</td>
            <td style="padding:9px 14px">${s.quantity}</td>
            <td style="padding:9px 14px">${s.unit}</td>
            <td style="padding:9px 14px;text-align:right">${Number(s.unit_price).toLocaleString('id-ID')}</td>
            <td style="padding:9px 14px;text-align:right;font-weight:600">${Number(total).toLocaleString('id-ID')}</td>
            <td style="padding:9px 14px"><span style="padding:2px 8px;border-radius:6px;background:#dbeafe;color:#1d4ed8;font-size:11.5px;font-weight:600">${v.name}</span></td>
            <td style="padding:9px 14px;font-size:11.5px;color:#6b7280">Selected</td>
        </tr>`;
    }).join('');
    document.getElementById('selected-items-tbody').innerHTML=itemRows||'<tr><td colspan="10" style="padding:16px;text-align:center;color:#9ca3af">No items selected</td></tr>';
    document.getElementById('grand-total-cell').textContent=fmt(grandTotal);
 
    /* Vendor summary cards */
    const vSummaries={};
    Object.entries(selections).forEach(([itemId,s])=>{
        const v=serverVendors.find(x=>x.id==s.vendor_id)||{name:s.vendor_id};
        const item=currentPR.items.find(x=>x.id==itemId)||{name:'?'};
        if(!vSummaries[s.vendor_id]){vSummaries[s.vendor_id]={name:v.name,items:[],total:0};}
        const sub=s.unit_price*s.quantity;
        vSummaries[s.vendor_id].items.push({name:item.name,qty:s.quantity,unit:s.unit,price:s.unit_price,sub});
        vSummaries[s.vendor_id].total+=sub;
    });
    document.getElementById('vendor-summary-cards').innerHTML=Object.values(vSummaries).map(vs=>`
        <div style="background:#fff;border:1px solid #e5e7eb;border-radius:10px;padding:14px;min-width:220px;flex:1">
            <div style="font-size:13px;font-weight:700;color:#111827;margin-bottom:2px">${vs.name}</div>
            <div style="font-size:11.5px;color:#9ca3af;margin-bottom:10px">${vs.items.length} selected item(s)</div>
            ${vs.items.map(it=>`<div style="margin-bottom:8px">
                <div style="font-size:12.5px;font-weight:600;color:#111827">${it.name} <span style="float:right;font-weight:700">${fmt(it.sub)}</span></div>
                <div style="font-size:11.5px;color:#9ca3af">${it.qty} ${it.unit} × Rp ${Number(it.price).toLocaleString('id-ID')}</div>
            </div>`).join('')}
            <div style="border-top:1px solid #e5e7eb;margin-top:10px;padding-top:10px;display:flex;justify-content:space-between">
                <span style="font-size:12px;font-weight:600;color:#6b7280">Vendor Total</span>
                <span style="font-size:13px;font-weight:800;color:#111827">${fmt(vs.total)}</span>
            </div>
        </div>
    `).join('');
}
 
/* ─── Submit modal ───────────────────────────────────────────────────────── */
function openSubmitModal(){document.getElementById('submit-modal').style.display='flex';}
function closeSubmitModal(){document.getElementById('submit-modal').style.display='none';}
 
function submitToServer(){
    const notes=document.getElementById('submit-notes').value.trim();
    document.getElementById('sum-notes').textContent=notes||'—';
    const payload={
        pr_id:currentPR.id,
        selection_notes:notes,
        selections:Object.entries(selections).map(([itemId,s])=>({
            vendor_id:s.vendor_id,
            item_id:itemId,
            unit_price:s.unit_price,
            quantity:s.quantity,
            notes:s.notes,
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
        document.getElementById('popup-notes').textContent=data.notes||notes||'—';
        document.getElementById('success-popup').style.display='flex';
    }).catch(()=>{
        /* fallback: show success anyway in dev */
        closeSubmitModal();
        document.getElementById('popup-pr').textContent=currentPR.document_number;
        document.getElementById('popup-notes').textContent=notes||'—';
        document.getElementById('success-popup').style.display='flex';
    });
}
 
function closeSuccess(){
    document.getElementById('success-popup').style.display='none';
    backToStep1();
}
 
/* ─── Overlay close ──────────────────────────────────────────────────────── */
['submit-modal','success-popup'].forEach(id=>{
    document.getElementById(id).addEventListener('click',function(e){if(e.target===this)this.style.display='none';});
});
</script>
@endsection