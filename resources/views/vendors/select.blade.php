@extends('layouts.app')
@php $pageTitle = 'Vendor Selection'; @endphp

@section('content')

{{-- =====================================================================
     STEP 1: SELECT PR — shown when no rfq/pr is selected
     Controller should pass: $rfqs (Collection of Rfq with purchaseRequest)
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
     Controller should pass:
       $rfq (Rfq with purchaseRequest.items + vendorQuotations.vendor loaded)
     ===================================================================== --}}

@php
    $pr              = $rfq->purchaseRequest;
    $items           = $pr->items;
    $vendorQuotations = $rfq->vendorQuotations->load('vendor');
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
    <span style="padding:2px 9px;background:#f3f4f6;border-radius:4px;color:#6b7280;font-weight:600;">Unit Different</span>
    <span style="padding:2px 9px;background:#ffedd5;border-radius:4px;color:#c2410c;font-weight:600;">Selected (qty insufficient)</span>
    <span style="padding:2px 9px;background:#f3f4f6;border-radius:4px;color:#374151;">Qty differs from PR</span>
    <span style="padding:2px 9px;background:#dcfce7;border-radius:4px;color:#15803d;">Selected (qty sufficient)</span>
    <span style="padding:2px 9px;background:#f9fafb;border-radius:4px;color:#9ca3af;">Not offered</span>
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
                    <th style="padding:9px 16px;text-align:left;font-size:10.5px;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.05em;">NO</th>
                    <th style="padding:9px 16px;text-align:left;font-size:10.5px;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.05em;">ITEM ID</th>
                    <th style="padding:9px 16px;text-align:left;font-size:10.5px;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.05em;">ITEM NAME</th>
                    <th style="padding:9px 16px;text-align:left;font-size:10.5px;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.05em;">SPECIFICATION</th>
                    <th style="padding:9px 16px;text-align:right;font-size:10.5px;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.05em;">REQUIRED QTY</th>
                    <th style="padding:9px 16px;text-align:left;font-size:10.5px;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.05em;">UNIT</th>
                    <th style="padding:9px 16px;text-align:left;font-size:10.5px;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.05em;">STATUS</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $i => $item)
                <tr id="item-row-{{ $item->id }}" style="border-bottom:1px solid #f9fafb;">
                    <td style="padding:11px 16px;color:#6b7280;">{{ $i + 1 }}</td>
                    <td style="padding:11px 16px;font-family:'Courier New',monospace;font-size:12px;font-weight:600;color:#3b5bdb;">{{ $item->item_code }}</td>
                    <td style="padding:11px 16px;font-weight:600;color:#111827;">{{ $item->item_name ?: $item->name }}</td>
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
    @php $vendor = $vq->vendor; @endphp

    <div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;overflow:hidden;">

        {{-- Vendor Header --}}
        <div style="padding:13px 16px;border-bottom:1px solid #f3f4f6;background:#fafafa;">
            <div style="font-size:14px;font-weight:700;color:#111827;">{{ $vendor->vendor_name ?? $vendor->name }}</div>
            <div style="font-size:12px;color:#9ca3af;margin-top:2px;">
                {{ $vendor->location ?? '—' }}
                @if($vq->lead_time) • Lead: {{ $vq->lead_time }} days @endif
            </div>
        </div>

        {{-- Item Cards --}}
        <div style="padding:12px;display:flex;flex-direction:column;gap:10px;">

            @foreach($items as $item)
            @php
                // Try to find quotation detail for this vendor + item
                // Adapt this lookup to your actual data model as needed
                $detail = null;
                // Example: $detail = $vq->quotationDetails->firstWhere('purchase_request_item_id', $item->id);

                $offeredQty   = $detail?->offered_quantity  ?? null;
                $unitPrice    = $detail?->offered_price_per_item ?? null;
                $subtotal     = ($offeredQty && $unitPrice) ? $offeredQty * $unitPrice : null;
                $isNotOffered = $detail === null;

                $qtyBadge = '';
                $cardBg   = '#fff';
                if (!$isNotOffered && $offeredQty !== null) {
                    if ($offeredQty == $item->quantity) {
                        $qtyBadge = '<span style="padding:1px 6px;background:#dcfce7;border-radius:4px;font-size:11px;font-weight:700;color:#15803d;margin-left:4px;">MATCH</span>';
                        $cardBg   = '#fff';
                    } elseif ($offeredQty < $item->quantity) {
                        $diff = $item->quantity - $offeredQty;
                        $qtyBadge = "<span style=\"padding:1px 6px;background:#ffedd5;border-radius:4px;font-size:11px;font-weight:700;color:#c2410c;margin-left:4px;\">INSUFFICIENT (Need {$diff} more)</span>";
                        $cardBg   = '#fffbf5';
                    } else {
                        $surplus = $offeredQty - $item->quantity;
                        $qtyBadge = "<span style=\"padding:1px 6px;background:#dbeafe;border-radius:4px;font-size:11px;font-weight:700;color:#1d4ed8;margin-left:4px;\">SURPLUS (+{$surplus})</span>";
                    }
                }

                // Determine best price tag (we'll compute this client side)
                $formattedPrice    = $unitPrice    ? 'Rp ' . number_format($unitPrice, 0, ',', '.') : null;
                $formattedSubtotal = $subtotal     ? 'Rp ' . number_format($subtotal,  0, ',', '.') : null;
                $formattedQty      = $offeredQty   ? $offeredQty . ' / ' . $item->quantity : '— / ' . $item->quantity;
            @endphp

            <div class="vendor-item-card"
                 id="card-{{ $vq->id }}-{{ $item->id }}"
                 data-vq-id="{{ $vq->id }}"
                 data-item-id="{{ $item->id }}"
                 data-required-qty="{{ $item->quantity }}"
                 data-offered-qty="{{ $offeredQty ?? '' }}"
                 data-unit-price="{{ $unitPrice ?? '' }}"
                 data-subtotal="{{ $subtotal ?? '' }}"
                 data-vendor-name="{{ addslashes($vendor->vendor_name ?? $vendor->name) }}"
                 data-item-name="{{ addslashes($item->item_name ?: $item->name) }}"
                 data-not-offered="{{ $isNotOffered ? '1' : '0' }}"
                 style="border:1px solid #e5e7eb;border-radius:10px;padding:12px 14px;position:relative;cursor:{{ $isNotOffered ? 'not-allowed' : 'pointer' }};background:{{ $cardBg }};transition:.15s;"
                 @if(!$isNotOffered)
                 onclick="toggleItemCard(this)"
                 onmouseover="if(this.dataset.selected!=='true')this.style.borderColor='#93c5fd'"
                 onmouseout="if(this.dataset.selected!=='true')this.style.borderColor='#e5e7eb'"
                 @endif>

                {{-- Checkbox (hidden for not-offered) --}}
                @if(!$isNotOffered)
                <div style="position:absolute;top:12px;right:12px;">
                    <div class="card-checkbox" style="width:17px;height:17px;border:2px solid #d1d5db;border-radius:4px;background:#fff;display:flex;align-items:center;justify-content:center;transition:.15s;"></div>
                </div>
                @endif

                <div style="font-size:13px;font-weight:700;color:{{ $isNotOffered ? '#9ca3af' : '#111827' }};margin-bottom:10px;padding-right:24px;">
                    {{ $item->item_name ?: $item->name }}
                </div>

                @if(!$isNotOffered)
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:5px;font-size:12px;">
                    <div>
                        <span style="color:#9ca3af;font-size:11px;">Qty Offer</span><br>
                        <span style="font-weight:600;color:#111827;">
                            {{ $formattedQty }}{!! $qtyBadge !!}
                        </span>
                    </div>
                    <div>
                        <span style="color:#9ca3af;font-size:11px;">Unit</span><br>
                        <span style="font-weight:600;color:#111827;">{{ $item->unit }}</span>
                    </div>
                    <div>
                        <span style="color:#9ca3af;font-size:11px;">Unit Price</span><br>
                        <span style="font-weight:600;color:#111827;">
                            {{ $formattedPrice ?? '—' }}
                            {{-- Best price badge rendered by JS after all cards load --}}
                            <span class="best-price-badge" data-item="{{ $item->id }}" data-price="{{ $unitPrice ?? 0 }}" style="display:none;"></span>
                        </span>
                    </div>
                    <div>
                        <span style="color:#9ca3af;font-size:11px;">Notes</span><br>
                        <span style="color:#6b7280;font-size:12px;">{{ $vq->notes ?? 'Add note...' }}</span>
                    </div>
                    <div>
                        <span style="color:#9ca3af;font-size:11px;">Subtotal</span><br>
                        <span style="font-weight:600;color:#111827;">{{ $formattedSubtotal ?? '—' }}</span>
                    </div>
                    <div>
                        <span style="color:#9ca3af;font-size:11px;">Warranty</span><br>
                        <span style="color:#6b7280;">{{ $vq->warranty ?? '—' }}</span>
                    </div>
                </div>
                @else
                <div style="text-align:center;padding:8px;background:#fef2f2;border-radius:6px;font-size:12px;font-weight:600;color:#dc2626;">
                    ❌ NOT OFFERED
                </div>
                @endif
            </div>
            @endforeach

            {{-- Vendor Total --}}
            <div style="padding:10px 14px;background:#f9fafb;border-radius:8px;display:flex;justify-content:space-between;font-size:13px;font-weight:700;color:#111827;border:1px solid #f3f4f6;">
                <span>Total Quote</span>
                <span class="vendor-col-total" data-vq="{{ $vq->id }}">
                    @php
                        // Server-side total if details exist
                        $vTotal = 0; // sum from quotation details if available
                    @endphp
                    Rp {{ $vTotal ? number_format($vTotal, 0, ',', '.') : '—' }}
                </span>
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
    <div style="font-size:13.5px;color:#9ca3af;margin-bottom:20px;">Invite vendors to submit their quotations for this RFQ first.</div>
    <form action="{{ route('vendors.store', $rfq) }}" method="post" style="display:inline-flex;flex-direction:column;align-items:center;gap:10px;min-width:300px;">
        @csrf
        <div style="position:relative;width:100%;">
            <select name="vendor_id" required
                style="width:100%;padding:10px 36px 10px 14px;border:1px solid #d1d5db;border-radius:8px;font-size:13.5px;color:#374151;background:#fff;appearance:none;cursor:pointer;font-family:inherit;outline:none;box-sizing:border-box;">
                <option value="">— Select a vendor —</option>
                @foreach($vendors ?? \App\Models\Vendor::where('status','active')->get() as $v)
                <option value="{{ $v->id }}">{{ $v->vendor_name ?? $v->name }}{{ $v->location ? ' ('.$v->location.')' : '' }}</option>
                @endforeach
            </select>
            <svg style="position:absolute;right:10px;top:50%;transform:translateY(-50%);pointer-events:none;color:#9ca3af;" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 9l6 6 6-6" stroke-linecap="round"/></svg>
        </div>
        <button type="submit" style="width:100%;padding:10px 18px;background:#1e3a5f;color:#fff;border:none;border-radius:8px;font-size:13.5px;font-weight:700;cursor:pointer;font-family:inherit;">
            + Invite Vendor
        </button>
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

    {{-- Summary Information --}}
    <div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;margin-bottom:14px;">
        <div style="padding:13px 20px;border-bottom:1px solid #f3f4f6;display:flex;align-items:center;justify-content:space-between;">
            <div style="font-size:14px;font-weight:700;color:#111827;">Summary Information</div>
            <span style="display:inline-flex;align-items:center;gap:5px;padding:4px 12px;border-radius:999px;background:#dcfce7;font-size:12px;font-weight:600;color:#15803d;">
                <span style="width:6px;height:6px;border-radius:50%;background:#22c55e;"></span>Ready for Purchasing
            </span>
        </div>
        <table style="width:100%;border-collapse:collapse;font-size:13.5px;">
            <thead>
                <tr style="background:#f9fafb;">
                    <th style="padding:9px 20px;text-align:left;font-size:10.5px;font-weight:700;color:#9ca3af;text-transform:uppercase;width:200px;">FIELD</th>
                    <th style="padding:9px 20px;text-align:left;font-size:10.5px;font-weight:700;color:#9ca3af;text-transform:uppercase;">DETAIL</th>
                </tr>
            </thead>
            <tbody>
                <tr style="border-bottom:1px solid #f9fafb;">
                    <td style="padding:12px 20px;font-weight:600;color:#6b7280;">PR Number</td>
                    <td style="padding:12px 20px;font-family:'Courier New',monospace;font-weight:600;color:#111827;">{{ $pr->document_number }}</td>
                </tr>
                <tr style="border-bottom:1px solid #f9fafb;">
                    <td style="padding:12px 20px;font-weight:600;color:#6b7280;">Submission Date</td>
                    <td style="padding:12px 20px;color:#111827;">{{ \Carbon\Carbon::parse($pr->created_at)->format('d/m/Y') }}</td>
                </tr>
                <tr>
                    <td style="padding:12px 20px;font-weight:600;color:#6b7280;">Selection Notes</td>
                    <td style="padding:12px 20px;color:#9ca3af;" id="summary-notes-display">—</td>
                </tr>
            </tbody>
        </table>
    </div>

    {{-- Selected Items --}}
    <div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;margin-bottom:14px;">
        <div style="padding:13px 20px;border-bottom:1px solid #f3f4f6;display:flex;align-items:center;justify-content:space-between;">
            <div style="font-size:14px;font-weight:700;color:#111827;">Selected Items</div>
            <span style="font-size:13px;font-weight:600;color:#15803d;" id="summary-item-count">{{ $items->count() }} Items ready to process</span>
        </div>
        <div style="overflow-x:auto;">
            <table style="width:100%;border-collapse:collapse;font-size:13px;">
                <thead>
                    <tr style="background:#f9fafb;">
                        <th style="padding:9px 16px;text-align:left;font-size:10.5px;font-weight:700;color:#9ca3af;text-transform:uppercase;">NO</th>
                        <th style="padding:9px 16px;text-align:left;font-size:10.5px;font-weight:700;color:#9ca3af;text-transform:uppercase;">ITEM ID</th>
                        <th style="padding:9px 16px;text-align:left;font-size:10.5px;font-weight:700;color:#9ca3af;text-transform:uppercase;">ITEM NAME</th>
                        <th style="padding:9px 16px;text-align:left;font-size:10.5px;font-weight:700;color:#9ca3af;text-transform:uppercase;">SPECIFICATION</th>
                        <th style="padding:9px 16px;text-align:right;font-size:10.5px;font-weight:700;color:#9ca3af;text-transform:uppercase;">QTY</th>
                        <th style="padding:9px 16px;text-align:left;font-size:10.5px;font-weight:700;color:#9ca3af;text-transform:uppercase;">UNIT</th>
                        <th style="padding:9px 16px;text-align:right;font-size:10.5px;font-weight:700;color:#9ca3af;text-transform:uppercase;">UNIT PRICE (RP)</th>
                        <th style="padding:9px 16px;text-align:right;font-size:10.5px;font-weight:700;color:#9ca3af;text-transform:uppercase;">TOTAL (RP)</th>
                        <th style="padding:9px 16px;text-align:left;font-size:10.5px;font-weight:700;color:#9ca3af;text-transform:uppercase;">VENDOR</th>
                        <th style="padding:9px 16px;text-align:left;font-size:10.5px;font-weight:700;color:#9ca3af;text-transform:uppercase;">NOTES</th>
                    </tr>
                </thead>
                <tbody id="summary-items-body"></tbody>
                <tfoot>
                    <tr style="background:#f9fafb;">
                        <td colspan="7" style="padding:11px 16px;text-align:right;font-size:13px;font-weight:700;color:#374151;border-top:1px solid #e5e7eb;">Grand Total:</td>
                        <td colspan="3" style="padding:11px 16px;font-size:13px;font-weight:700;color:#1e3a5f;border-top:1px solid #e5e7eb;" id="grand-total">Rp —</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    {{-- Vendor Purchase Summary --}}
    <div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:20px;">
        <div style="font-size:11px;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.08em;margin-bottom:14px;">Vendor Purchase Summary</div>
        <div id="vendor-summary-cards" style="display:flex;gap:12px;flex-wrap:wrap;"></div>
    </div>
