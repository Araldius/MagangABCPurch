@extends('layouts.app')
@php $pageTitle = 'Admin Dashboard'; @endphp

@section('content')
@php $firstName = explode(' ', auth()->user()->name)[0]; @endphp

<div class="page-header">
    <div class="page-title">Selamat datang, {{ $firstName }}</div>
    <div class="page-desc">Ringkasan seluruh aktivitas pengadaan — {{ now()->format('d M Y') }}</div>
</div>

{{-- Stat Cards --}}
<div class="stat-grid">
    <div class="stat-card">
        <div class="stat-label">Total PR</div>
        <div class="stat-value blue">{{ $totalRequests }}</div>
        <div class="stat-sub">Semua periode</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">PR Menunggu</div>
        <div class="stat-value orange">{{ $pendingRequests }}</div>
        <div class="stat-sub">Perlu tindakan</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">RFQ Terbuka</div>
        <div class="stat-value" style="color:#0369a1;">{{ $openRfqs }}</div>
        <div class="stat-sub">Aktif saat ini</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Selesai Bulan Ini</div>
        <div class="stat-value green">{{ $completedMonth }}</div>
        <div class="stat-sub">PR terfulfill</div>
    </div>
</div>

{{-- Quick Actions --}}
<div style="display:grid;grid-template-columns:2fr 1fr;gap:20px;margin-top:24px;">
    {{-- Latest Purchase Requests --}}
    <div class="card">
        <div class="card-header">
            <div>
                <div class="card-title">Purchase Request Terbaru</div>
                <div class="card-desc">10 PR terakhir dari semua departemen.</div>
            </div>
            <a href="{{ route('pr.list') }}" class="btn btn-outline btn-sm">Lihat Semua</a>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>No. Dokumen</th>
                        <th>Requester</th>
                        <th>Departemen</th>
                        <th>Status</th>
                        <th>Tgl Diajukan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($latestRequests as $pr)
                    @php
                        $badgeClass = match($pr->status) {
                            'submitted'=> 'badge-awaiting',
                            'rfq_open'         => 'badge-rfq',
                            'completed'        => 'badge-completed',
                            'cancelled'        => 'badge-cancelled',
                            default            => 'badge-inprocess',
                        };
                        $badgeLabel = match($pr->status) {
                            'vendor_selection'          => 'Vendor Selection',
                            'submitted'=> 'Awaiting',
                            'rfq_open'         => 'RFQ Open',
                            'completed'        => 'Completed',
                            'cancelled'        => 'Cancelled',
                            default            => ucfirst($pr->status),
                        };
                    @endphp
                    <tr>
                        <td class="td-doc">{{ $pr->document_number }}</td>
                        <td>
                            <div style="font-weight:500;">{{ $pr->user->name ?? '—' }}</div>
                        </td>
                        <td><span class="tag tag-blue">{{ $pr->department ?? '—' }}</span></td>
                        <td><span class="badge {{ $badgeClass }}">{{ $badgeLabel }}</span></td>
                        <td class="text-muted text-sm">
                            {{ $pr->submission_date ? \Carbon\Carbon::parse($pr->submission_date)->format('d M Y') : \Carbon\Carbon::parse($pr->created_at)->format('d M Y') }}
                        </td>
                        <td>
                            @if($pr->rfqs && $pr->rfqs->where('status','open')->count())
                                <a href="{{ route('quotations.status', $pr->rfqs->where('status','open')->first()) }}" class="btn btn-outline btn-sm">RFQ</a>
                            @else
                                <button class="btn btn-ghost btn-sm" onclick="openAdminModal({{ $pr->id }})">Detail</button>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" style="text-align:center;padding:32px;color:var(--text-muted);">Belum ada PR.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Right Panel --}}
    <div style="display:flex;flex-direction:column;gap:20px;">
        {{-- Quick Actions --}}
        <div class="card">
            <div class="card-header">
                <div class="card-title">Menu Cepat</div>
            </div>
            <div class="card-body" style="display:flex;flex-direction:column;gap:10px;">
                <a href="{{ route('rfqs.create') }}" class="btn btn-primary" style="width:100%;justify-content:center;">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14" stroke-linecap="round"/></svg>
                    Buat RFQ Baru
                </a>
                <a href="{{ route('vendors.list') }}" class="btn btn-outline" style="width:100%;justify-content:center;">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    Kelola Vendor
                </a>
                <a href="{{ route('history.orders') }}" class="btn btn-outline" style="width:100%;justify-content:center;">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    Riwayat Pengadaan
                </a>
            </div>
        </div>

        {{-- Recent Vendor Activity --}}
        <div class="card" style="flex:1;">
            <div class="card-header">
                <div class="card-title">Aktivitas Vendor Terkini</div>
            </div>
            <div class="card-body" style="padding:12px 0 0;">
                @forelse($recentHistory->take(5) as $h)
                <div style="display:flex;gap:10px;align-items:flex-start;padding:10px 20px;border-bottom:1px solid var(--border);">
                    <div style="width:34px;height:34px;border-radius:50%;background:#eef2ff;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:12px;font-weight:700;color:#3b5bdb;">
                        {{ strtoupper(substr($h->vendor->name ?? 'V', 0, 2)) }}
                    </div>
                    <div style="flex:1;min-width:0;">
                        <div style="font-size:13px;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $h->vendor->name ?? '—' }}</div>
                        <div style="font-size:12px;color:var(--text-muted);">{{ $h->action }}</div>
                        <div style="font-size:11px;color:var(--text-light);margin-top:2px;">{{ \Carbon\Carbon::parse($h->action_date)->diffForHumans() }}</div>
                    </div>
                    <span class="badge badge-completed" style="font-size:11px;">Done</span>
                </div>
                @empty
                <div style="padding:20px;text-align:center;color:var(--text-muted);font-size:13px;">Belum ada aktivitas.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection