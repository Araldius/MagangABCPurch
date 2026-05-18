@extends('layouts.app')
@php
    $pageTitle = 'Dashboard';
    $user      = auth()->user();
    $firstName = explode(' ', $user->name)[0];

    /**
     * STATUS MAP — 4 status valid:
     *   awaiting_approval  → Awaiting Approval  (oranye)
     *   in_process         → In Process         (biru muda)
     *   approved           → Approved           (hijau)
     *   completed          → Completed          (biru)
     *
     * Format: [label, bg, textColor, dotColor]
     */
    $statusCfg = [
        'awaiting_approval' => ['Awaiting Approval', '#fff7ed', '#c2410c', '#f97316'],
        'in_process'        => ['In Process',        '#f0f9ff', '#0369a1', '#0ea5e9'],
        'approved'          => ['Approved',           '#f0fdf4', '#15803d', '#22c55e'],
        'completed'         => ['Completed',          '#eff6ff', '#1d4ed8', '#3b82f6'],
    ];
@endphp
@section('content')

{{-- PAGE HEADER --}}
<div style="margin-bottom:24px;">
    <h1 style="font-size:22px;font-weight:700;color:#111827;margin:0 0 4px;">Welcome back, {{ $firstName }}</h1>
    <p style="font-size:13.5px;color:#6b7280;margin:0;">Here's a summary of your procurement requests.</p>
</div>

{{-- ═══ STAT CARDS ═══ --}}
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:24px;">

    <div style="background:#fff;border:1px solid #e5e7eb;border-radius:14px;padding:22px 24px;">
        <div style="font-size:11px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.07em;">Active </div>
        <div style="font-size:32px;font-weight:800;color:#2563eb;margin:10px 0 6px;line-height:1;">{{ $activePrs }}</div>
        <div style="font-size:12px;color:#9ca3af;">Period: {{ now()->format('M Y') }}</div>
    </div>

    <div style="background:#fff;border:1px solid #e5e7eb;border-radius:14px;padding:22px 24px;">
        <div style="font-size:11px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.07em;">Awaiting Approval</div>
        <div style="font-size:32px;font-weight:800;color:#ea580c;margin:10px 0 6px;line-height:1;">{{ $awaitingApproval }}</div>
        <div style="font-size:12px;color:#9ca3af;">Needs action</div>
    </div>

    <div style="background:#fff;border:1px solid #e5e7eb;border-radius:14px;padding:22px 24px;">
        <div style="font-size:11px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.07em;">In Process</div>
        <div style="font-size:32px;font-weight:800;color:#0284c7;margin:10px 0 6px;line-height:1;">{{ $inProcess }}</div>
        <div style="font-size:12px;color:#9ca3af;">Purchasing verification</div>
    </div>

    <div style="background:#fff;border:1px solid #e5e7eb;border-radius:14px;padding:22px 24px;">
        <div style="font-size:11px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.07em;">Completed This Month</div>
        <div style="font-size:32px;font-weight:800;color:#16a34a;margin:10px 0 6px;line-height:1;">{{ $completedMonth }}</div>
        <div style="font-size:12px;color:#9ca3af;">PR fulfilled</div>
    </div>

</div>

