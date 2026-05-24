@extends('layouts.app')
@php $pageTitle = 'Vendor Selection'; @endphp

@section('content')

{{-- =====================================================================
     STEP 1: SELECT PR — shown when no rfq/pr is selected
     ===================================================================== --}}
@if(!isset($rfq) || !$rfq)

<div style="margin-bottom:24px;">
    <div style="font-size:12px;color:#9ca3af;margin-bottom:6px;">
        <a href="/dashboard" style="color:#6b7280;text-decoration:none;">Portal</a>
        <span style="margin:0 6px;">/</span>
        <span style="color:#111827;font-weight:600;">Vendor Selection</span>
    </div>
    <h1 style="font-size:22px;font-weight:700;color:#111827;margin:0 0 4px;">Vendor Selection</h1>
    <p style="font-size:13.5px;color:#6b7280;margin:0;">Select a PR, then choose items from each vendor offer.</p>
</div>

<div style="background:#fff;border:1px solid #e5e7eb;border-radius:14px;padding:22px 24px;">
    <div style="font-size:14px;font-weight:700;color:#111827;margin-bottom:16px;">Select PR Number</div>
    <div style="display:flex;gap:10px;align-items:center;">
        <div style="position:relative;flex:1;">
            <select id="pr-select"
                style="width:100%;padding:10px 36px 10px 14px;border:1px solid #d1d5db;border-radius:8px;font-size:13.5px;color:#374151;background:#fff;appearance:none;cursor:pointer;font-family:inherit;outline:none;">
                <option value="">--- Select PR number to view vendor offers ---</option>
                @foreach($rfqs ?? [] as $r)
                <option value="{{ route('vendors.select', $r) }}">
                    {{ $r->purchaseRequest->document_number }} | {{ $r->purchaseRequest->title }}
                </option>
                @endforeach
            </select>
            <svg style="position:absolute;right:12px;top:50%;transform:translateY(-50%);pointer-events:none;color:#6b7280;" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 9l6 6 6-6" stroke-linecap="round"/></svg>
        </div>
        <button onclick="goToPR()"
            style="display:inline-flex;align-items:center;gap:7px;padding:10px 20px;background:#1e3a5f;color:#fff;border:none;border-radius:8px;font-size:13.5px;font-weight:600;cursor:pointer;white-space:nowrap;font-family:inherit;">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35" stroke-linecap="round"/></svg>
            Show Vendors
        </button>
    </div>
</div>

<script>
function goToPR() {
    const val = document.getElementById('pr-select').value;
    if (val) window.location.href = val;
}
</script>

@else

{{-- =====================================================================
     STEP 2: MAIN SELECTION VIEW — PR selected, show vendor comparison
     ===================================================================== --}}

@php
    $pr               = $rfq->purchaseRequest;
    $items            = $pr->items;
    $vendorQuotations = $rfq->vendorQuotations->load('vendor');
    $allQuotations    = \App\Models\Quotation::with('quotationDetails')->where('rfq_id', $rfq->id)->get();
@endphp

{{-- PAGE HEADER --}}
<div style="margin-bottom:14px;display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:10px;">
    <div>
        <div style="font-size:12px;color:#9ca3af;margin-bottom:6px;">
            <a href="/dashboard" style="color:#6b7280;text-decoration:none;">Portal</a>
            <span style="margin:0 6px;">/</span>
            <span style="color:#111827;font-weight:600;">Vendor Selection</span>
        </div>
        <h1 style="font-size:20px;font-weight:700;color:#111827;margin:0 0 3px;">
            Vendor Selection: <span style="color:#1e3a5f;">{{ $pr->document_number }}</span>
        </h1>
        <p style="font-size:13px;color:#6b7280;margin:0;">{{ $pr->document_number }} | {{ $pr->title }}</p>
    </div>
    <div style="display:flex;align-items:center;gap:10px;">
        <span style="display:inline-flex;align-items:center;gap:6px;padding:5px 12px;border-radius:999px;background:#fef3c7;font-size:12.5px;font-weight:600;color:#92400e;">
            <span style="width:7px;height:7px;border-radius:50%;background:#f59e0b;"></span>
            Awaiting Selection
        </span>
        <a href="{{ route('pr.list') }}"
            style="display:inline-flex;align-items:center;gap:6px;padding:6px 14px;border:1px solid #d1d5db;border-radius:8px;background:#fff;font-size:13px;font-weight:600;color:#374151;text-decoration:none;">
            ← Back
        </a>
    </div>
