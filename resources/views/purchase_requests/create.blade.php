@extends('layouts.app')
@php $pageTitle = 'New Request'; @endphp
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
.flex-between  { display: flex; align-items: center; justify-content: space-between; }
.form-row      { display: grid; gap: 16px; }
.form-row-2    { grid-template-columns: 1fr 1fr; }
.form-group    { display: flex; flex-direction: column; }
.form-label    { font-size: 13px; font-weight: 600; color: var(--text); margin-bottom: 6px; }
.req           { color: var(--req-color); margin-left: 2px; }
.form-control  { width: 100%; box-sizing: border-box; padding: 8px 12px; font-size: 13.5px; border: 1px solid var(--border-strong); border-radius: 8px; background: #fff; color: var(--text); outline: none; }
.form-control:focus  { border-color: #6366f1; box-shadow: 0 0 0 3px rgb(99 102 241/.12); }
.mt-3 { margin-top: 16px; }
.mt-4 { margin-top: 24px; }
.mb-2 { margin-bottom: 8px; }
.mb-3 { margin-bottom: 16px; }
.btn { display: inline-flex; align-items: center; gap: 6px; padding: 9px 18px; border-radius: 8px; font-size: 13.5px; font-weight: 600; cursor: pointer; border: 1px solid transparent; text-decoration: none; }
.btn-primary  { background: #111827; color: #fff; }
.btn-primary:hover { background: #1f2937; }
.btn-outline  { background: #fff; color: var(--text); border-color: var(--border-strong); }
.btn-outline:hover { background: #f9fafb; }
.btn-sm       { padding: 6px 14px; font-size: 12.5px; border-radius: 7px; }

/* Custom Nested Jobs UI */
.job-container { border: 1px solid var(--border); padding: 16px; border-radius: 10px; margin-bottom: 16px; background: #fafafa; }
.job-header { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 12px; gap: 16px; }
.item-container { background: #fff; border: 1px solid var(--border); border-radius: 8px; padding: 12px; }
.item-table { width: 100%; border-collapse: collapse; font-size: 13px; }
.item-table th { text-align: left; padding: 8px; color: var(--text-muted); border-bottom: 1px solid var(--border); font-weight: 600; font-size: 11px; text-transform: uppercase; }
.item-table td { padding: 6px 4px; border-bottom: 1px solid #f3f4f6; }

/* Modal & Tabs */
.modal-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.4); z-index: 1000; align-items: center; justify-content: center; padding: 16px; }
.modal-overlay.open { display: flex; }
.modal { background: #fff; border-radius: 14px; width: 100%; max-width: 560px; display: flex; flex-direction: column; overflow: hidden; }
.modal-header { padding: 18px 20px; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; }
.modal-title  { font-size: 15px; font-weight: 700; }
.modal-desc   { font-size: 12.5px; color: var(--text-muted); }
.modal-body   { padding: 18px 20px; overflow-y: auto; max-height: 60vh; }
.modal-footer { padding: 14px 20px; border-top: 1px solid var(--border); display: flex; justify-content: flex-end; gap: 10px; }
.modal-close  { background: none; border: none; cursor: pointer; color: var(--text-muted); font-size: 18px; }
.tab-btn { padding:7px 18px;border-radius:8px;border:1px solid var(--border);font-size:13.5px;font-weight:600;cursor:pointer;background:white;color:var(--text-muted); }
.tab-btn.tab-active { background:#111827;color:white;border-color:#111827; }
</style>

<div class="page-header">
    <div class="page-title">New Request</div>
    <div class="page-desc">Fill in the form below to submit a new procurement/service request.</div>
</div>

<form action="{{ route('purchase_requests.store') }}" method="post" id="pr-form">
@csrf
<input type="hidden" name="item_type" id="item_type_field" value="goods">

<div class="card">
    <div class="card-header">
        <div class="flex-center">
            <div class="form-section-icon"><svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="9" y="2" width="6" height="4" rx="1"/><path d="M7 2H5a2 2 0 00-2 2v16a2 2 0 002 2h14a2 2 0 002-2V4a2 2 0 00-2-2h-2" stroke-linecap="round" stroke-linejoin="round"/></svg></div>
            <div>
                <div class="card-title">Request Schedule & Location</div>
                <div class="card-desc">Determine where and when the request is needed</div>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="form-row form-row-2">
            <div class="form-group"><label class="form-label">Requested Date <span class="req">*</span></label><input class="form-control" type="date" name="requested_date" required></div>
            <div class="form-group">
                <label class="form-label">Plant <span class="req">*</span></label>
                <select class="form-control" name="plant" required>
                    <option value="">— Select Plant —</option>
                    <option value="Cikarang">Cikarang</option>
                    <option value="Cibitung">Cibitung</option>
                    <option value="Gresik">Gresik</option>
                </select>
            </div>
        </div>
    </div>
</div>

<div class="card mt-4">
    <div class="card-header">
        <div class="flex-center">
            <div class="form-section-icon"><svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20 7H4a2 2 0 00-2 2v10a2 2 0 002 2h16a2 2 0 002-2V9a2 2 0 00-2-2z"/><path d="M16 7V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v2" stroke-linecap="round"/></svg></div>
            <div>
                <div class="card-title" id="section-title">Purchase Details</div>
                <div class="card-desc">Select request type and input item/service scopes</div>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div style="display:flex;gap:8px;margin-bottom:20px;">
            <button type="button" id="tab-goods" class="tab-btn tab-active" onclick="setTab('goods')">🛒 Goods (Purchase Request)</button>
            <button type="button" id="tab-service" class="tab-btn" onclick="setTab('service')">🔧 Service (Service Request)</button>
        </div>

        <div id="goods-panel">
            <div class="form-row form-row-2 mb-3">
                <div class="form-group"><label class="form-label">Document Number <span class="req">*</span></label><input class="form-control" name="document_number" value="PR-{{ now()->format('Ymd') }}-XXX" id="goods-doc" required></div>
                <div class="form-group"><label class="form-label">Title / Project Name <span class="req">*</span></label><input class="form-control" name="title" id="goods-title" placeholder="e.g. Pengadaan ATK Bulanan" required></div>
            </div>
            <div class="form-group mb-3"><label class="form-label">Department</label><input class="form-control" name="department" value="{{ Auth::user()->department ?? '' }}"></div>
            
            <div class="flex-between mb-2 mt-4">
                <label class="form-label mb-0" id="goods-count-label">0 item(s) added</label>
                <button type="button" class="btn btn-primary btn-sm" onclick="openItemModal()">+ Add Item</button>
            </div>
            <div style="border:1px solid var(--border);border-radius:10px;overflow:hidden;">
                <table class="item-table" style="margin-bottom:0;">
                    <thead style="background:#f9fafb;">
                        <tr>
                            <th style="width:40px;">NO</th>
                            <th style="width:110px;">ITEM ID</th>
                            <th>ITEM NAME</th>
                            <th style="width:70px;">QTY</th>
                            <th style="width:80px;">UNIT</th>
                            <th>SPECIFICATION</th>
                            <th>NOTES</th>
                            <th style="width:40px;"></th>
                        </tr>
                    </thead>
                    <tbody id="goods-tbody">
                        <tr><td colspan="8" style="text-align:center;padding:28px 16px;color:#9ca3af;">No items added yet. Click "+ Add Item" to begin.</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div id="service-panel" style="display:none;">
            <div class="form-group mb-4"><label class="form-label">Service Name / Project <span class="req">*</span></label><input class="form-control" name="service_name" id="svc-name" placeholder="e.g. Renovasi Atap Gudang Utama"></div>
            <div id="jobs-container"></div>
            <button type="button" class="btn btn-outline btn-sm mt-2" onclick="addJob()" style="border: 1px dashed var(--primary); color: var(--primary); width: 100%; justify-content: center; padding: 12px;">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14" stroke-linecap="round"/></svg> Add New Job / Scope
            </button>
        </div>
    </div>
</div>

<div style="display:flex;justify-content:flex-end;gap:12px;margin-top:24px;">
    <a href="{{ route('dashboard') }}" class="btn btn-outline">Cancel</a>
    <button type="submit" class="btn btn-primary">Submit Request</button>
</div>
</form>

<div class="modal-overlay" id="item-modal">
    <div class="modal">
        <div class="modal-header">
            <div><div class="modal-title">Select Item</div><div class="modal-desc">Search catalog items</div></div>
            <button class="modal-close" onclick="closeItemModal()">&times;</button>
        </div>
        <div style="padding: 16px 20px 12px; border-bottom: 1px solid var(--border); background: #fafafa;">
            <input class="form-control mb-2" id="item-search" placeholder="Search..." oninput="filterItems(this.value)">
            <button type="button" class="btn btn-outline btn-sm" onclick="openNewItemModal()" style="width:100%; justify-content:center;">+ Create New Item ID</button>
        </div>
        <div class="modal-body" style="padding-top: 12px;">
            <div id="item-list" style="display:flex;flex-direction:column;gap:4px;"></div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline" onclick="closeItemModal()">Cancel</button>
            <button type="button" class="btn btn-primary" onclick="addSelectedItem()">Add Selected</button>
        </div>
    </div>
</div>

<div class="modal-overlay" id="new-item-modal">
    <div class="modal">
        <div class="modal-header">
            <div><div class="modal-title">Create New Item</div></div>
            <button class="modal-close" onclick="closeNewItemModal()">&times;</button>
        </div>
        <div class="modal-body">
            <div class="form-row form-row-2 mb-3">
                <div class="form-group"><label class="form-label">Item Name <span class="req">*</span></label><input class="form-control" id="new-item-name"></div>
                <div class="form-group"><label class="form-label">Unit <span class="req">*</span></label>
                    <select class="form-control" id="new-item-unit"><option>Roll</option><option>Pcs</option><option>Unit</option><option>Meter</option><option>Kg</option><option>Set</option><option>Box</option></select>
                </div>
            </div>
            <div class="form-group mb-3"><label class="form-label">Specification</label><textarea class="form-control" id="new-item-spec"></textarea></div>
            <div class="form-group"><label class="form-label">Notes</label><textarea class="form-control" id="new-item-notes"></textarea></div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline" onclick="closeNewItemModal()">Cancel</button>
            <button type="button" class="btn btn-primary" onclick="saveNewItem()">Save & Add</button>
        </div>
    </div>
</div>

<style>
.item-option { padding:12px 14px;border-radius:8px;cursor:pointer;border:1px solid var(--border);transition:background .1s; }
.item-option:hover { background:#f9fafb; }
.item-option.selected { background:var(--primary-light);border-color:var(--primary); }
.item-option-name { font-size:13.5px;font-weight:600;color:var(--text); }
.item-option-desc { font-size:12px;color:var(--text-muted); }
</style>

<script>
function setTab(t) {
    document.getElementById('item_type_field').value = t;
    document.getElementById('tab-goods').classList.toggle('tab-active', t==='goods');
    document.getElementById('tab-service').classList.toggle('tab-active', t==='service');
    document.getElementById('goods-panel').style.display   = t==='goods'   ? '' : 'none';
    document.getElementById('service-panel').style.display = t==='service' ? '' : 'none';
    
    document.getElementById('goods-doc').required = (t === 'goods');
    document.getElementById('goods-title').required = (t === 'goods');
    document.getElementById('svc-name').required = (t === 'service');
}

// -- GOODS LOGIC --
const catalog = @json($existingItems);
let selectedItemId = null, addedItems = [], itemCounter = 0;

function filterItems(q) { renderItemList(q.toLowerCase()); }
function renderItemList(q='') {
    const filtered = catalog.filter(i => !q || i.name.toLowerCase().includes(q) || (i.spec && i.spec.toLowerCase().includes(q)));
    document.getElementById('item-list').innerHTML = filtered.map(i=>`
        <div class="item-option ${selectedItemId===i.id?'selected':''}" onclick="selectItem('${i.id}')">
            <div class="item-option-name">[${i.id}] ${i.name}</div>
            <div class="item-option-desc">Spec: ${i.spec || '—'} | Unit: ${i.unit}</div>
        </div>`).join('');
}
function selectItem(id){ selectedItemId=id; renderItemList(document.getElementById('item-search').value.toLowerCase()); }
function openItemModal(){ selectedItemId=null; document.getElementById('item-search').value=''; renderItemList(); document.getElementById('item-modal').classList.add('open'); }
function closeItemModal(){ document.getElementById('item-modal').classList.remove('open'); }
function addSelectedItem(){
    if(!selectedItemId){alert('Please select an item.');return;}
    const i=catalog.find(x=>x.id===selectedItemId); if(!i)return;
    addedItems.push({idx:itemCounter++, id:i.id, name:i.name, unit:i.unit, spec:i.spec, notes:i.notes, qty:1});
    renderGoodsTable(); closeItemModal();
}
function removeGoods(idx){ addedItems=addedItems.filter(i=>i.idx!==idx); renderGoodsTable(); }
function openNewItemModal(){ closeItemModal(); document.getElementById('new-item-modal').classList.add('open'); }
function closeNewItemModal(){ document.getElementById('new-item-modal').classList.remove('open'); }
function saveNewItem(){
    const name=document.getElementById('new-item-name').value.trim();
    if(!name){alert('Item name is required.');return;}
    const unit=document.getElementById('new-item-unit').value, spec=document.getElementById('new-item-spec').value.trim(), notes=document.getElementById('new-item-notes').value.trim();
    const newId='ITM-'+Math.floor(Math.random()*9000+1000);
    catalog.push({id:newId, name, unit, spec, notes});
    addedItems.push({idx:itemCounter++, id:newId, name, unit, spec, notes, qty:1});
    renderGoodsTable(); closeNewItemModal();
    ['new-item-name','new-item-spec','new-item-notes'].forEach(id=>document.getElementById(id).value='');
}
function renderGoodsTable(){
    const t=document.getElementById('goods-tbody');
    document.getElementById('goods-count-label').textContent=`${addedItems.length} item(s) added`;
    if(!addedItems.length){t.innerHTML='<tr><td colspan="8" style="text-align:center;padding:28px 16px;color:#9ca3af;">No items added yet.</td></tr>';return;}
    t.innerHTML=addedItems.map((it,i)=>`<tr>
        <td>${i+1}</td>
        <td style="font-family: monospace; font-weight: 600; color: var(--primary);">
            ${it.id}<input type="hidden" name="items[${it.idx}][item_id]" value="${it.id}">
        </td>
        <td><input class="form-control" name="items[${it.idx}][item_name]" value="${it.name}" required></td>
        <td><input type="number" class="form-control" name="items[${it.idx}][quantity]" value="${it.qty}" min="1" required></td>
        <td><input class="form-control" name="items[${it.idx}][unit]" value="${it.unit}" required></td>
        <td><input class="form-control" name="items[${it.idx}][specification]" value="${it.spec||''}"></td>
        <td><input class="form-control" name="items[${it.idx}][item_notes]" value="${it.notes||''}"></td>
        <td style="text-align:center;"><button type="button" onclick="removeGoods(${it.idx})" style="background:none;border:none;color:#ef4444;cursor:pointer;font-size:18px;">&times;</button></td>
    </tr>`).join('');
}

// -- SERVICE LOGIC --
let sJobs = [], jobIdCounter = 0, itemIdCounter = 0;
function addJob() { sJobs.push({ jId: jobIdCounter++, desc: '', items: [] }); renderServiceJobs(); }
function removeJob(jId) { sJobs = sJobs.filter(j => j.jId !== jId); renderServiceJobs(); }
function addServiceItem(jId) {
    const job = sJobs.find(j => j.jId === jId);
    if (job) { job.items.push({ iId: itemIdCounter++, name: '', qty: 1, unit: 'm2', spec: '' }); renderServiceJobs(); }
}
function removeServiceItem(jId, iId) {
    const job = sJobs.find(j => j.jId === jId);
    if (job) { job.items = job.items.filter(i => i.iId !== iId); renderServiceJobs(); }
}
function updateJobDesc(jId, val) { sJobs.find(j => j.jId === jId).desc = val; }
function updateSvcItem(jId, iId, field, val) { 
    const j = sJobs.find(j => j.jId === jId);
    if(j) { const i = j.items.find(it => it.iId === iId); if(i) i[field] = val; }
}
function renderServiceJobs() {
    const container = document.getElementById('jobs-container');
    if (!sJobs.length) { container.innerHTML = ''; return; }
    container.innerHTML = sJobs.map((job, jIdx) => `
        <div class="job-container">
            <div class="job-header">
                <div class="form-group" style="flex:1;">
                    <label class="form-label">Job Description (Scope of Work) <span class="req">*</span></label>
                    <input class="form-control" name="jobs[${jIdx}][description]" value="${job.desc}" oninput="updateJobDesc(${job.jId}, this.value)" required>
                </div>
                <button type="button" class="btn btn-outline btn-sm" style="color:#ef4444; border-color:#fca5a5;" onclick="removeJob(${job.jId})">Remove Job</button>
            </div>
            <div class="item-container">
                <div class="flex-between mb-2">
                    <label class="form-label mb-0">Items Required for this Job</label>
                    <button type="button" class="btn btn-primary btn-sm" onclick="addServiceItem(${job.jId})">+ Add Item</button>
                </div>
                <table class="item-table">
                    <thead><tr><th>Item Name <span class="req">*</span></th><th style="width:80px;">Qty <span class="req">*</span></th><th style="width:100px;">Unit <span class="req">*</span></th><th>Specification</th><th style="width:30px;"></th></tr></thead>
                    <tbody>
                        ${job.items.length === 0 ? `<tr><td colspan="5" style="text-align:center;padding:16px;color:#9ca3af;">No items in this job. Click "+ Add Item".</td></tr>` : 
                        job.items.map((it, iIdx) => `
                        <tr>
                            <td><input class="form-control" name="jobs[${jIdx}][items][${iIdx}][item_name]" value="${it.name}" oninput="updateSvcItem(${job.jId}, ${it.iId}, 'name', this.value)" required></td>
                            <td><input type="number" step="0.01" class="form-control" name="jobs[${jIdx}][items][${iIdx}][quantity]" value="${it.qty}" oninput="updateSvcItem(${job.jId}, ${it.iId}, 'qty', this.value)" required></td>
                            <td><input class="form-control" name="jobs[${jIdx}][items][${iIdx}][unit]" value="${it.unit}" oninput="updateSvcItem(${job.jId}, ${it.iId}, 'unit', this.value)" required></td>
                            <td><input class="form-control" name="jobs[${jIdx}][items][${iIdx}][specification]" value="${it.spec}" oninput="updateSvcItem(${job.jId}, ${it.iId}, 'spec', this.value)"></td>
                            <td style="text-align:center;"><button type="button" onclick="removeServiceItem(${job.jId}, ${it.iId})" style="background:none;border:none;color:#ef4444;cursor:pointer;font-size:18px;">&times;</button></td>
                        </tr>`).join('')}
                    </tbody>
                </table>
            </div>
        </div>
    `).join('');
}

document.getElementById('pr-form').addEventListener('submit', function(e) {
    const type = document.getElementById('item_type_field').value;
    if (type === 'goods' && addedItems.length === 0) { e.preventDefault(); alert('Please add at least one item.'); return; }
    if (type === 'service') {
        if (sJobs.length === 0) { e.preventDefault(); alert('Please add at least one job scope.'); return; }
        for (let j of sJobs) { if (j.items.length === 0) { e.preventDefault(); alert('Every job scope must have at least one item.'); return; } }
    }
});

['item-modal','new-item-modal'].forEach(id=>{ document.getElementById(id).addEventListener('click',function(e){if(e.target===this)this.classList.remove('open');}); });
</script>
@endsection