{{-- ═══ PURCHASE REQUESTS TABLE ═══ --}}
<div style="background:#fff;border:1px solid #e5e7eb;border-radius:14px;margin-bottom:24px;">

    <div style="display:flex;align-items:center;justify-content:space-between;padding:18px 24px;border-bottom:1px solid #f3f4f6;">
        <span style="font-size:15px;font-weight:700;color:#111827;">Purchase Requests (PR)</span>
        <a href="{{ route('purchase_requests.create') }}"
           style="display:inline-flex;align-items:center;gap:6px;padding:8px 16px;background:#111827;color:#fff;border-radius:8px;font-size:13px;font-weight:600;text-decoration:none;"
           onmouseover="this.style.background='#1f2937'" onmouseout="this.style.background='#111827'">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14" stroke-linecap="round"/></svg>
            New Request
        </a>
    </div>

    <div style="overflow-x:auto;">
        <table style="width:100%;border-collapse:collapse;font-size:13.5px;">
            <thead>
                <tr style="background:#f9fafb;">
                    <th style="padding:10px 24px;text-align:left;font-size:11px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.06em;white-space:nowrap;">DOC NO.</th>
                    <th style="padding:10px 16px;text-align:left;font-size:11px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.06em;white-space:nowrap;">REQUESTED DATE</th>
                    <th style="padding:10px 16px;text-align:left;font-size:11px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.06em;">DESCRIPTION</th>
                    <th style="padding:10px 16px;text-align:left;font-size:11px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.06em;">QTY</th>
                    <th style="padding:10px 16px;text-align:left;font-size:11px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.06em;">STATUS</th>
                    <th style="padding:10px 16px;text-align:left;font-size:11px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.06em;white-space:nowrap;">LAST UPDATE</th>
                    <th style="padding:10px 16px;text-align:left;font-size:11px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.06em;">ACTION</th>
                </tr>
            </thead>
            <tbody>
                @forelse($requests as $pr)
                @php
                    [$statusLabel, $statusBg, $statusText, $statusDot] =
                        $statusCfg[$pr->status] ?? [ucfirst(str_replace('_',' ',$pr->status)), '#f3f4f6', '#374151', '#9ca3af'];

                    $reqDate = \Carbon\Carbon::parse($pr->need_date ?? $pr->created_at);

                    $upd = $pr->updated_at;
                    if ($upd->isToday())          $lastUpdate = 'Today, '     . $upd->format('H:i');
                    elseif ($upd->isYesterday())  $lastUpdate = 'Yesterday, ' . $upd->format('H:i');
                    else                          $lastUpdate = $upd->format('d M') . ', ' . $upd->format('H:i');

                    $units    = $pr->items->pluck('unit')->unique();
                    $totalQty = $pr->items->sum('quantity');
                    $qtyLabel = $units->count() === 1
                        ? $totalQty . ' ' . $units->first()
                        : $pr->items->count() . ' Items';
                @endphp
                <tr style="border-bottom:1px solid #f3f4f6;" onmouseover="this.style.background='#fafafa'" onmouseout="this.style.background='transparent'">

                    <td style="padding:14px 24px;">
                        <span style="font-family:'Courier New',monospace;font-size:13px;font-weight:600;color:#111827;">{{ $pr->document_number }}</span>
                    </td>

                    <td style="padding:14px 16px;color:#6b7280;font-size:13px;white-space:nowrap;">
                        {{ $reqDate->format('d M Y') }}
                    </td>

                    <td style="padding:14px 16px;max-width:240px;">
                        <div style="font-size:13.5px;font-weight:500;color:#111827;">{{ $pr->title }}</div>
                        <div style="font-size:12px;color:#9ca3af;margin-top:2px;">{{ $pr->plant }}</div>
                    </td>

                    <td style="padding:14px 16px;font-size:13px;color:#374151;white-space:nowrap;">
                        {{ $qtyLabel }}
                    </td>

                    <td style="padding:14px 16px;">
                        <span style="display:inline-flex;align-items:center;gap:5px;padding:4px 10px;border-radius:999px;background:{{ $statusBg }};font-size:12px;font-weight:600;color:{{ $statusText }};white-space:nowrap;">
                            <span style="width:6px;height:6px;border-radius:50%;background:{{ $statusDot }};flex-shrink:0;"></span>
                            {{ $statusLabel }}
                        </span>
                    </td>

                    <td style="padding:14px 16px;font-size:13px;color:#6b7280;white-space:nowrap;">
                        {{ $lastUpdate }}
                    </td>

                    <td style="padding:14px 16px;">
                        <button onclick="openDetailModal({{ $pr->id }})"
                            style="padding:5px 14px;border:1px solid #d1d5db;border-radius:7px;background:#fff;font-size:13px;font-weight:500;color:#374151;cursor:pointer;"
                            onmouseover="this.style.background='#f9fafb'" onmouseout="this.style.background='#fff'">
                            Detail
                        </button>
                    </td>

                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align:center;padding:40px 24px;color:#9ca3af;font-size:13.5px;">
                        No purchase requests yet.
                        <a href="{{ route('purchase_requests.create') }}" style="color:#3b5bdb;font-weight:600;text-decoration:none;">Create your first PR →</a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- ═══ RECENT VENDOR HISTORY ═══ --}}