</div>

{{-- LEGEND --}}
<div style="background:#fff;border:1px solid #e5e7eb;border-radius:10px;padding:9px 14px;margin-bottom:14px;display:flex;align-items:center;gap:6px;flex-wrap:wrap;font-size:12px;">
    <span style="font-weight:700;color:#6b7280;margin-right:2px;">Legend:</span>
    <span style="padding:2px 9px;background:#dcfce7;border-radius:4px;color:#15803d;font-weight:600;">● Perfect Match</span>
    <span style="padding:2px 9px;background:#fef9c3;border-radius:4px;color:#854d0e;font-weight:600;">Qty Less</span>
    <span style="padding:2px 9px;background:#dbeafe;border-radius:4px;color:#1d4ed8;font-weight:600;">Qty More</span>
</div>

{{-- PR ITEM REQUIREMENTS TABLE --}}
<div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;margin-bottom:14px;">
    <div style="padding:13px 20px;border-bottom:1px solid #f3f4f6;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;">
        <div>
            <div style="font-size:14px;font-weight:700;color:#111827;">PR Item Requirements</div>
            <div style="font-size:12px;color:#9ca3af;margin-top:1px;">{{ $items->count() }} items required</div>
        </div>
        <div style="display:flex;align-items:center;gap:8px;font-size:13px;font-weight:600;color:#374151;">
            Selected: <span id="selected-count" style="color:#1d4ed8;">0</span> of {{ $items->count() }}
            <div style="width:90px;height:6px;background:#e5e7eb;border-radius:999px;overflow:hidden;">
                <div id="progress-bar" style="height:100%;background:#1d4ed8;border-radius:999px;width:0%;transition:.3s;"></div>
            </div>
        </div>
    </div>
    <div style="overflow-x:auto;">
        <table style="width:100%;border-collapse:collapse;font-size:13px;">
            <thead>
                <tr style="background:#f9fafb;">
                    <th style="padding:9px 16px;text-align:left;font-size:10.5px;font-weight:700;color:#9ca3af;">NO</th>
                    <th style="padding:9px 16px;text-align:left;font-size:10.5px;font-weight:700;color:#9ca3af;">ITEM CODE</th>
                    <th style="padding:9px 16px;text-align:left;font-size:10.5px;font-weight:700;color:#9ca3af;">ITEM NAME</th>
                    <th style="padding:9px 16px;text-align:left;font-size:10.5px;font-weight:700;color:#9ca3af;">SPECIFICATION</th>
                    <th style="padding:9px 16px;text-align:right;font-size:10.5px;font-weight:700;color:#9ca3af;">REQUIRED QTY</th>
                    <th style="padding:9px 16px;text-align:left;font-size:10.5px;font-weight:700;color:#9ca3af;">UNIT</th>
                    <th style="padding:9px 16px;text-align:left;font-size:10.5px;font-weight:700;color:#9ca3af;">STATUS</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $i => $item)
                <tr id="item-row-{{ $item->id }}" style="border-bottom:1px solid #f9fafb;">
                    <td style="padding:11px 16px;color:#6b7280;">{{ $i + 1 }}</td>
                    <td style="padding:11px 16px;font-family:'Courier New',monospace;font-size:12px;font-weight:600;color:#3b5bdb;">{{ $item->item_id }}</td>
                    <td style="padding:11px 16px;font-weight:600;color:#111827;">{{ $item->item_name }}</td>
                    <td style="padding:11px 16px;font-size:12.5px;color:#6b7280;">{{ $item->specification ?? '—' }}</td>
                    <td style="padding:11px 16px;font-weight:700;color:#111827;text-align:right;">{{ $item->quantity }}</td>
                    <td style="padding:11px 16px;color:#6b7280;">{{ $item->unit }}</td>
                    <td style="padding:11px 16px;">
                        <span id="item-status-{{ $item->id }}"
                            style="display:inline-flex;align-items:center;gap:5px;padding:3px 10px;border-radius:999px;background:#fff7ed;font-size:12px;font-weight:600;color:#c2410c;">
                            <span style="width:6px;height:6px;border-radius:50%;background:#f97316;"></span>Pending
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- VENDOR CARDS --}}
@if($vendorQuotations->count() > 0)
<div style="display:grid;grid-template-columns:repeat({{ min($vendorQuotations->count(), 3) }},1fr);gap:14px;margin-bottom:80px;">

    @foreach($vendorQuotations as $vq)
    @php
        $vendor = $vq->vendor;
        $vendorQuotationRecord = $allQuotations->where('vendor_id', optional($vendor)->id)->first();
    @endphp

    <div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;overflow:hidden;">
        <div style="padding:13px 16px;border-bottom:1px solid #f3f4f6;background:#fafafa;">
            <div style="font-size:14px;font-weight:700;color:#111827;">{{ optional($vendor)->vendor_name ?? optional($vendor)->name ?? 'Unknown' }}</div>
            <div style="font-size:12px;color:#9ca3af;margin-top:2px;">
                {{ optional($vendor)->location ?? '—' }}
            </div>
        </div>

        <div style="padding:12px;display:flex;flex-direction:column;gap:10px;">
            @foreach($items as $item)
            @php
                $detail = $vendorQuotationRecord
                            ? $vendorQuotationRecord->quotationDetails->where('purchase_request_item_id', $item->id)->first()
                            : null;

                $offeredQty   = $detail?->offered_quantity  ?? null;
                $unitPrice    = $detail?->offered_price_per_item ?? null;
                $subtotal     = ($offeredQty && $unitPrice) ? $offeredQty * $unitPrice : null;
                $isNotOffered = $detail === null;

                $qtyBadge = '';
                $cardBg   = '#fff';
                if (!$isNotOffered && $offeredQty !== null) {
                    if ($offeredQty == $item->quantity) {
                        $qtyBadge = '<span style="padding:1px 6px;background:#dcfce7;border-radius:4px;font-size:11px;font-weight:700;color:#15803d;margin-left:4px;">MATCH</span>';
                    } elseif ($offeredQty < $item->quantity) {
                        $diff = $item->quantity - $offeredQty;
                        $qtyBadge = "<span style=\"padding:1px 6px;background:#ffedd5;border-radius:4px;font-size:11px;font-weight:700;color:#c2410c;margin-left:4px;\">INSUFFICIENT (-{$diff})</span>";
                        $cardBg   = '#fffbf5';
                    } else {
                        $surplus = $offeredQty - $item->quantity;
                        $qtyBadge = "<span style=\"padding:1px 6px;background:#dbeafe;border-radius:4px;font-size:11px;font-weight:700;color:#1d4ed8;margin-left:4px;\">SURPLUS (+{$surplus})</span>";
                    }
                }

                $formattedPrice    = $unitPrice    ? 'Rp ' . number_format($unitPrice, 0, ',', '.') : null;
                $formattedSubtotal = $subtotal     ? 'Rp ' . number_format($subtotal,  0, ',', '.') : null;
                $formattedQty      = $offeredQty   ? $offeredQty . ' / ' . $item->quantity : '— / ' . $item->quantity;
            @endphp

            <div class="vendor-item-card"
                 data-vq-id="{{ $vq->id }}"
                 data-vendor-id="{{ optional($vendor)->id }}"
                 data-item-id="{{ $item->id }}"
                 data-required-qty="{{ $item->quantity }}"
                 data-offered-qty="{{ $offeredQty ?? '' }}"
                 data-unit-price="{{ $unitPrice ?? '' }}"
                 data-subtotal="{{ $subtotal ?? '' }}"
                 data-vendor-name="{{ optional($vendor)->vendor_name ?? optional($vendor)->name ?? 'Unknown Vendor' }}"
                 data-item-name="{{ $item->item_name }}"
                 data-not-offered="{{ $isNotOffered ? '1' : '0' }}"
                 style="border:1px solid #e5e7eb;border-radius:10px;padding:12px 14px;position:relative;cursor:{{ $isNotOffered ? 'not-allowed' : 'pointer' }};background:{{ $cardBg }};transition:.15s;"
                 @if(!$isNotOffered)
                 onclick="toggleItemCard(this)"
                 onmouseover="if(this.dataset.selected!=='true')this.style.borderColor='#93c5fd'"
                 onmouseout="if(this.dataset.selected!=='true')this.style.borderColor='#e5e7eb'"
                 @endif>

                @if(!$isNotOffered)
                <div style="position:absolute;top:12px;right:12px;">
                    <div class="card-checkbox" style="width:17px;height:17px;border:2px solid #d1d5db;border-radius:4px;background:#fff;display:flex;align-items:center;justify-content:center;transition:.15s;"></div>
                </div>
                @endif

                <div style="font-size:13px;font-weight:700;color:{{ $isNotOffered ? '#9ca3af' : '#111827' }};margin-bottom:10px;padding-right:24px;">
                    {{ $item->item_name }}
                </div>

                @if(!$isNotOffered)
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:5px;font-size:12px;">
                    <div>
                        <span style="color:#9ca3af;font-size:11px;">Qty Offer</span><br>
                        <span style="font-weight:600;color:#111827;">{{ $formattedQty }}{!! $qtyBadge !!}</span>
                    </div>
                    <div>
                        <span style="color:#9ca3af;font-size:11px;">Unit Price</span><br>
                        <span style="font-weight:600;color:#111827;">
                            {{ $formattedPrice ?? '—' }}
                            <span class="best-price-badge" data-item="{{ $item->id }}" style="display:none;"></span>
                        </span>
                    </div>
                    <div style="grid-column: span 2;">
                        <span style="color:#9ca3af;font-size:11px;">Subtotal</span><br>
                        <span style="font-weight:600;color:#111827;">{{ $formattedSubtotal ?? '—' }}</span>
                    </div>
                </div>
                @else
                <div style="text-align:center;padding:8px;background:#fef2f2;border-radius:6px;font-size:12px;font-weight:600;color:#dc2626;">
                    ❌ NOT OFFERED
                </div>
                @endif
            </div>
            @endforeach

            <div style="padding:10px 14px;background:#f9fafb;border-radius:8px;display:flex;justify-content:space-between;font-size:13px;font-weight:700;color:#111827;border:1px solid #f3f4f6;">
                <span>Total Quote</span>
                <span class="vendor-col-total" data-vq="{{ $vq->id }}">Rp —</span>
            </div>
        </div>
    </div>
    @endforeach