</div>

{{-- STICKY BOTTOM BAR --}}
<div style="position:fixed;bottom:0;left:0;right:0;z-index:100;background:#fff;border-top:1px solid #e5e7eb;padding:11px 28px;display:flex;align-items:center;justify-content:space-between;gap:16px;box-shadow:0 -4px 20px rgba(0,0,0,.06);flex-wrap:wrap;">

    {{-- Vendor total tabs --}}
    <div style="display:flex;gap:0;border:1px solid #e5e7eb;border-radius:8px;overflow:hidden;">
        @foreach($vendorQuotations as $vq)
        <div style="padding:7px 16px;border-right:1px solid #e5e7eb;font-size:12.5px;color:#374151;">
            <span style="color:#9ca3af;font-size:11px;display:block;">Total Quote</span>
            <span style="font-weight:700;color:#1e3a5f;" id="footer-total-{{ $vq->id }}">Rp —</span>
        </div>
        @endforeach
    </div>

    <div style="display:flex;align-items:center;gap:16px;flex-wrap:wrap;">
        <div style="font-size:13px;color:#6b7280;">
            Items selected: <strong id="footer-count">0 / {{ $items->count() }}</strong>
        </div>
        <span style="font-size:12px;color:#9ca3af;" id="footer-hint">Select all required items to enable submit</span>
        <button id="show-result-btn" onclick="showSelectionResult()"
            style="display:inline-flex;align-items:center;gap:7px;padding:9px 20px;background:#374151;color:#fff;border:none;border-radius:8px;font-size:13.5px;font-weight:700;cursor:pointer;font-family:inherit;opacity:.5;pointer-events:none;transition:.2s;">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11" stroke-linecap="round"/></svg>
            Show Selection Result
        </button>
    </div>
