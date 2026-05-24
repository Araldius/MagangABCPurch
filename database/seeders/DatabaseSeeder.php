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

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. USERS
        $admin = User::factory()->create(['name' => 'Admin Purchasing', 'email' => 'admin@purchasing.local', 'password' => bcrypt('password'), 'role' => 'purchasing', 'department' => 'Procurement']);
        $req1  = User::factory()->create(['name' => 'Budi Santoso', 'email' => 'budi@company.local', 'password' => bcrypt('password'), 'role' => 'requester', 'department' => 'Operations']);
        $req2  = User::factory()->create(['name' => 'John Smith', 'email' => 'john.smith@company.local', 'password' => bcrypt('password'), 'role' => 'requester', 'department' => 'Engineering']);
        $req3  = User::factory()->create(['name' => 'Sarah Johnson', 'email' => 'sarah.johnson@company.local', 'password' => bcrypt('password'), 'role' => 'requester', 'department' => 'Maintenance']);

        // 2. VENDORS
        $v1 = Vendor::create(['vendor_name' => 'PT Sumber Mandiri', 'location' => 'Jakarta',  'contact' => '021-5551234', 'status' => 'active']);
        $v2 = Vendor::create(['vendor_name' => 'PT Prima Niaga',    'location' => 'Bandung',  'contact' => '022-5559876', 'status' => 'active']);
        $v3 = Vendor::create(['vendor_name' => 'CV Karya Teknik',   'location' => 'Surabaya', 'contact' => '031-5554321', 'status' => 'active']);

        // 3. SKENARIO PURCHASE REQUESTS (GOODS)
        
        /* TEST CASE 1: PR Normal Selesai */
        $pr1 = PurchaseRequest::create(['user_id' => $req1->id, 'document_number' => 'PR-2026-0101-001', 'title' => 'Pengadaan ATK Bulanan', 'department' => 'Operations', 'priority' => 'high', 'plant' => 'Cikarang', 'submission_date' => now()->subDays(20), 'requested_date' => now()->subDays(18), 'need_date' => now()->addDays(5), 'note' => 'Urgent for Q2', 'status' => 'completed']);
        
        $i1 = PurchaseRequestItem::create(['purchase_request_id' => $pr1->id, 'item_id' => 'OFF-001', 'item_name' => 'Kertas A4 80 GSM', 'quantity' => 50, 'unit' => 'Ream', 'specification' => 'Sinar Dunia / PaperOne', 'item_notes' => 'Tolong dipacking rapi']);
        $i1_2 = PurchaseRequestItem::create(['purchase_request_id' => $pr1->id, 'item_id' => 'OFF-002', 'item_name' => 'Tinta Printer Epson 003', 'quantity' => 20, 'unit' => 'Botol', 'specification' => 'Black, Original', 'item_notes' => null]);
        
        $rfq1 = Rfq::create(['purchase_request_id' => $pr1->id, 'rfq_number' => 'RFQ-2026-0102-001', 'is_sent_to_user' => true, 'sent_to_user_at' => now()->subDays(15), 'status' => 'closed']);
        QuotationPeriod::create(['rfq_id' => $rfq1->id, 'round' => 1, 'start_date' => now()->subDays(19), 'end_date' => now()->subDays(12), 'status' => 'closed']);
        VendorQuotation::create(['rfq_id' => $rfq1->id, 'vendor_id' => $v1->id, 'status' => 'submitted', 'submitted_at' => now()->subDays(17)]);
        
        $quot1 = Quotation::create(['rfq_id' => $rfq1->id, 'vendor_id' => $v1->id, 'total_price' => 2500000, 'status' => 'finalized']);
        $qd1 = QuotationDetail::create(['quotation_id' => $quot1->id, 'purchase_request_item_id' => $i1->id, 'offered_price_per_item' => 30000, 'offered_quantity' => 50]);
        
        $qs1 = QuotationSummary::create(['rfq_id' => $rfq1->id, 'quotation_detail_id' => $qd1->id, 'is_sent_to_user' => true, 'sent_to_user_at' => now()->subDays(14)]);
        $vs1 = VendorSelection::create(['rfq_id' => $rfq1->id, 'vendor_id' => $v1->id, 'quotation_id' => $quot1->id, 'decision_notes' => 'Best Price', 'decided_at' => now()->subDays(10)]);
        SelectionItem::create(['vendor_selection_id' => $vs1->id, 'quotation_summary_id' => $qs1->id, 'final_price_per_item' => 30000, 'final_quantity' => 50]);

        /* TEST CASE 2: Split PO / Multi-Vendor */
        $prSplit = PurchaseRequest::create(['user_id' => $req3->id, 'document_number' => 'PR-2026-0105-SPLIT', 'title' => 'Peralatan Pabrik Kompleks', 'department' => 'Maintenance', 'priority' => 'high', 'plant' => 'Cibitung', 'submission_date' => now()->subDays(30), 'requested_date' => now()->subDays(29), 'need_date' => now()->addDays(10), 'note' => 'Harus dibagi ke vendor termurah', 'status' => 'in_process']);
        PurchaseRequestItem::create(['purchase_request_id' => $prSplit->id, 'item_id' => 'TOL-001', 'item_name' => 'Bor Listrik Bosch', 'quantity' => 10, 'unit' => 'Unit', 'specification' => 'Bosch GSB 550 Professional', 'item_notes' => null]);
        PurchaseRequestItem::create(['purchase_request_id' => $prSplit->id, 'item_id' => 'TOL-002', 'item_name' => 'Mata Bor Set', 'quantity' => 20, 'unit' => 'Set', 'specification' => 'HSS-R Metal Drill Bits', 'item_notes' => null]);

        /* TEST CASE 3: PR Zonk */
        $prZonk = PurchaseRequest::create(['user_id' => $req3->id, 'document_number' => 'PR-2026-0110-ZONK', 'title' => 'Sewa Genset Darurat', 'department' => 'Maintenance', 'priority' => 'high', 'plant' => 'Cikarang', 'submission_date' => now()->subDays(15), 'requested_date' => now()->subDays(15), 'need_date' => now()->addDays(2), 'note' => 'Butuh cepat tapi ga ada vendor yang merespon', 'status' => 'in_process']);
        PurchaseRequestItem::create(['purchase_request_id' => $prZonk->id, 'item_id' => 'SVC-GEN-01', 'item_name' => 'Sewa Genset 500kVA', 'quantity' => 7, 'unit' => 'Hari', 'specification' => 'Service', 'item_notes' => null]);
        $rfqZonk = Rfq::create(['purchase_request_id' => $prZonk->id, 'rfq_number' => 'RFQ-ZONK-001', 'is_sent_to_user' => false, 'sent_to_user_at' => null, 'status' => 'closed']);

        // 4. SKENARIO SERVICE REQUEST (JASA NESTED)
        $sr1 = ServiceRequest::create([
            'user_id' => $req3->id,
            'service_name' => 'Renovasi Atap Gudang Utama',
            'submission_date' => now()->subDays(10),
            'requested_date' => now()->subDays(8),
            'plant' => 'Cikarang',
            'status' => 'awaiting_approval'
        ]);

        // JOB 1: Pembongkaran
        $job1 = ServiceRequestJob::create(['service_request_id' => $sr1->id, 'job_description' => 'Pembongkaran Atap Lama']);
        ServiceRequestItem::create(['job_id' => $job1->id, 'item_name' => 'Pekerja Lepas', 'quantity' => 5, 'unit' => 'Orang', 'specification' => 'Tenaga kasar']);
        ServiceRequestItem::create(['job_id' => $job1->id, 'item_name' => 'Sewa Scaffolding', 'quantity' => 10, 'unit' => 'Set', 'specification' => 'Tinggi 3 meter']);

        // JOB 2: Pemasangan
        $job2 = ServiceRequestJob::create(['service_request_id' => $sr1->id, 'job_description' => 'Pemasangan Atap Galvalum Baru']);
        ServiceRequestItem::create(['job_id' => $job2->id, 'item_name' => 'Seng Galvalum 0.3mm', 'quantity' => 200, 'unit' => 'Lembar', 'specification' => 'Ukuran 2 meter']);
        
        // 5. HISTORY DUMMY
        History::create(['user_id' => $req1->id, 'action' => 'Purchase Request Created', 'transaction_status' => 'completed', 'notes' => 'PR Office Supplies Dibuat', 'action_date' => now()->subDays(20)]);
    }
}