</div>
@else
{{-- No vendor quotations — show invite form --}}
<div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:40px;text-align:center;margin-bottom:80px;">
    <div style="font-size:36px;margin-bottom:12px;">🏪</div>
    <div style="font-size:15px;font-weight:700;color:#111827;margin-bottom:6px;">No vendor quotations yet</div>
    <form action="{{ route('vendors.store', $rfq) }}" method="post">
        @csrf
        <button type="submit" style="padding:10px 18px;background:#1e3a5f;color:#fff;border:none;border-radius:8px;font-weight:700;cursor:pointer;">+ Invite Vendor</button>
    </form>
</div>
@endif

{{-- =====================================================================
     SUMMARY SECTION (hidden; shown after "Show Selection Result")
     ===================================================================== --}}
<div id="summary-section" style="display:none;margin-bottom:90px;">
    <div style="font-size:13.5px;font-weight:600;color:#374151;margin-bottom:12px;">
        Summary Selection for <span style="font-family:'Courier New',monospace;color:#1e3a5f;">{{ $pr->document_number }}</span>
    </div>

    <div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;margin-bottom:14px;">
        <div style="padding:13px 20px;border-bottom:1px solid #f3f4f6;display:flex;align-items:center;justify-content:space-between;">
            <div style="font-size:14px;font-weight:700;color:#111827;">Selected Items</div>
        </div>
        <div style="overflow-x:auto;">
            <table style="width:100%;border-collapse:collapse;font-size:13px;">
                <thead>
                    <tr style="background:#f9fafb;">
                        <th style="padding:9px 16px;text-align:left;font-size:10.5px;font-weight:700;color:#9ca3af;">NO</th>
                        <th style="padding:9px 16px;text-align:left;font-size:10.5px;font-weight:700;color:#9ca3af;">ITEM NAME</th>
                        <th style="padding:9px 16px;text-align:left;font-size:10.5px;font-weight:700;color:#9ca3af;">VENDOR</th>
                        <th style="padding:9px 16px;text-align:right;font-size:10.5px;font-weight:700;color:#9ca3af;">BUY QTY</th>
                        <th style="padding:9px 16px;text-align:left;font-size:10.5px;font-weight:700;color:#9ca3af;">UNIT</th>
                        <th style="padding:9px 16px;text-align:right;font-size:10.5px;font-weight:700;color:#9ca3af;">PRICE (RP)</th>
                        <th style="padding:9px 16px;text-align:right;font-size:10.5px;font-weight:700;color:#9ca3af;">SUBTOTAL (RP)</th>
                    </tr>
                </thead>
                <tbody id="summary-items-body"></tbody>
                <tfoot>
                    <tr style="background:#f9fafb;">
                        <td colspan="6" style="padding:11px 16px;text-align:right;font-weight:700;border-top:1px solid #e5e7eb;">Grand Total:</td>
                        <td colspan="2" style="padding:11px 16px;font-weight:700;color:#1e3a5f;border-top:1px solid #e5e7eb;" id="grand-total">Rp —</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<div style="position:fixed;bottom:0;left:0;right:0;z-index:100;background:#fff;border-top:1px solid #e5e7eb;padding:11px 28px;display:flex;align-items:center;justify-content:space-between;gap:16px;box-shadow:0 -4px 20px rgba(0,0,0,.06);flex-wrap:wrap;">
    <div style="display:flex;gap:0;border:1px solid #e5e7eb;border-radius:8px;overflow:hidden;">
        @foreach($vendorQuotations as $vq)
        <div style="padding:7px 16px;border-right:1px solid #e5e7eb;font-size:12.5px;color:#374151;">
            <span style="color:#9ca3af;font-size:11px;display:block;">Total Quote</span>
            <span style="font-weight:700;color:#1e3a5f;" id="footer-total-{{ $vq->id }}">Rp —</span>
        </div>
        @endforeach
    </div>

    <div style="display:flex;align-items:center;gap:16px;flex-wrap:wrap;">
        <div style="font-size:13px;color:#6b7280;">Items selected: <strong id="footer-count">0 / {{ $items->count() }}</strong></div>
        <span style="font-size:12px;color:#9ca3af;" id="footer-hint">Select all required items to submit</span>
        <button id="show-result-btn" onclick="showSelectionResult()"
            style="display:inline-flex;align-items:center;gap:7px;padding:9px 20px;background:#374151;color:#fff;border:none;border-radius:8px;font-size:13.5px;font-weight:700;cursor:pointer;opacity:.5;pointer-events:none;transition:.2s;">
            Show Selection Result
        </button>
    </div>
</div>

<div id="notes-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.4);z-index:300;align-items:center;justify-content:center;">
    <div style="background:#fff;border-radius:12px;width:100%;max-width:460px;padding:24px;">
        <div style="font-size:15px;font-weight:700;margin-bottom:16px;">Submission Notes</div>
        <textarea id="submission-notes" rows="4" style="width:100%;padding:10px;border:1px solid #d1d5db;border-radius:8px;margin-bottom:16px;box-sizing:border-box;"></textarea>
        <div style="display:flex;justify-content:flex-end;gap:10px;">
            <button onclick="closeNotesModal()" style="padding:8px 16px;border:1px solid #d1d5db;border-radius:8px;background:#fff;cursor:pointer;">Cancel</button>
            <button onclick="doSubmit()" style="padding:8px 16px;background:#15803d;color:#fff;border:none;border-radius:8px;font-weight:700;cursor:pointer;">Submit to Purchasing</button>
        </div>
    </div>
</div>

<form id="submit-form" action="{{ route('vendors.store.selection') }}" method="post" style="display:none;">
    @csrf
</form>

<script>
const TOTAL_ITEMS   = {{ $items->count() }};
const selectedItems = {};

document.addEventListener('DOMContentLoaded', markBestPrices);

function markBestPrices() {
    const priceMap = {}; 
    document.querySelectorAll('.vendor-item-card[data-not-offered="0"]').forEach(card => {
        const itemId = card.dataset.itemId;
        const price  = parseFloat(card.dataset.unitPrice) || 0;
        if (price > 0) {
            if (!priceMap[itemId]) priceMap[itemId] = [];
            priceMap[itemId].push({ card, price });
        }
    });

    for (const [itemId, entries] of Object.entries(priceMap)) {
        if (entries.length < 2) continue;
        const minPrice = Math.min(...entries.map(e => e.price));
        entries.forEach(({ card, price }) => {
            if (price === minPrice) {
                const badges = card.querySelectorAll('.best-price-badge');
                badges.forEach(b => {
                    b.style.display = 'inline';
                    b.style.cssText = 'padding:1px 5px;background:#fef3c7;border:1px solid #fbbf24;border-radius:3px;font-size:10px;font-weight:700;color:#92400e;margin-left:4px;';
                    b.textContent = 'BEST PRICE';
                });
            }
        });
    }
}

