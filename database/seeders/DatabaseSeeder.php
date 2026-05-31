<?php

namespace Database\Seeders;

use App\Models\History;
use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestItem;
use App\Models\ServiceRequest;
use App\Models\ServiceRequestJob;
use App\Models\ServiceRequestItem;
use App\Models\Quotation;
use App\Models\QuotationDetail;
use App\Models\QuotationPeriod;
use App\Models\QuotationSummary;
use App\Models\Rfq;
use App\Models\SelectionItem;
use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorQuotation;
use App\Models\VendorSelection;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── Wipe all tables in safe order (FK checks off) ──────────────────
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('history')->truncate();
        DB::table('selection_items')->truncate();
        DB::table('vendor_selections')->truncate();
        DB::table('quotation_summaries')->truncate();
        DB::table('quotation_details')->truncate();
        DB::table('quotations')->truncate();
        DB::table('quotation_periods')->truncate();
        DB::table('vendor_quotations')->truncate();
        DB::table('rfqs')->truncate();
        DB::table('service_request_items')->truncate();
        DB::table('service_request_jobs')->truncate();
        DB::table('service_requests')->truncate();
        DB::table('purchase_request_items')->truncate();
        DB::table('purchase_requests')->truncate();
        DB::table('vendors')->truncate();
        DB::table('users')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
        // ───────────────────────────────────────────────────────────────────
        // 1. SETUP USERS (1 Admin, 1 User)
        $admin = User::create([
            'name' => 'Admin Purchasing', 'email' => 'admin@company.com', 
            'password' => Hash::make('password'), 'role' => 'purchasing', 'department' => 'Procurement'
        ]);

        $user = User::create([
            'name' => 'John Requester', 'email' => 'user@company.com', 
            'password' => Hash::make('password'), 'role' => 'requester', 'department' => 'Operations'
        ]);

        // 2. SETUP VENDORS
        $v1 = Vendor::create(['vendor_name' => 'PT Tekno Mandiri', 'location' => 'Jakarta', 'contact' => '021-111111', 'status' => 'active']);
        $v2 = Vendor::create(['vendor_name' => 'CV Maju Komputer', 'location' => 'Bandung', 'contact' => '022-222222', 'status' => 'active']);
        $v3 = Vendor::create(['vendor_name' => 'PT Karya Jasa', 'location' => 'Surabaya', 'contact' => '031-333333', 'status' => 'active']);
        $v4 = Vendor::create(['vendor_name' => 'CV Bangun Nusantara', 'location' => 'Semarang', 'contact' => '024-444444', 'status' => 'active']);

        // 3. SKENARIO 1: GOODS - COMPLETED (SPLIT PO)
        $pr1 = PurchaseRequest::create([
            'user_id' => $user->id, 'document_number' => 'PR-2026-0001', 'title' => 'Pengadaan Perangkat IT Baru', 'department' => 'Operations', 'priority' => 'high', 'plant' => 'Cikarang', 
            'submission_date' => now()->subDays(30), 'requested_date' => now()->subDays(29), 'need_date' => now()->addDays(5), 'note' => 'Untuk karyawan baru', 'status' => 'completed'
        ]);
        
        $pr1_i1 = PurchaseRequestItem::create(['purchase_request_id' => $pr1->id, 'item_id' => 'IT-001', 'item_name' => 'Laptop Thinkpad', 'quantity' => 2, 'unit' => 'Unit', 'specification' => 'Core i7']);
        $pr1_i2 = PurchaseRequestItem::create(['purchase_request_id' => $pr1->id, 'item_id' => 'IT-002', 'item_name' => 'Mouse Wireless', 'quantity' => 5, 'unit' => 'Pcs', 'specification' => 'M220']);

        $rfq1 = Rfq::create(['purchase_request_id' => $pr1->id, 'rfq_number' => 'RFQ-2026-001', 'is_sent_to_user' => true, 'sent_to_user_at' => now()->subDays(25), 'status' => 'closed', 'opened_at' => now()->subDays(28), 'closed_at' => now()->subDays(26)]);
        QuotationPeriod::create(['rfq_id' => $rfq1->id, 'round' => 1, 'start_date' => now()->subDays(28), 'end_date' => now()->subDays(26), 'status' => 'closed']);
        
        // V1 Offer
        VendorQuotation::create(['rfq_id' => $rfq1->id, 'vendor_id' => $v1->id, 'status' => 'submitted', 'submitted_at' => now()->subDays(27)]);
        $quot1_v1 = Quotation::create(['rfq_id' => $rfq1->id, 'vendor_id' => $v1->id, 'total_price' => 30500000, 'status' => 'finalized']);
        $qd1_v1_i1 = QuotationDetail::create(['quotation_id' => $quot1_v1->id, 'purchase_request_item_id' => $pr1_i1->id, 'offered_price_per_item' => 15000000, 'offered_quantity' => 2]);
        $qd1_v1_i2 = QuotationDetail::create(['quotation_id' => $quot1_v1->id, 'purchase_request_item_id' => $pr1_i2->id, 'offered_price_per_item' => 100000, 'offered_quantity' => 5]);
        $qs1_v1_i1 = QuotationSummary::create(['rfq_id' => $rfq1->id, 'quotation_detail_id' => $qd1_v1_i1->id, 'is_sent_to_user' => true]);
        $qs1_v1_i2 = QuotationSummary::create(['rfq_id' => $rfq1->id, 'quotation_detail_id' => $qd1_v1_i2->id, 'is_sent_to_user' => true]);

        // V2 Offer
        VendorQuotation::create(['rfq_id' => $rfq1->id, 'vendor_id' => $v2->id, 'status' => 'submitted', 'submitted_at' => now()->subDays(27)]);
        $quot1_v2 = Quotation::create(['rfq_id' => $rfq1->id, 'vendor_id' => $v2->id, 'total_price' => 32400000, 'status' => 'finalized']);
        $qd1_v2_i1 = QuotationDetail::create(['quotation_id' => $quot1_v2->id, 'purchase_request_item_id' => $pr1_i1->id, 'offered_price_per_item' => 16000000, 'offered_quantity' => 2]); 
        $qd1_v2_i2 = QuotationDetail::create(['quotation_id' => $quot1_v2->id, 'purchase_request_item_id' => $pr1_i2->id, 'offered_price_per_item' => 80000, 'offered_quantity' => 5]); 
        $qs1_v2_i1 = QuotationSummary::create(['rfq_id' => $rfq1->id, 'quotation_detail_id' => $qd1_v2_i1->id, 'is_sent_to_user' => true]);
        $qs1_v2_i2 = QuotationSummary::create(['rfq_id' => $rfq1->id, 'quotation_detail_id' => $qd1_v2_i2->id, 'is_sent_to_user' => true]);

        // Split Selection: Laptop dari V1, Mouse dari V2
        $sel1_v1 = VendorSelection::create(['rfq_id' => $rfq1->id, 'vendor_id' => $v1->id, 'quotation_id' => $quot1_v1->id, 'decision_notes' => 'Pilih V1', 'decided_at' => now()->subDays(24)]);
        SelectionItem::create(['vendor_selection_id' => $sel1_v1->id, 'quotation_summary_id' => $qs1_v1_i1->id, 'purchase_request_item_id' => $pr1_i1->id, 'final_price_per_item' => 15000000, 'final_quantity' => 2]);
        
        $sel1_v2 = VendorSelection::create(['rfq_id' => $rfq1->id, 'vendor_id' => $v2->id, 'quotation_id' => $quot1_v2->id, 'decision_notes' => 'Pilih V2', 'decided_at' => now()->subDays(24)]);
        SelectionItem::create(['vendor_selection_id' => $sel1_v2->id, 'quotation_summary_id' => $qs1_v2_i2->id, 'purchase_request_item_id' => $pr1_i2->id, 'final_price_per_item' => 80000, 'final_quantity' => 5]);

        // 4. SKENARIO 2: GOODS - IN PROCESS (WAITING SELECTION)
        $pr2 = PurchaseRequest::create([
            'user_id' => $user->id, 'document_number' => 'PR-2026-0002', 'title' => 'Restock Alat Tulis Kantor', 'department' => 'Operations', 'priority' => 'normal', 'plant' => 'Cikarang', 
            'submission_date' => now()->subDays(10), 'requested_date' => now()->subDays(9), 'need_date' => now()->addDays(14), 'note' => 'Stok habis', 'status' => 'in_process'
        ]);
        
        $pr2_i1 = PurchaseRequestItem::create(['purchase_request_id' => $pr2->id, 'item_id' => 'ATK-01', 'item_name' => 'Kertas A4', 'quantity' => 100, 'unit' => 'Ream']);

        $rfq2 = Rfq::create(['purchase_request_id' => $pr2->id, 'rfq_number' => 'RFQ-2026-002', 'is_sent_to_user' => true, 'sent_to_user_at' => now()->subDays(1), 'status' => 'closed']);
        
        // Tawaran V1
        $quot2_v1 = Quotation::create(['rfq_id' => $rfq2->id, 'vendor_id' => $v1->id, 'total_price' => 4500000, 'status' => 'finalized']);
        $qd2_v1_i1 = QuotationDetail::create(['quotation_id' => $quot2_v1->id, 'purchase_request_item_id' => $pr2_i1->id, 'offered_price_per_item' => 45000, 'offered_quantity' => 100]);
        QuotationSummary::create(['rfq_id' => $rfq2->id, 'quotation_detail_id' => $qd2_v1_i1->id, 'is_sent_to_user' => true]);

        // Tawaran V2
        $quot2_v2 = Quotation::create(['rfq_id' => $rfq2->id, 'vendor_id' => $v2->id, 'total_price' => 4800000, 'status' => 'finalized']);
        $qd2_v2_i1 = QuotationDetail::create(['quotation_id' => $quot2_v2->id, 'purchase_request_item_id' => $pr2_i1->id, 'offered_price_per_item' => 48000, 'offered_quantity' => 100]);
        QuotationSummary::create(['rfq_id' => $rfq2->id, 'quotation_detail_id' => $qd2_v2_i1->id, 'is_sent_to_user' => true]);


        // 5. SKENARIO 3: SERVICE - IN PROCESS (WAITING SELECTION)
        $sr2 = ServiceRequest::create([
            'user_id' => $user->id, 'document_number' => 'SR-2026-0001', 'service_name' => 'Renovasi Pantry', 'submission_date' => now()->subDays(5), 'requested_date' => now()->subDays(4), 'plant' => 'Cikarang', 'status' => 'in_process'
        ]);

        $sr2_job1 = ServiceRequestJob::create(['service_request_id' => $sr2->id, 'job_description' => 'Pembongkaran & Pembuangan']);
        $sr2_i1 = ServiceRequestItem::create(['job_id' => $sr2_job1->id, 'item_name' => 'Jasa Bongkar Keramik', 'quantity' => 1, 'unit' => 'Lot']);
        
        $sr2_job2 = ServiceRequestJob::create(['service_request_id' => $sr2->id, 'job_description' => 'Pemasangan Interior Baru']);
        $sr2_i2 = ServiceRequestItem::create(['job_id' => $sr2_job2->id, 'item_name' => 'Pemasangan Kitchen Set', 'quantity' => 1, 'unit' => 'Set']);

        $rfq4 = Rfq::create(['service_request_id' => $sr2->id, 'rfq_number' => 'RFQ-SR-2026-002', 'is_sent_to_user' => true, 'sent_to_user_at' => now(), 'status' => 'closed']);
        
        // Tawaran V3 (Jasa)
        $quot4_v3 = Quotation::create(['rfq_id' => $rfq4->id, 'vendor_id' => $v3->id, 'total_price' => 10500000, 'status' => 'finalized']);
        $qd4_v3_i1 = QuotationDetail::create(['quotation_id' => $quot4_v3->id, 'service_request_item_id' => $sr2_i1->id, 'offered_price_per_item' => 1000000, 'offered_quantity' => 1]);
        $qd4_v3_i2 = QuotationDetail::create(['quotation_id' => $quot4_v3->id, 'service_request_item_id' => $sr2_i2->id, 'offered_price_per_item' => 9500000, 'offered_quantity' => 1]);
        QuotationSummary::create(['rfq_id' => $rfq4->id, 'quotation_detail_id' => $qd4_v3_i1->id, 'is_sent_to_user' => true]);
        QuotationSummary::create(['rfq_id' => $rfq4->id, 'quotation_detail_id' => $qd4_v3_i2->id, 'is_sent_to_user' => true]);

        // Tawaran V4 (Jasa Lebih Murah)
        $quot4_v4 = Quotation::create(['rfq_id' => $rfq4->id, 'vendor_id' => $v4->id, 'total_price' => 9100000, 'status' => 'finalized']);
        $qd4_v4_i1 = QuotationDetail::create(['quotation_id' => $quot4_v4->id, 'service_request_item_id' => $sr2_i1->id, 'offered_price_per_item' => 800000, 'offered_quantity' => 1]);
        $qd4_v4_i2 = QuotationDetail::create(['quotation_id' => $quot4_v4->id, 'service_request_item_id' => $sr2_i2->id, 'offered_price_per_item' => 8300000, 'offered_quantity' => 1]);
        QuotationSummary::create(['rfq_id' => $rfq4->id, 'quotation_detail_id' => $qd4_v4_i1->id, 'is_sent_to_user' => true]);
        QuotationSummary::create(['rfq_id' => $rfq4->id, 'quotation_detail_id' => $qd4_v4_i2->id, 'is_sent_to_user' => true]);

        // ==========================================
        // 6. HISTORY UMUM
        // ==========================================
        History::create(['user_id' => $user->id, 'action' => 'User Account Created', 'notes' => 'Akun User John Requester dibuat.', 'action_date' => now()]);
    }
}