</div>

{{-- SUBMISSION NOTES MODAL --}}
<div id="notes-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.4);z-index:300;align-items:center;justify-content:center;">
    <div style="background:#fff;border-radius:12px;width:100%;max-width:460px;padding:24px;box-shadow:0 8px 40px rgba(0,0,0,.15);">
        <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:16px;">
            <div>
                <div style="font-size:15px;font-weight:700;color:#111827;">Submission Notes</div>
                <div style="font-size:13px;color:#6b7280;margin-top:2px;">Add remarks before submit to Purchasing</div>
            </div>
            <button onclick="closeNotesModal()" style="background:none;border:none;cursor:pointer;color:#9ca3af;">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M18 6L6 18M6 6l12 12" stroke-linecap="round"/></svg>
            </button>
        </div>
        <label style="display:block;font-size:11px;font-weight:700;color:#374151;text-transform:uppercase;letter-spacing:.05em;margin-bottom:8px;">Notes / Remarks</label>
        <textarea id="submission-notes" rows="4" placeholder="e.g. Prioritize fast delivery items..."
            style="width:100%;padding:10px 12px;border:1.5px solid #3b82f6;border-radius:8px;font-size:13.5px;color:#111827;font-family:inherit;outline:none;resize:vertical;box-sizing:border-box;margin-bottom:16px;"></textarea>
        <div style="display:flex;gap:10px;justify-content:flex-end;">
            <button onclick="closeNotesModal()"
                style="padding:9px 18px;border:1px solid #d1d5db;border-radius:8px;background:#fff;font-size:13.5px;font-weight:600;color:#374151;cursor:pointer;font-family:inherit;">
                Cancel
            </button>
            <button onclick="doSubmit()"
                style="display:inline-flex;align-items:center;gap:7px;padding:9px 18px;background:#15803d;color:#fff;border:none;border-radius:8px;font-size:13.5px;font-weight:700;cursor:pointer;font-family:inherit;">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                Submit to Purchasing
            </button>
        </div>
    </div>