function toggleItemCard(card) {
    if (card.dataset.notOffered === '1') return;

    const itemId = card.dataset.itemId;
    const isSelected = card.dataset.selected === 'true';

    document.querySelectorAll(`.vendor-item-card[data-item-id="${itemId}"]`).forEach(c => {
        if (c !== card) deselectCard(c);
    });

    isSelected ? deselectCard(card) : selectCard(card);
    updateCounter();
    updateItemStatus(itemId);
}

function selectCard(card) {
    card.dataset.selected  = 'true';
    card.style.borderColor = '#3b82f6';
    card.style.background  = '#eff6ff';

    const cb = card.querySelector('.card-checkbox');
    if (cb) {
        cb.style.background  = '#3b82f6';
        cb.style.borderColor = '#3b82f6';
        cb.innerHTML = '<svg width="10" height="10" fill="none" stroke="white" stroke-width="3" viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5" stroke-linecap="round" stroke-linejoin="round"/></svg>';
    }

    selectedItems[card.dataset.itemId] = {
        vq_id:       card.dataset.vqId,
        vendor_id:   card.dataset.vendorId,
        vendor_name: card.dataset.vendorName,
        item_name:   card.dataset.itemName,
        item_id:     card.dataset.itemId,
        unit_price:  parseFloat(card.dataset.unitPrice) || null,
        subtotal:    parseFloat(card.dataset.subtotal)  || null,
        qty:         parseInt(card.dataset.offeredQty)  || null,
    };
    updateVendorTotal(card.dataset.vqId);
}