<div style="background:#fff;border:1px solid #e5e7eb;border-radius:14px;">

    <div style="display:flex;align-items:center;justify-content:space-between;padding:18px 24px;border-bottom:1px solid #f3f4f6;">
        <span style="font-size:15px;font-weight:700;color:#111827;">Recent Vendor History</span>
        <a href="{{ route('history.index') }}"
           style="font-size:13px;font-weight:500;color:#6b7280;text-decoration:none;"
           onmouseover="this.style.color='#111827'" onmouseout="this.style.color='#6b7280'">
            View All →
        </a>
    </div>

    <div style="overflow-x:auto;">
        <table style="width:100%;border-collapse:collapse;font-size:13.5px;">
            <thead>
                <tr style="background:#f9fafb;">
                    <th style="padding:10px 24px;text-align:left;font-size:11px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.06em;">DOC NO.</th>
                    <th style="padding:10px 16px;text-align:left;font-size:11px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.06em;">VENDOR</th>
                    <th style="padding:10px 16px;text-align:left;font-size:11px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.06em;">DEPARTMENT</th>
                    <th style="padding:10px 16px;text-align:left;font-size:11px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.06em;">TOTAL VALUE</th>
                    <th style="padding:10px 16px;text-align:left;font-size:11px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.06em;">STATUS</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentHistory as $h)
                @php
                    $vendorName = optional($h->vendor)->vendor_name ?? optional($h->vendor)->vendor_name ?? '—';
                    $vendorCity = optional($h->vendor)->location ?? '';
                    $docNo      = optional($h->rfq)->purchaseRequest->document_number ?? '—';
                    $dept       = optional($h->rfq)->purchaseRequest->department ?? '—';
                    $totalVal   = optional($h->vendorSelection)->total_value ?? 0;

                    $deptColors = [
                        'Maintenance'  => ['#e0f2fe', '#0369a1'],
                        'Produksi'     => ['#dcfce7', '#15803d'],
                        'Production'   => ['#dcfce7', '#15803d'],
                        'IT'           => ['#ede9fe', '#7c3aed'],
                        'IT & Digital' => ['#ede9fe', '#7c3aed'],
                        'Finance'      => ['#fef9c3', '#92400e'],
                        'HR'           => ['#fce7f3', '#be185d'],
                    ];
                    [$deptBg, $deptText] = $deptColors[$dept] ?? ['#f1f5f9', '#475569'];
                @endphp
                <tr style="border-bottom:1px solid #f3f4f6;" onmouseover="this.style.background='#fafafa'" onmouseout="this.style.background='transparent'">

                    <td style="padding:14px 24px;">
                        <span style="font-family:'Courier New',monospace;font-size:13px;font-weight:600;color:#111827;">{{ $docNo }}</span>
                    </td>

                    <td style="padding:14px 16px;">
                        <div style="font-size:13.5px;font-weight:500;color:#111827;">{{ $vendorName }}</div>
                        <div style="font-size:12px;color:#9ca3af;margin-top:2px;">{{ $vendorCity }}</div>
                    </td>

                    <td style="padding:14px 16px;">
                        <span style="display:inline-flex;align-items:center;padding:3px 10px;border-radius:6px;font-size:12px;font-weight:600;background:{{ $deptBg }};color:{{ $deptText }};">
                            {{ $dept }}
                        </span>
                    </td>

                    <td style="padding:14px 16px;font-size:13.5px;font-weight:600;color:#111827;">
                        Rp {{ number_format($totalVal, 0, ',', '.') }}
                    </td>

                    <td style="padding:14px 16px;">
                        {{-- Vendor history selalu completed --}}
                        <span style="display:inline-flex;align-items:center;gap:5px;padding:4px 10px;border-radius:999px;background:#eff6ff;font-size:12px;font-weight:600;color:#1d4ed8;">
                            <span style="width:6px;height:6px;border-radius:50%;background:#3b82f6;flex-shrink:0;"></span>
                            Completed
                        </span>
                    </td>

                </tr>
                @empty
                <tr>
                    <td colspan="5" style="text-align:center;padding:32px 24px;color:#9ca3af;font-size:13.5px;">
                        No vendor history yet.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>

{{-- ═══ DETAIL MODAL ═══ --}}
<div id="detail-modal"
     style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:200;align-items:center;justify-content:center;padding:24px;">
    <div style="background:#fff;border-radius:16px;width:100%;max-width:680px;max-height:88vh;display:flex;flex-direction:column;box-shadow:0 8px 40px rgba(0,0,0,.12);">

        <div style="padding:20px 24px;border-bottom:1px solid #f3f4f6;display:flex;align-items:flex-start;justify-content:space-between;gap:12px;">
            <div>
                <div id="modal-pr-title" style="font-size:16px;font-weight:700;color:#111827;"></div>
                <div id="modal-pr-sub" style="font-size:13px;color:#3b5bdb;font-weight:500;margin-top:3px;"></div>
            </div>
            <button onclick="closeDetailModal()"
                style="background:none;border:none;cursor:pointer;color:#9ca3af;padding:4px;border-radius:6px;"
                onmouseover="this.style.background='#f3f4f6'" onmouseout="this.style.background='none'">
                <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M18 6L6 18M6 6l12 12" stroke-linecap="round"/></svg>
            </button>
        </div>

        <div id="modal-body-content" style="padding:20px 24px;overflow-y:auto;flex:1;font-size:13.5px;"></div>

        <div style="padding:16px 24px;border-top:1px solid #f3f4f6;display:flex;justify-content:flex-end;gap:10px;">
            <button onclick="closeDetailModal()"
                style="padding:8px 18px;border:1px solid #d1d5db;border-radius:8px;background:#fff;font-size:13.5px;font-weight:600;color:#374151;cursor:pointer;">
                Close
            </button>
            <button id="modal-select-vendor-btn"
                style="display:inline-flex;align-items:center;gap:6px;padding:8px 18px;background:#111827;color:#fff;border-radius:8px;font-size:13.5px;font-weight:600;border:none;cursor:pointer;">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                Select Vendor
            </button>
        </div>

    </div>