</div>

{{-- SUCCESS MODAL --}}
<div id="success-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.4);z-index:400;align-items:center;justify-content:center;">
    <div style="background:#fff;border-radius:12px;width:100%;max-width:360px;padding:32px;text-align:center;box-shadow:0 8px 40px rgba(0,0,0,.15);">
        <div style="width:52px;height:52px;background:#dcfce7;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 14px;">
            <svg width="24" height="24" fill="none" stroke="#15803d" stroke-width="2.5" viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </div>
        <div style="font-size:18px;font-weight:700;color:#111827;margin-bottom:10px;">Success!</div>
        <div style="font-size:13.5px;color:#6b7280;line-height:1.7;">
            PR: <strong>{{ $pr->document_number }}</strong><br>
            Notes: <span id="success-notes-display">—</span>
        </div>
        <button onclick="closeSuccess()"
            style="margin-top:20px;padding:9px 28px;border:1px solid #d1d5db;border-radius:8px;background:#fff;font-size:13.5px;font-weight:600;color:#374151;cursor:pointer;font-family:inherit;">
            Close
        </button>
    </div>
</div>

{{-- Hidden submit form --}}
<form id="submit-form" action="{{ route('vendors.store', $rfq) }}" method="post" style="display:none;">
    @csrf
    <input type="hidden" name="vendor_id" id="form-vendor-id" value="">
    <input type="hidden" name="note" id="form-note" value="">
    <input type="hidden" name="selection_data" id="form-selection-data" value="">
</form>

<script>
// -----------------------------------------------------------------------
// State
// -----------------------------------------------------------------------
const TOTAL_ITEMS   = {{ $items->count() }};
const selectedItems = {}; // { item_id: { vq_id, vendor_name, item_name, unit_price, subtotal, qty } }

// -----------------------------------------------------------------------
// On page load: mark best price per item
// -----------------------------------------------------------------------
document.addEventListener('DOMContentLoaded', function() {
    markBestPrices();
});

function markBestPrices() {
    // Group prices per item across all vendor cards
    const priceMap = {}; // item_id => [ { card, price } ]
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
                // Find the price cell and add BEST PRICE badge
                const badges = card.querySelectorAll('.best-price-badge');
                badges.forEach(b => {
                    b.style.display = 'inline';
                    b.style.cssText = 'display:inline;padding:1px 5px;background:#fef3c7;border:1px solid #fbbf24;border-radius:3px;font-size:10px;font-weight:700;color:#92400e;margin-left:4px;';
                    b.textContent = 'BEST PRICE';
                });
            }
        });
    }
}

// -----------------------------------------------------------------------
// Toggle card selection
// -----------------------------------------------------------------------
function toggleItemCard(card) {
    if (card.dataset.notOffered === '1') return;

    const itemId = card.dataset.itemId;
    const isSelected = card.dataset.selected === 'true';

    // Deselect all other vendor cards for same item
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

// -----------------------------------------------------------------------
// Update PR item row status
// -----------------------------------------------------------------------
function updateItemStatus(itemId) {
    const badge = document.getElementById(`item-status-${itemId}`);
    if (!badge) return;
    if (selectedItems[itemId]) {
        badge.innerHTML = '<span style="width:6px;height:6px;border-radius:50%;background:#22c55e;display:inline-block;margin-right:4px;"></span>Match';
        badge.style.background = '#dcfce7';
        badge.style.color      = '#15803d';
    } else {
        badge.innerHTML = '<span style="width:6px;height:6px;border-radius:50%;background:#f97316;display:inline-block;margin-right:4px;"></span>Pending';
        badge.style.background = '#fff7ed';
        badge.style.color      = '#c2410c';
    }
}

// -----------------------------------------------------------------------
// Update counter + submit button
// -----------------------------------------------------------------------
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

// -----------------------------------------------------------------------
// Show Selection Result
// -----------------------------------------------------------------------
const itemMeta = @json($items->map(fn($it) => ([
    'id'         => $it->id,
    'item_code'  => $it->item_code,
    'name'       => $it->item_name ?: $it->name,
    'spec'       => $it->specification ?? '—',
    'qty'        => $it->quantity,
    'unit'       => $it->unit,
])->keyBy('id'));

function showSelectionResult() {
    const tbody = document.getElementById('summary-items-body');
    tbody.innerHTML = '';
    let grandTotal = 0;
    let rowNum = 1;

    const vendorBreakdown = {};
    const vendorColors = [['#dbeafe','#1d4ed8'],['#dcfce7','#15803d'],['#fef3c7','#92400e'],['#f3e8ff','#7e22ce']];
    const vendorColorMap = {};
    let ci = 0;

    for (const [itemId, meta] of Object.entries(itemMeta)) {
        const sel = selectedItems[itemId];
        const unitPrice = sel?.unit_price;
        const subtotal  = sel?.subtotal;
        const vendor    = sel?.vendor_name ?? '—';

        if (subtotal) grandTotal += subtotal;

        if (!vendorColorMap[vendor]) vendorColorMap[vendor] = vendorColors[ci++ % vendorColors.length];
        const [vBg, vTxt] = vendorColorMap[vendor];

        const tr = document.createElement('tr');
        tr.style.borderBottom = '1px solid #f9fafb';
        tr.innerHTML = `
            <td style="padding:11px 16px;color:#6b7280;">${rowNum++}</td>
            <td style="padding:11px 16px;font-family:'Courier New',monospace;font-size:12px;font-weight:600;color:#3b5bdb;">${meta.item_code}</td>
            <td style="padding:11px 16px;font-weight:600;color:#111827;">${meta.name}</td>
            <td style="padding:11px 16px;font-size:12.5px;color:#6b7280;">${meta.spec}</td>
            <td style="padding:11px 16px;font-weight:700;text-align:right;">${meta.qty}</td>
            <td style="padding:11px 16px;color:#6b7280;">${meta.unit}</td>
            <td style="padding:11px 16px;text-align:right;font-weight:600;">${unitPrice ? 'Rp '+Number(unitPrice).toLocaleString('id-ID') : '—'}</td>
            <td style="padding:11px 16px;text-align:right;font-weight:600;">${subtotal ? 'Rp '+Number(subtotal).toLocaleString('id-ID') : '—'}</td>
            <td style="padding:11px 16px;"><span style="padding:2px 8px;border-radius:4px;font-size:12px;font-weight:600;background:${vBg};color:${vTxt};">${vendor}</span></td>
            <td style="padding:11px 16px;font-size:12.5px;color:#15803d;">Selected</td>
        `;
        tbody.appendChild(tr);

        if (sel) {
            if (!vendorBreakdown[vendor]) vendorBreakdown[vendor] = { items: [], total: 0 };
            vendorBreakdown[vendor].items.push({ name: meta.name, qty: meta.qty, unit: meta.unit, unitPrice, subtotal });
            if (subtotal) vendorBreakdown[vendor].total += subtotal;
        }
    }

    document.getElementById('grand-total').textContent = grandTotal
        ? 'Rp ' + grandTotal.toLocaleString('id-ID') : 'Rp —';

    // Vendor summary cards
    const container = document.getElementById('vendor-summary-cards');
    container.innerHTML = '';
    let cj = 0;
    for (const [vendorName, data] of Object.entries(vendorBreakdown)) {
        const [bg, txt] = vendorColors[cj++ % vendorColors.length];
        const rows = data.items.map(it => `
            <div style="display:flex;justify-content:space-between;font-size:12.5px;margin-bottom:2px;">
                <span style="font-weight:600;color:#374151;">${it.name}</span>
                <span style="font-weight:700;color:#111827;">${it.subtotal ? 'Rp '+Number(it.subtotal).toLocaleString('id-ID') : '—'}</span>
            </div>
            <div style="font-size:11.5px;color:#9ca3af;margin-bottom:8px;">${it.qty} ${it.unit} × Rp ${it.unitPrice ? Number(it.unitPrice).toLocaleString('id-ID') : '—'}</div>
        `).join('');

        const card = document.createElement('div');
        card.style.cssText = 'flex:1;min-width:200px;border:1px solid #e5e7eb;border-radius:10px;padding:14px 16px;';
        card.innerHTML = `
            <div style="font-size:13.5px;font-weight:700;color:#111827;margin-bottom:2px;">${vendorName}</div>
            <div style="font-size:12px;color:#9ca3af;margin-bottom:12px;">${data.items.length} selected item(s)</div>
            ${rows}
            <div style="border-top:1px solid #e5e7eb;padding-top:10px;display:flex;justify-content:space-between;">
                <span style="font-size:13px;font-weight:600;color:#6b7280;">Vendor Total</span>
                <span style="font-size:13px;font-weight:700;color:#1e3a5f;">Rp ${data.total ? data.total.toLocaleString('id-ID') : '—'}</span>
            </div>
        `;
        container.appendChild(card);
    }

    document.getElementById('summary-section').style.display = 'block';
    document.getElementById('summary-section').scrollIntoView({ behavior: 'smooth' });

    // Swap button to Confirm
    const btn = document.getElementById('show-result-btn');
    btn.innerHTML = `<svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5" stroke-linecap="round" stroke-linejoin="round"/></svg> Confirm & Submit to Purchasing`;
    btn.style.background = '#15803d';
    btn.onclick = openNotesModal;
}

// -----------------------------------------------------------------------
// Modals
// -----------------------------------------------------------------------
function openNotesModal() { document.getElementById('notes-modal').style.display = 'flex'; }
function closeNotesModal() { document.getElementById('notes-modal').style.display = 'none'; }

function doSubmit() {
    const notes = document.getElementById('submission-notes').value;
    document.getElementById('form-note').value           = notes;
    document.getElementById('form-selection-data').value = JSON.stringify(selectedItems);
    document.getElementById('summary-notes-display').textContent = notes || '—';
    document.getElementById('success-notes-display').textContent = notes || '—';

    closeNotesModal();
    document.getElementById('success-modal').style.display = 'flex';
    // Uncomment to actually POST: document.getElementById('submit-form').submit();
}

function closeSuccess() {
    document.getElementById('success-modal').style.display = 'none';
    window.location.href = '{{ route("pr.list") }}';
}

['notes-modal','success-modal'].forEach(id => {
    document.getElementById(id)?.addEventListener('click', e => {
        if (e.target === document.getElementById(id)) document.getElementById(id).style.display = 'none';
    });
});
</script>

@endif
@endsection