@extends('layouts.app')
@php $pageTitle = 'Vendor Selection'; @endphp
@section('content')
 
<style>
/* Base Styles */
h1 { font-size:20px;font-weight:700;color:#111827;margin:0 0 3px }
.desc { font-size:12.5px;color:#6b7280;margin:0 }
.card-box { background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:20px;margin-bottom:20px }
.btn-primary { display:inline-flex;align-items:center;gap:6px;padding:9px 18px;background:#3b5bdb;color:#fff;border-radius:8px;font-size:13px;font-weight:600;border:none;cursor:pointer;white-space:nowrap;transition:background .2s }
.btn-primary:hover { background:#3451c7 }
.btn-outline { padding:6px 14px;border:1px solid #d1d5db;border-radius:7px;background:#fff;font-size:12.5px;font-weight:600;color:#374151;cursor:pointer }

/* Table Styles */
.req-table { width:100%;border-collapse:collapse;font-size:12.5px }
.req-table th { padding:8px 16px;text-align:left;font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase;background:#f9fafb }
.req-table td { padding:11px 16px;border-bottom:1px solid #f3f4f6 }

/* Service Hierarchy Styles in Tables */
.tr-service td { background:#f3f4f6; font-weight:700; color:#111827; border-bottom:2px solid #e5e7eb; padding:8px 16px; font-size:13px; }
.tr-job td { background:#f9fafb; font-weight:600; color:#374151; padding:8px 16px 8px 30px; font-size:12px; border-bottom:1px dashed #e5e7eb; }
.tr-item td { padding:10px 16px 10px 45px; } /* Indented items */

/* Service Hierarchy Styles in Vendor Cards */
.vc-svc-header { background:#e5e7eb; padding:8px 10px; font-size:12.5px; font-weight:700; color:#111827; display:flex; align-items:center; gap:8px; border-radius:6px; margin-bottom:8px; }
.vc-job-header { background:#f3f4f6; padding:6px 10px; font-size:11.5px; font-weight:600; color:#374151; display:flex; align-items:center; gap:8px; border-radius:6px; margin:4px 0 4px 10px; }
.vc-item-box { margin-left: 20px; }
</style>

<div style="margin-bottom:20px">
    <h1>Vendor Selection</h1>
    <p class="desc">Select vendor. Divide the quantity to several vendors if stock is insufficient.</p>
</div>
 
{{-- STEP 1: SELECT PR/SR --}}
<div class="card-box" id="step1-card">
    <div style="font-size:13.5px;font-weight:700;color:#111827;margin-bottom:14px">Select Request Number</div>
    <div style="display:flex;gap:10px;align-items:center">
        <div style="flex:1;position:relative">
            <select id="pr-select" onchange="loadPR(this.value)"
                style="width:100%;padding:9px 32px 9px 12px;border:1px solid #d1d5db;border-radius:8px;font-size:13px;color:#374151;background:#fff;appearance:none;cursor:pointer;font-family:inherit">
                <option value="">— Select PR/SR number to view vendor offers —</option>
                @foreach($prs as $pr)
                <option value="{{ $pr->id }}">{{ $pr->document_number }} | {{ $pr->title }}</option>
                @endforeach
                </select>
            <svg style="position:absolute;right:10px;top:50%;transform:translateY(-50%);pointer-events:none;color:#6b7280" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 9l6 6 6-6" stroke-linecap="round"/></svg>
        </div>
        <button class="btn-primary" onclick="loadPR(document.getElementById('pr-select').value)">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35" stroke-linecap="round"/></svg>
            Show Vendors
        </button>
    </div>
</div>
 
{{-- STEP 2+: Selection workspace --}}
<div id="selection-workspace" style="display:none">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;flex-wrap:wrap;gap:10px">
        <div>
            <h2 id="ws-title" style="font-size:16px;font-weight:700;color:#111827;margin:0"></h2>
            <p id="ws-sub" style="font-size:12px;color:#6b7280;margin:2px 0 0"></p>
        </div>
        <div style="display:flex;align-items:center;gap:10px">
            <span id="ws-status-badge" style="display:inline-flex;align-items:center;gap:5px;padding:4px 10px;border-radius:999px;background:#fff7ed;font-size:12px;font-weight:600;color:#c2410c">
                <span style="width:6px;height:6px;border-radius:50%;background:#f97316"></span>Awaiting Selection
            </span>
            <button class="btn-outline" onclick="backToStep1()">← Back</button>
        </div>
    </div>
 
    {{-- Requirements table --}}
    <div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;margin-bottom:14px;overflow:hidden;">
        <div style="display:flex;align-items:center;justify-content:space-between;padding:14px 18px;border-bottom:1px solid #f3f4f6">
            <div>
                <div style="font-size:13.5px;font-weight:700;color:#111827">Item / Service Requirements</div>
                <div id="ws-item-count" style="font-size:11.5px;color:#6b7280;margin-top:1px"></div>
            </div>
            <div style="font-size:12px;color:#6b7280">Items Fulfilled: <span id="sel-count" style="font-weight:700;color:#111827">0</span> of <span id="sel-total" style="font-weight:700;color:#111827">0</span></div>
        </div>
        <div style="overflow-x:auto">
            <table class="req-table">
                <thead>
                    <tr>
                        <th style="width:50px;">NO</th>
                        <th style="width:120px;">ITEM ID</th>
                        <th>DESCRIPTION / ITEM NAME</th>
                        <th style="text-align:right;width:120px;">TARGET QTY</th>
                        <th style="width:160px;">STATUS PEMENUHAN</th>
                    </tr>
                </thead>
                <tbody id="items-requirement-tbody"></tbody>
            </table>
        </div>
    </div>
 
    {{-- Vendor cards grid --}}
    <div id="vendor-cards-grid" style="display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-bottom:14px"></div>
 
    {{-- Footer bar --}}
    <div style="background:#fff;border:1px solid #e5e7eb;border-radius:10px;padding:14px 18px;display:flex;align-items:center;justify-content:space-between">
        <div>
            <div style="font-size:12.5px;font-weight:600;color:#111827">Target Items fulfilled: <span id="footer-sel">0</span> / <span id="footer-total">0</span></div>
            <div style="font-size:11.5px;color:#9ca3af;margin-top:1px">Sistem akan memperingatkan jika Anda submit sebelum semua item/quantity terpenuhi</div>
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
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px">
        <div>
            <div id="res-pr-label" style="font-size:14px;font-weight:700;color:#111827;margin-top:3px"></div>
            <div style="font-size:12px;color:#6b7280;margin-top:2px">Review final split PO Anda sebelum diproses oleh Purchasing</div>
        </div>
        <button onclick="document.getElementById('selection-workspace').style.display='block'; document.getElementById('result-workspace').style.display='none';" class="btn-outline">
            ← Edit Selection
        </button>
    </div>
 
    <div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;margin-bottom:14px;overflow:hidden;">
        <div style="padding:14px 18px;border-bottom:1px solid #f3f4f6;font-size:13.5px;font-weight:700;color:#111827">Selected Items</div>
        <div style="overflow-x:auto">
            <table class="req-table">
                <thead><tr>
                    <th>NO</th><th>ITEM NAME</th><th>VENDOR</th><th style="text-align:right">BUY QTY</th><th>UNIT</th><th style="text-align:right">PRICE (RP)</th><th style="text-align:right">SUBTOTAL (RP)</th>
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
 
    <div style="margin-bottom:14px">
        <div style="font-size:11.5px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.07em;margin-bottom:10px">Total per Vendor</div>
        <div id="vendor-summary-cards" style="display:flex;gap:12px;flex-wrap:wrap"></div>
    </div>
 
    <div style="display:flex;justify-content:flex-end;margin-top:20px">
        <button onclick="openSubmitModal()" style="display:inline-flex;align-items:center;gap:6px;padding:12px 24px;background:#16a34a;color:#fff;border-radius:8px;font-size:14px;font-weight:700;border:none;cursor:pointer;box-shadow:0 4px 6px -1px rgba(22,163,74,.2)">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg> Confirm &amp; Submit
        </button>
    </div>
</div>

<div id="warning-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:400;align-items:center;justify-content:center;padding:20px;backdrop-filter:blur(2px)"><div style="background:#fff;border-radius:12px;width:100%;max-width:440px;box-shadow:0 10px 40px rgba(0,0,0,.2);overflow:hidden"><div style="background:#fef2f2;padding:20px;border-bottom:1px solid #fee2e2;display:flex;align-items:center;gap:14px"><div style="width:44px;height:44px;background:#fee2e2;border-radius:50%;display:flex;align-items:center;justify-content:center;color:#ef4444;flex-shrink:0"><svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg></div><div><div style="font-size:16px;font-weight:700;color:#991b1b;line-height:1.2">Peringatan Kuantitas</div><div style="font-size:12.5px;color:#b91c1c;margin-top:2px">Target Qty belum sepenuhnya terpenuhi</div></div></div><div style="padding:22px;font-size:13.5px;color:#374151;line-height:1.6">Masih ada item yang kuantitasnya <strong>BELUM TERPENUHI</strong>.<br>Apakah Anda yakin ingin mengabaikannya dan melanjutkan?</div><div style="padding:16px 22px;border-top:1px solid #f3f4f6;background:#f9fafb;display:flex;justify-content:flex-end;gap:10px"><button onclick="closeWarningModal()" class="btn-outline">Batalkan</button><button onclick="forceShowSelectionResult()" style="padding:9px 18px;background:#ef4444;color:#fff;border-radius:8px;font-size:13px;font-weight:600;border:none;cursor:pointer;">Ya, Lanjutkan</button></div></div></div>
<div id="submit-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:200;align-items:center;justify-content:center;padding:20px"><div style="background:#fff;border-radius:12px;width:100%;max-width:440px;"><div style="padding:18px 20px;border-bottom:1px solid #f3f4f6;display:flex;align-items:flex-start;justify-content:space-between"><div><div style="font-size:14px;font-weight:700;color:#111827">Submission Notes</div></div><button onclick="closeSubmitModal()" style="background:none;border:none;cursor:pointer;color:#9ca3af;padding:4px">✕</button></div><div style="padding:18px 20px"><textarea id="submit-notes" rows="4" placeholder="Catatan untuk tim Purchasing..." style="width:100%;padding:9px 12px;border:1px solid #d1d5db;border-radius:8px;font-size:13px;font-family:inherit;resize:vertical;outline:none"></textarea></div><div style="padding:14px 20px;border-top:1px solid #f3f4f6;display:flex;justify-content:flex-end;gap:10px"><button onclick="closeSubmitModal()" class="btn-outline">Cancel</button><button onclick="submitToServer()" style="padding:7px 18px;background:#16a34a;color:#fff;border-radius:7px;font-size:12.5px;font-weight:600;border:none;cursor:pointer;">Final Submit</button></div></div></div>
<div id="success-popup" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:300;align-items:center;justify-content:center;padding:20px"><div style="background:#fff;border-radius:12px;padding:32px;width:100%;max-width:400px;text-align:center;"><div style="font-size:22px;font-weight:700;color:#16a34a;margin-bottom:12px">Success!</div><div style="font-size:13px;color:#374151;margin-bottom:4px">PR/SR: <span id="popup-pr" style="font-weight:700"></span></div><button onclick="closeSuccess()" style="margin-top:20px;padding:8px 24px;border:1px solid #d1d5db;border-radius:8px;background:#fff;font-size:13px;font-weight:600;cursor:pointer">Close</button></div></div>
<script>
const serverPRs = @json($prs->load('items')->keyBy('id'));
const serverVendors = @json($vendors);

/* --- MOCK DATA UNTUK SERVICE (DIINJEKSI OTOMATIS) --- */
const mockServiceRequest = {
    id: 'SR-MOCK-999',
    document_number: 'SR-2026-999',
    title: 'Setup & Renovasi Ruang IT',
    type: 'service', // Penanda mode Service
    services: [
        {
            id: 'SVC-1', service_name: 'Pekerjaan Sipil & Interior',
            jobs: [
                {
                    id: 'JOB-1', description: 'Pengecatan Ruangan & Partisi',
                    items: [
                        { id: 'ITM-S1', item_name: 'Cat Putih 25kg', quantity: 4, unit: 'Pail' },
                        { id: 'ITM-S2', item_name: 'Jasa Pengecatan (Borongan)', quantity: 1, unit: 'Lot' }
                    ]
                }
            ]
        },
        {
            id: 'SVC-2', service_name: 'Instalasi Jaringan Listrik & Data',
            jobs: [
                {
                    id: 'JOB-2', description: 'Penarikan Kabel LAN',
                    items: [
                        { id: 'ITM-S3', item_name: 'Kabel UTP Cat6 Supreme', quantity: 2, unit: 'Roll' },
                        { id: 'ITM-S4', item_name: 'RJ45 Connector', quantity: 50, unit: 'Pcs' }
                    ]
                },
                {
                    id: 'JOB-3', description: 'Instalasi Access Point',
                    items: [
                        { id: 'ITM-S5', item_name: 'Router Access Point Aruba', quantity: 3, unit: 'Unit' },
                        { id: 'ITM-S6', item_name: 'Jasa Setting Jaringan', quantity: 1, unit: 'Jasa' }
                    ]
                }
            ]
        }
    ]
};
// Flatten items agar logika pencarian ID tetap kompatibel
mockServiceRequest.items = [];
mockServiceRequest.services.forEach(s => s.jobs.forEach(j => j.items.forEach(i => mockServiceRequest.items.push(i))));
serverPRs[mockServiceRequest.id] = mockServiceRequest;

// Auto-inject mock option to HTML dropdown
document.addEventListener('DOMContentLoaded', () => {
    const select = document.getElementById('pr-select');
    select.innerHTML += `<option value="${mockServiceRequest.id}" style="color:#1d4ed8; font-weight:bold;">🛠️ [DUMMY SERVICE] ${mockServiceRequest.document_number} | ${mockServiceRequest.title}</option>`;
});

/* --- CORE LOGIC --- */
let currentPR = null;
let selections = {}; 
let vendorOffers = {};

function fmt(n){return 'Rp '+Number(n).toLocaleString('id-ID');}

function mockOffers(prItems, vendors) {
    const offers = {};
    vendors.forEach(v => {
        offers[v.id] = { vendor: v, lead_days: Math.floor(Math.random()*8)+2, items: {} };
        prItems.forEach(item => {
            const offered = Math.random()>0.15; // 85% chance vendor offers it
            if (!offered) return;
            const qtyOff = Math.random()>0.4 ? item.quantity : Math.floor(Math.random() * (item.quantity - 1)) + 1;
            const basePrice = Math.floor(Math.random()*500+50)*1000;
            offers[v.id].items[item.id] = { qty_offered: qtyOff, unit_price: basePrice };
        });
    });
    return offers;
}

function loadPR(prId) {
    if (!prId) return;
    currentPR = serverPRs[prId];
    if (!currentPR) return;
    selections = {};
    // Gunakan array items rata (flattened) untuk meng-generate tawaran vendor
    vendorOffers = mockOffers(currentPR.items, serverVendors);
 
    document.getElementById('selection-workspace').style.display='block';
    document.getElementById('result-workspace').style.display='none';
    document.getElementById('ws-title').textContent='Vendor Selection: '+currentPR.document_number;
    document.getElementById('ws-sub').textContent=currentPR.document_number+' | '+currentPR.title;
    document.getElementById('ws-item-count').textContent=currentPR.items.length+' items/services required';
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
/* =========================================================
   GANTIKAN SCRIPT INI DARI function getItemStatus(itemId) 
   HINGGA SEBELUM TAG 
========================================================= */

function getItemStatus(itemId) {
    const item = currentPR.items.find(i => i.id == itemId);
    let totalSel = 0;
    for (let key in selections) { if (selections[key].item_id == itemId) totalSel += selections[key].quantity; }

    if (totalSel === 0) return ['Pending','#fff7ed','#c2410c','#f97316'];
    if (totalSel < item.quantity) return [`Partial (${totalSel}/${item.quantity})`,'#fef9c3','#854d0e','#eab308'];
    if (totalSel > item.quantity) return [`Over (${totalSel}/${item.quantity})`,'#dbeafe','#1d4ed8','#3b82f6'];
    return ['Full Match','#f0fdf4','#15803d','#22c55e'];
}

function getRowBg(label) {
    if (label === 'Full Match') return '#f0fdf4';
    if (label.startsWith('Partial')) return '#fefce8';
    if (label.startsWith('Over')) return '#eff6ff';
    if (label !== 'Pending') return '#fff5f5';
    return '#fff';
}

function renderRequirementsTable(){
    const tbody = document.getElementById('items-requirement-tbody');
    
    // Jika tipe SERVICE, render secara hirarki
    if (currentPR.type === 'service') {
        let html = '';
        currentPR.services.forEach((svc, sIdx) => {
            html += `<tr class="tr-service"><td colspan="5">📁 Service ${sIdx+1}: ${svc.service_name}</td></tr>`;
            svc.jobs.forEach(job => {
                html += `<tr class="tr-job"><td colspan="5">↳ 🛠️ Scope: ${job.description}</td></tr>`;
                job.items.forEach(item => {
                    const [label,bg,tc,dot] = getItemStatus(item.id);
                    html += `<tr class="tr-item" style="background:${getRowBg(label)}">
                        <td style="color:#6b7280">-</td>
                        <td style="font-family:monospace;font-size:11.5px;color:#3b5bdb">${item.id}</td>
                        <td style="font-weight:500;color:#111827">${item.item_name}</td>
                        <td style="font-weight:600;text-align:right">${item.quantity} ${item.unit}</td>
                        <td><span style="display:inline-flex;align-items:center;gap:4px;padding:3px 9px;border-radius:999px;background:${bg};font-size:11px;font-weight:600;color:${tc}"><span style="width:5px;height:5px;border-radius:50%;background:${dot}"></span>${label}</span></td>
                    </tr>`;
                });
            });
        });
        tbody.innerHTML = html;
    } else {
        // Mode GOODS biasa
        tbody.innerHTML = currentPR.items.map((item,i)=>{
            const [label,bg,tc,dot] = getItemStatus(item.id);
            return `<tr style="border-bottom:1px solid #f3f4f6;background:${getRowBg(label)}">
                <td style="color:#6b7280">${i+1}</td>
                <td style="font-family:monospace;font-size:11.5px;color:#3b5bdb;font-weight:600">${item.item_id||item.id}</td>
                <td style="font-weight:500;color:#111827">${item.item_name}</td>
                <td style="font-weight:600;text-align:right">${item.quantity} ${item.unit}</td>
                <td><span style="display:inline-flex;align-items:center;gap:4px;padding:3px 9px;border-radius:999px;background:${bg};font-size:11.5px;font-weight:600;color:${tc}"><span style="width:5px;height:5px;border-radius:50%;background:${dot}"></span>${label}</span></td>
            </tr>`;
        }).join('');
    }
}

/* --- LOGIKA CHECKBOX HIERARKI (MEMPERBAIKI BUG SHORTCUT) --- */
function processJobItemsSelection(vId, job, isChecked) {
    job.items.forEach(item => {
        const selKey = `${vId}_${item.id}`;
        const isCurrentlySelected = !!selections[selKey];
        const offer = vendorOffers[vId].items[item.id];

        // Hanya diproses jika vendor memang menawarkan item ini
        if (offer) {
            if (isChecked && !isCurrentlySelected) {
                // Centang & set full qty
                toggleSelect(vId, item.id, true);
            } else if (!isChecked && isCurrentlySelected) {
                // Hapus centang
                toggleSelect(vId, item.id, true);
            } else if (isChecked && isCurrentlySelected) {
                // Jika sudah tercentang sebagian, shortcut ini memaksimalkan kuantitasnya
                const prItem = currentPR.items.find(i => i.id == item.id);
                let qtyAlreadySelected = 0;
                for(let key in selections) { if (key !== selKey && selections[key].item_id == item.id) qtyAlreadySelected += selections[key].quantity; }
                let remainingNeed = prItem.quantity - qtyAlreadySelected;
                let maxBuy = Math.min(Math.max(1, remainingNeed), offer.qty_offered);
                
                selections[selKey].quantity = maxBuy;
                selections[selKey].subtotal = maxBuy * offer.unit_price;
            }
        }
    });
}

function toggleSvcCheckbox(vId, svcId, isChecked) {
    const svc = currentPR.services.find(s => s.id === svcId);
    if(svc) {
        svc.jobs.forEach(j => processJobItemsSelection(vId, j, isChecked));
    }
    // WAJIB MERENDER ULANG UI SETELAH LOOPING SELESAI
    renderRequirementsTable();
    renderVendorCards();
    updateCounts();
}

function toggleJobCheckbox(vId, jobId, isChecked) {
    let job = null;
    currentPR.services.forEach(s => { const f = s.jobs.find(j => j.id === jobId); if(f) job = f; });
    if(job) {
        processJobItemsSelection(vId, job, isChecked);
    }
    // WAJIB MERENDER ULANG UI SETELAH LOOPING SELESAI
    renderRequirementsTable();
    renderVendorCards();
    updateCounts();
}

function renderVendorCards(){
    const grid = document.getElementById('vendor-cards-grid');
    grid.innerHTML = serverVendors.map(v => {
        const vName = v.vendor_name || v.name || 'Vendor';
        const off = vendorOffers[v.id];
        
        let contentHtml = '';
        if (currentPR.type === 'service') {
            currentPR.services.forEach(svc => {
                // Kalkulasi UI Status untuk Checkbox Service (Two-Way Binding)
                const svcItems = svc.jobs.flatMap(j => j.items);
                const offeredSvcItems = svcItems.filter(i => vendorOffers[v.id].items[i.id]);
                const isSvcChecked = offeredSvcItems.length > 0 && offeredSvcItems.every(i => selections[`${v.id}_${i.id}`]);

                contentHtml += `<div class="vc-svc-header"><input type="checkbox" ${isSvcChecked ? 'checked' : ''} onchange="toggleSvcCheckbox(${v.id}, '${svc.id}', this.checked)"> ${svc.service_name}</div>`;

                svc.jobs.forEach(job => {
                    // Kalkulasi UI Status untuk Checkbox Job (Two-Way Binding)
                    const offeredJobItems = job.items.filter(i => vendorOffers[v.id].items[i.id]);
                    const isJobChecked = offeredJobItems.length > 0 && offeredJobItems.every(i => selections[`${v.id}_${i.id}`]);

                    contentHtml += `<div class="vc-job-header"><input type="checkbox" ${isJobChecked ? 'checked' : ''} onchange="toggleJobCheckbox(${v.id}, '${job.id}', this.checked)"> ${job.description}</div><div class="vc-item-box">`;

                    job.items.forEach(item => { contentHtml += renderItemCard(v, item, off); });
                    contentHtml += `</div>`;
                });
            });
        } else {
            contentHtml = currentPR.items.map(item => renderItemCard(v, item, off)).join('');
        }
        
        return `<div style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:12px;overflow:hidden">
            <div style="padding:12px 14px;border-bottom:1px solid #e5e7eb;background:#fff">
                <div style="font-size:13.5px;font-weight:700;color:#111827">${vName}</div>
            </div>
            <div style="padding:10px;max-height:650px;overflow-y:auto">${contentHtml}</div>
            <div style="padding:10px 14px;border-top:1px solid #e5e7eb;background:#fff;font-size:12.5px;font-weight:700;color:#111827">Total Quote <span id="vendor-total-${v.id}" style="float:right">${fmt(0)}</span></div>
        </div>`;
    }).join('');
    updateVendorTotals();
}

function renderItemCard(v, item, off) {
    const o = off.items[item.id];
    if(!o) return `<div style="background:#fff;border:1px solid #e5e7eb;border-radius:8px;padding:10px;margin-bottom:8px">
        <div style="font-size:12.5px;font-weight:700;color:#111827;margin-bottom:6px">${item.item_name}</div>
        <div style="background:#fef2f2;border-radius:6px;padding:4px;font-size:11px;color:#b91c1c;font-weight:600;text-align:center">❌ NOT OFFERED</div>
    </div>`;
    
    const selKey = `${v.id}_${item.id}`;
    const isSelected = !!selections[selKey];
    let totalSel = 0;
    for(let key in selections) { if (selections[key].item_id == item.id) totalSel += selections[key].quantity; }
    
    const isFullMatch = totalSel >= item.quantity;
    const disableSel = isFullMatch && !isSelected;

    const buyQty = isSelected ? selections[selKey].quantity : Math.min(o.qty_offered, Math.max(1, item.quantity - totalSel));
    const subtotal = isSelected ? selections[selKey].subtotal : (buyQty * o.unit_price);
    
    const stokBadge = o.qty_offered < item.quantity ? `<span style="color:#ef4444">(Tersedia ${o.qty_offered})</span>` : `<span style="color:#10b981">(Tersedia)</span>`;

    return `<div style="background:#fff;border:2px solid ${isSelected?'#3b5bdb':'#e5e7eb'};border-radius:8px;padding:10px;margin-bottom:8px;cursor:${disableSel?'not-allowed':'pointer'};opacity:${disableSel?'0.5':'1'};transition:all .15s"
        ${disableSel ? '' : `onclick="toggleSelect(${v.id}, '${item.id}')"`}>
        <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:6px">
            <div style="font-size:12.5px;font-weight:700;color:#111827;line-height:1.2">${item.item_name} <br><span style="font-size:10px;font-weight:600;margin-top:2px;display:inline-block">${stokBadge}</span></div>
            <input type="checkbox" ${isSelected?'checked':''} ${disableSel?'disabled':''} onclick="event.stopPropagation(); ${disableSel ? '' : `toggleSelect(${v.id}, '${item.id}')`}" style="width:16px;height:16px;accent-color:#3b5bdb;">
        </div>
        <div style="font-size:11px;display:grid;grid-template-columns:auto 1fr;gap:6px 10px;align-items:center">
            <span style="color:#6b7280;font-weight:500">Harga</span>
            <span style="font-weight:600;color:${isSelected?'#111827':'#6b7280'}">${fmt(o.unit_price)}</span>
            <span style="color:${isSelected?'#3b5bdb':'#6b7280'};font-weight:${isSelected?'700':'500'}">Qty Beli</span>
            <span style="display:flex;align-items:center;gap:5px">
                <input type="number" onclick="event.stopPropagation()" onchange="updateQty(${v.id}, '${item.id}', this.value)"
                    value="${buyQty}" min="1" max="${o.qty_offered}"
                    style="width:50px;padding:3px 5px;border:1px solid ${isSelected?'#3b5bdb':'#d1d5db'};border-radius:4px;font-size:11px;font-weight:600;background:${isSelected?'#eff6ff':'#f9fafb'};"
                    ${!isSelected || disableSel ? 'disabled' : ''}> <span style="color:#6b7280">/ ${item.quantity}</span>
            </span>
            <span style="color:#6b7280;font-weight:500">Subtotal</span>
            <span style="font-weight:700;color:${isSelected?'#111827':'#6b7280'}">${fmt(subtotal)}</span>
        </div>
    </div>`;
}

function toggleSelect(vId, itemId, forceRenderOnlyAtEnd = false) {
    const selKey = `${vId}_${itemId}`;
    if(selections[selKey]){
        delete selections[selKey];
    } else {
        const item = currentPR.items.find(i => i.id == itemId);
        const offer = vendorOffers[vId].items[itemId];
        if(item && offer) {
            let qtyAlreadySelected = 0;
            for(let key in selections) { if (selections[key].item_id == itemId) qtyAlreadySelected += selections[key].quantity; }
            if (qtyAlreadySelected >= item.quantity) return; // Limit penuh

            let remainingNeed = item.quantity - qtyAlreadySelected;
            let defaultBuyQty = Math.min(Math.max(1, remainingNeed), offer.qty_offered);

            selections[selKey] = { vendor_id: vId, item_id: itemId, item_name: item.item_name, unit_price: offer.unit_price, quantity: defaultBuyQty, unit: item.unit };
            selections[selKey].subtotal = defaultBuyQty * offer.unit_price;
        }
    }
    
    // Cegah lag dengan me-render hanya di akhir batch-loop saat menggunakan shortcut checkbox
    if(!forceRenderOnlyAtEnd) {
        renderRequirementsTable(); 
        renderVendorCards(); 
        updateCounts();
    }
}

function updateQty(vendorId, itemId, val) {
    const selKey = `${vendorId}_${itemId}`;
    if (selections[selKey]) {
        let q = parseInt(val) || 1;
        const max = vendorOffers[vendorId].items[itemId].qty_offered;
        if (q > max) q = max; if (q < 1) q = 1;
        selections[selKey].quantity = q;
        selections[selKey].subtotal = q * selections[selKey].unit_price;
        renderRequirementsTable(); renderVendorCards(); updateCounts();
    }
}

function updateCounts(){
    let itemsMet = 0;
    if (currentPR) {
        currentPR.items.forEach(item => {
            let t = 0;
            for(let key in selections) { if(selections[key].item_id == item.id) t += selections[key].quantity; }
            if (t >= item.quantity) itemsMet++;
        });
    }
    document.getElementById('sel-count').textContent = itemsMet; document.getElementById('footer-sel').textContent = itemsMet;
    const btn = document.getElementById('show-result-btn');
    if(Object.keys(selections).length > 0) {
        btn.style.opacity='1'; btn.style.pointerEvents='auto'; btn.style.background='#16a34a';
    } else {
        btn.style.opacity='.4'; btn.style.pointerEvents='none'; btn.style.background='#111827';
    }
}

function updateVendorTotals(){
    serverVendors.forEach(v=>{
        let t=0;
        currentPR.items.forEach(item=>{ const o = vendorOffers[v.id]?.items[item.id]; if(o) t += (o.qty_offered * o.unit_price); });
        const el=document.getElementById('vendor-total-'+v.id); if(el) el.textContent=fmt(t);
    });
}

function showSelectionResult() {
    let itemsMet = 0;
    currentPR.items.forEach(item => {
        let t = 0;
        for(let key in selections) { if(selections[key].item_id == item.id) t += selections[key].quantity; }
        if (t >= item.quantity) itemsMet++;
    });
    if (itemsMet < currentPR.items.length) { document.getElementById('warning-modal').style.display = 'flex'; } 
    else { renderResultWorkspace(); }
}

function closeWarningModal() { document.getElementById('warning-modal').style.display = 'none'; }
function forceShowSelectionResult() { closeWarningModal(); renderResultWorkspace(); }

function renderResultWorkspace() {
    document.getElementById('selection-workspace').style.display='none';
    document.getElementById('result-workspace').style.display='block';
    document.getElementById('res-pr-label').textContent='Summary Split PO untuk '+currentPR.document_number;
 
    let grandTotal=0; let rowNum=1;
    const itemsArr = Object.values(selections).map((s) => {
        const item = currentPR.items.find(x => x.id == s.item_id);
        const v = serverVendors.find(x => x.id == s.vendor_id) || {};
        const vName = v.vendor_name || v.name || s.vendor_id;
        grandTotal += s.subtotal;
        
        return `<tr style="border-bottom:1px solid #f3f4f6">
            <td style="padding:10px 14px;color:#6b7280">${rowNum++}</td>
            <td style="padding:10px 14px;font-weight:600;color:#111827">${s.item_name}</td>
            <td style="padding:10px 14px"><span style="padding:3px 8px;border-radius:6px;background:#dbeafe;color:#1d4ed8;font-size:11px;font-weight:700">${vName}</span></td>
            <td style="padding:10px 14px;text-align:right;font-weight:700;font-size:13px">${s.quantity}</td>
            <td style="padding:10px 14px">${s.unit}</td>
            <td style="padding:10px 14px;text-align:right">${Number(s.unit_price).toLocaleString('id-ID')}</td>
            <td style="padding:10px 14px;text-align:right;font-weight:700;color:#111827">${Number(s.subtotal).toLocaleString('id-ID')}</td>
        </tr>`;
    }).join('');
    
    document.getElementById('selected-items-tbody').innerHTML = itemsArr;
    document.getElementById('grand-total-cell').textContent = fmt(grandTotal);
 
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
                <div style="font-size:13.5px;font-weight:700;color:#1d4ed8">${vs.name}</div><div style="font-size:13.5px;font-weight:800;color:#111827">${fmt(vs.total)}</div>
            </div>
            ${vs.items.map(it=>`<div style="margin-bottom:8px">
                <div style="font-size:12.5px;font-weight:600;color:#374151">${it.name}</div>
                <div style="font-size:11.5px;color:#9ca3af">${it.qty} ${it.unit} × Rp ${Number(it.price).toLocaleString('id-ID')} <span style="float:right;font-weight:700;color:#4b5563">${fmt(it.sub)}</span></div>
            </div>`).join('')}
        </div>
    `).join('');
}

function openSubmitModal(){document.getElementById('submit-modal').style.display='flex';}
function closeSubmitModal(){document.getElementById('submit-modal').style.display='none';}
function closeSuccess(){document.getElementById('success-popup').style.display='none'; backToStep1();}

function submitToServer(){
    const notes=document.getElementById('submit-notes').value.trim();
    const payload={
        purchase_request_id:currentPR.id, selection_notes:notes,
        selections: Object.values(selections).map(s => ({ vendor_id: s.vendor_id, item_id: s.item_id, unit_price: s.unit_price, quantity: s.quantity, notes: s.notes })),
        _token:document.querySelector('meta[name=csrf-token]')?.content||'',
    };
    
    fetch('{{ route("vendors.store.selection") }}',{ method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':payload._token}, body:JSON.stringify(payload) })
    .then(r=>r.json()).then(data=>{
        closeSubmitModal(); document.getElementById('popup-pr').textContent=data.pr_number||currentPR.document_number; document.getElementById('success-popup').style.display='flex';
    }).catch(()=>{
        closeSubmitModal(); document.getElementById('popup-pr').textContent=currentPR.document_number; document.getElementById('success-popup').style.display='flex';
    });
}
</script>
@endsection