</div>

<script>
const prData = @json($requests->load('items','user')->keyBy('id'));

// Status config (JS mirror of PHP above)
const statusCfg = {
    awaiting_approval: ['Awaiting Approval', '#fff7ed', '#c2410c', '#f97316'],
    in_process:        ['In Process',        '#f0f9ff', '#0369a1', '#0ea5e9'],
    approved:          ['Approved',           '#f0fdf4', '#15803d', '#22c55e'],
    completed:         ['Completed',          '#eff6ff', '#1d4ed8', '#3b82f6'],
};

function openDetailModal(id) {
    const pr = prData[id];
    if (!pr) return;

    document.getElementById('modal-pr-title').textContent = pr.title || 'Purchase Request';
    document.getElementById('modal-pr-sub').textContent   = (pr.document_number || '') + ' | ' + (pr.plant || '');

    const items = pr.items || [];
    const itemRows = items.map((item, i) => `
        <tr>
            <td style="padding:10px 14px;border-bottom:1px solid #f9fafb;color:#6b7280;">${i+1}</td>
            <td style="padding:10px 14px;border-bottom:1px solid #f9fafb;font-family:'Courier New',monospace;font-size:12px;color:#3b5bdb;font-weight:600;">${item.item_code || '—'}</td>
            <td style="padding:10px 14px;border-bottom:1px solid #f9fafb;font-weight:500;color:#111827;">${item.name}</td>
            <td style="padding:10px 14px;border-bottom:1px solid #f9fafb;font-size:12px;color:#6b7280;">${item.specification || '—'}</td>
            <td style="padding:10px 14px;border-bottom:1px solid #f9fafb;color:#374151;">${item.quantity}</td>
            <td style="padding:10px 14px;border-bottom:1px solid #f9fafb;color:#374151;">${item.unit}</td>
            <td style="padding:10px 14px;border-bottom:1px solid #f9fafb;color:#9ca3af;">—</td>
            <td style="padding:10px 14px;border-bottom:1px solid #f9fafb;color:#9ca3af;">—</td>
        </tr>
    `).join('') || '<tr><td colspan="8" style="padding:20px;text-align:center;color:#9ca3af;">No items found.</td></tr>';

    const subDate = pr.created_at
        ? new Date(pr.created_at).toLocaleDateString('id-ID', {day:'2-digit',month:'short',year:'numeric'})
        : '—';

    const [sLabel, sBg, sText, sDot] = statusCfg[pr.status] || [pr.status, '#f3f4f6', '#374151', '#9ca3af'];

    document.getElementById('modal-body-content').innerHTML = `
        <div style="background:#f9fafb;border-radius:10px;padding:16px 18px;margin-bottom:20px;">
            <div style="font-size:10.5px;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.08em;margin-bottom:14px;">Request Information</div>
            <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:14px;">
                <div>
                    <div style="font-size:10.5px;color:#9ca3af;text-transform:uppercase;font-weight:600;margin-bottom:4px;">Submission Date</div>
                    <div style="font-size:13.5px;font-weight:500;color:#111827;">${subDate}</div>
                </div>
                <div>
                    <div style="font-size:10.5px;color:#9ca3af;text-transform:uppercase;font-weight:600;margin-bottom:4px;">Department</div>
                    <div style="font-size:13.5px;font-weight:500;color:#111827;">${pr.department || '—'}</div>
                </div>
                <div>
                    <div style="font-size:10.5px;color:#9ca3af;text-transform:uppercase;font-weight:600;margin-bottom:4px;">Requested By</div>
                    <div style="font-size:13.5px;font-weight:500;color:#111827;">${pr.user?.name || 'You'}</div>
                </div>
                <div>
                    <div style="font-size:10.5px;color:#9ca3af;text-transform:uppercase;font-weight:600;margin-bottom:4px;">Priority</div>
                    <div style="font-size:13.5px;font-weight:500;color:#111827;">${pr.priority ? (pr.priority.charAt(0).toUpperCase()+pr.priority.slice(1)) : 'Normal'}</div>
                </div>
                <div>
                    <div style="font-size:10.5px;color:#9ca3af;text-transform:uppercase;font-weight:600;margin-bottom:4px;">Plant</div>
                    <div style="font-size:13.5px;font-weight:500;color:#111827;">${pr.plant || '—'}</div>
                </div>
                <div>
                    <div style="font-size:10.5px;color:#9ca3af;text-transform:uppercase;font-weight:600;margin-bottom:4px;">Status</div>
                    <span style="display:inline-flex;align-items:center;gap:5px;padding:4px 10px;border-radius:999px;background:${sBg};font-size:12px;font-weight:600;color:${sText};">
                        <span style="width:6px;height:6px;border-radius:50%;background:${sDot};flex-shrink:0;"></span>
                        ${sLabel}
                    </span>
                </div>
            </div>
        </div>

        <div style="font-size:10.5px;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.08em;margin-bottom:10px;">Item List</div>
        <div style="border:1px solid #e5e7eb;border-radius:10px;overflow:hidden;margin-bottom:20px;">
            <table style="width:100%;border-collapse:collapse;font-size:13px;">
                <thead>
                    <tr style="background:#f9fafb;">
                        <th style="padding:9px 14px;text-align:left;font-size:10.5px;color:#9ca3af;font-weight:700;text-transform:uppercase;">NO</th>
                        <th style="padding:9px 14px;text-align:left;font-size:10.5px;color:#9ca3af;font-weight:700;text-transform:uppercase;">ITEM ID</th>
                        <th style="padding:9px 14px;text-align:left;font-size:10.5px;color:#9ca3af;font-weight:700;text-transform:uppercase;">ITEM NAME</th>
                        <th style="padding:9px 14px;text-align:left;font-size:10.5px;color:#9ca3af;font-weight:700;text-transform:uppercase;">SPEC</th>
                        <th style="padding:9px 14px;text-align:left;font-size:10.5px;color:#9ca3af;font-weight:700;text-transform:uppercase;">QTY</th>
                        <th style="padding:9px 14px;text-align:left;font-size:10.5px;color:#9ca3af;font-weight:700;text-transform:uppercase;">UNIT</th>
                        <th style="padding:9px 14px;text-align:left;font-size:10.5px;color:#9ca3af;font-weight:700;text-transform:uppercase;">UNIT PRICE (RP)</th>
                        <th style="padding:9px 14px;text-align:left;font-size:10.5px;color:#9ca3af;font-weight:700;text-transform:uppercase;">TOTAL (RP)</th>
                    </tr>
                </thead>
                <tbody>${itemRows}</tbody>
                <tfoot>
                    <tr style="background:#f9fafb;">
                        <td colspan="7" style="padding:10px 14px;text-align:right;font-size:12px;font-weight:700;color:#374151;border-top:1px solid #e5e7eb;">Total Request Value</td>
                        <td style="padding:10px 14px;font-weight:700;color:#374151;border-top:1px solid #e5e7eb;">Rp —</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div style="font-size:10.5px;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.08em;margin-bottom:10px;">Activity Log</div>
        <div style="display:flex;flex-direction:column;gap:10px;">
            <div style="display:flex;gap:10px;align-items:flex-start;">
                <div style="width:7px;height:7px;background:#6b7280;border-radius:50%;margin-top:5px;flex-shrink:0;"></div>
                <div>
                    <div style="font-size:13px;font-weight:500;color:#111827;">PR created and submitted to supervisor</div>
                    <div style="font-size:12px;color:#9ca3af;margin-top:2px;">${subDate} — ${pr.user?.name || 'You'}</div>
                </div>
            </div>
            <div style="display:flex;gap:10px;align-items:flex-start;">
                <div style="width:7px;height:7px;background:#9ca3af;border-radius:50%;margin-top:5px;flex-shrink:0;"></div>
                <div>
                    <div style="font-size:13px;font-weight:500;color:#111827;">Notification sent to Supervisor</div>
                    <div style="font-size:12px;color:#9ca3af;margin-top:2px;">${subDate} — System</div>
                </div>
            </div>
        </div>
    `;

    const modal = document.getElementById('detail-modal');
    modal.style.display = 'flex';
}

function closeDetailModal() {
    document.getElementById('detail-modal').style.display = 'none';
}

document.getElementById('detail-modal').addEventListener('click', function(e) {
    if (e.target === this) closeDetailModal();
});
</script>

@endsection