function deselectCard(card) {
    card.dataset.selected  = 'false';
    card.style.borderColor = '#e5e7eb';
    card.style.background  = '#fff';

    const cb = card.querySelector('.card-checkbox');
    if (cb) {
        cb.style.background  = '#fff';
        cb.style.borderColor = '#d1d5db';
        cb.innerHTML = '';
    }

    delete selectedItems[card.dataset.itemId];
    
    // Fitur Veren: Reset row highlight
    const row = document.getElementById(`item-row-${card.dataset.itemId}`);
    if (row) row.style.background = '';

    updateVendorTotal(card.dataset.vqId);
}

function updateVendorTotal(vqId) {
    let total = 0;
    for (const [, sel] of Object.entries(selectedItems)) {
        if (sel.vq_id === vqId && sel.subtotal) total += sel.subtotal;
    }
    const footerEl = document.getElementById(`footer-total-${vqId}`);
    if (footerEl) footerEl.textContent = total > 0 ? 'Rp ' + total.toLocaleString('id-ID') : 'Rp —';
    const colEl = document.querySelector(`.vendor-col-total[data-vq="${vqId}"]`);
    if (colEl) colEl.textContent = total > 0 ? 'Rp ' + total.toLocaleString('id-ID') : 'Rp —';
}

function updateItemStatus(itemId) {
    const badge = document.getElementById(`item-status-${itemId}`);
    const row   = document.getElementById(`item-row-${itemId}`);
    if (!badge) return;

    if (selectedItems[itemId]) {
        const sel = selectedItems[itemId];
        const requiredQty = parseInt(document.querySelector(`.vendor-item-card[data-item-id="${itemId}"]`)?.dataset.requiredQty) || 0;
        const offeredQty  = sel.qty || 0;
        let bgColor, textColor, dotColor, label, rowBg;

        if (offeredQty === requiredQty) {
            bgColor   = '#dcfce7'; textColor = '#15803d'; dotColor = '#22c55e';
            rowBg     = '#f0fdf4'; label     = 'Match';
        } else if (offeredQty < requiredQty) {
            bgColor   = '#fee2e2'; textColor = '#b91c1c'; dotColor = '#ef4444';
            rowBg     = '#fff5f5'; label     = `Qty Less (${offeredQty}/${requiredQty})`;
        } else {
            bgColor   = '#dbeafe'; textColor = '#1d4ed8'; dotColor = '#3b82f6';
            rowBg     = '#eff6ff'; label     = `Qty More (${offeredQty}/${requiredQty})`;
        }

        badge.innerHTML = `<span style="width:6px;height:6px;border-radius:50%;background:${dotColor};display:inline-block;margin-right:4px;"></span>${label}`;
        badge.style.background = bgColor;
        badge.style.color      = textColor;
        if (row) row.style.background = rowBg;
    } else {
        badge.innerHTML = '<span style="width:6px;height:6px;border-radius:50%;background:#f97316;display:inline-block;margin-right:4px;"></span>Pending';
        badge.style.background = '#fff7ed'; badge.style.color = '#c2410c';
        if (row) row.style.background = '';
    }
}

function updateCounter() {
    const count = Object.keys(selectedItems).length;
    document.getElementById('selected-count').textContent = count;
    document.getElementById('footer-count').textContent   = `${count} / ${TOTAL_ITEMS}`;
    document.getElementById('progress-bar').style.width   = `${(count / TOTAL_ITEMS) * 100}%`;

    const btn  = document.getElementById('show-result-btn');
    const hint = document.getElementById('footer-hint');
    if (count >= TOTAL_ITEMS) {
        btn.style.opacity = '1'; btn.style.pointerEvents = 'auto'; btn.style.background = '#1e3a5f';
        hint.textContent = '';
    } else {
        btn.style.opacity = '.5'; btn.style.pointerEvents = 'none'; btn.style.background = '#374151';
        hint.textContent = 'Select all required items to enable submit';
    }
}

function showSelectionResult() {
    const tbody = document.getElementById('summary-items-body');
    tbody.innerHTML = '';
    let grandTotal = 0;
    let rowNum = 1;

    for (const [itemId, sel] of Object.entries(selectedItems)) {
        if (sel.subtotal) grandTotal += sel.subtotal;
        const tr = document.createElement('tr');
        tr.style.borderBottom = '1px solid #f9fafb';
        tr.innerHTML = `
            <td style="padding:11px 16px;">${rowNum++}</td>
            <td style="padding:11px 16px;font-weight:600;">${sel.item_name}</td>
            <td style="padding:11px 16px;font-weight:600;color:#1d4ed8;">${sel.vendor_name}</td>
            <td style="padding:11px 16px;text-align:right;">${sel.qty}</td>
            <td style="padding:11px 16px;">${sel.unit ?? ''}</td>
            <td style="padding:11px 16px;text-align:right;">${sel.unit_price ? 'Rp '+Number(sel.unit_price).toLocaleString('id-ID') : '—'}</td>
            <td style="padding:11px 16px;text-align:right;font-weight:700;">${sel.subtotal ? 'Rp '+Number(sel.subtotal).toLocaleString('id-ID') : '—'}</td>
        `;
        tbody.appendChild(tr);
    }

    document.getElementById('grand-total').textContent = grandTotal ? 'Rp ' + grandTotal.toLocaleString('id-ID') : 'Rp —';
    document.getElementById('summary-section').style.display = 'block';
    document.getElementById('summary-section').scrollIntoView({ behavior: 'smooth' });

    const btn = document.getElementById('show-result-btn');
    btn.innerHTML = `Confirm & Submit`;
    btn.style.background = '#15803d';
    btn.onclick = openNotesModal;
}

function openNotesModal() { document.getElementById('notes-modal').style.display = 'flex'; }
function closeNotesModal() { document.getElementById('notes-modal').style.display = 'none'; }

function doSubmit() {
    const notes = document.getElementById('submission-notes').value;
    const form = document.getElementById('submit-form');
    
    form.innerHTML = '@csrf'; 
    form.insertAdjacentHTML('beforeend', `<input type="hidden" name="pr_id" value="{{ $pr->id }}">`);
    form.insertAdjacentHTML('beforeend', `<input type="hidden" name="selection_notes" value="${notes}">`);

    let i = 0;
    for (const [itemId, sel] of Object.entries(selectedItems)) {
        form.insertAdjacentHTML('beforeend', `<input type="hidden" name="selections[${i}][vendor_id]" value="${sel.vendor_id}">`);
        form.insertAdjacentHTML('beforeend', `<input type="hidden" name="selections[${i}][item_id]" value="${sel.item_id}">`);
        form.insertAdjacentHTML('beforeend', `<input type="hidden" name="selections[${i}][unit_price]" value="${sel.unit_price}">`);
        form.insertAdjacentHTML('beforeend', `<input type="hidden" name="selections[${i}][quantity]" value="${sel.qty}">`);
        form.insertAdjacentHTML('beforeend', `<input type="hidden" name="selections[${i}][notes]" value="Selected from UI">`);
        i++;
    }

    form.submit();
}
</script>

@endif
@endsection