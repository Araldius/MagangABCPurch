<?php

namespace Database\Seeders;

use App\Models\History;
use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestItem;
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
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use \Illuminate\Database\Console\Seeds\WithoutModelEvents;

    public function run(): void
    {
        // =====================================================================
        // 1. USERS
        // =====================================================================
        $admin = User::factory()->create(['name' => 'Admin Purchasing', 'email' => 'admin@purchasing.local', 'password' => bcrypt('password'), 'role' => 'purchasing', 'department' => 'Procurement']);
        $req1  = User::factory()->create(['name' => 'Budi Santoso', 'email' => 'budi@company.local', 'password' => bcrypt('password'), 'role' => 'requester', 'department' => 'Operations']);
        $req2  = User::factory()->create(['name' => 'John Smith', 'email' => 'john.smith@company.local', 'password' => bcrypt('password'), 'role' => 'requester', 'department' => 'Engineering']);
        $req3  = User::factory()->create(['name' => 'Sarah Johnson', 'email' => 'sarah.johnson@company.local', 'password' => bcrypt('password'), 'role' => 'requester', 'department' => 'Maintenance']);
        $req4  = User::factory()->create(['name' => 'Dina Mariana', 'email' => 'dina@company.local', 'password' => bcrypt('password'), 'role' => 'requester', 'department' => 'HRD']); // User tanpa transaksi (untuk test UI kosong)

        // =====================================================================
        // 2. VENDORS
        // =====================================================================
        // Vendor Barang
        $v1 = Vendor::create(['vendor_name' => 'PT Sumber Mandiri (Goods)', 'location' => 'Jakarta',  'contact' => '021-5551234', 'status' => 'active']);
        $v2 = Vendor::create(['vendor_name' => 'PT Prima Niaga (Goods)',    'location' => 'Bandung',  'contact' => '022-5559876', 'status' => 'active']);
        $v3 = Vendor::create(['vendor_name' => 'CV Karya Teknik (Goods)',   'location' => 'Surabaya', 'contact' => '031-5554321', 'status' => 'active']);
        // Vendor Jasa
        $v4 = Vendor::create(['vendor_name' => 'PT Solusi IT Nusantara (Service)', 'location' => 'Jakarta',  'contact' => '021-5558888', 'status' => 'active']);
        $v5 = Vendor::create(['vendor_name' => 'CV Master Clean (Service)',        'location' => 'Semarang', 'contact' => '024-5559999', 'status' => 'active']);
        $v6 = Vendor::create(['vendor_name' => 'PT Bangun Teknindo (Service)',     'location' => 'Medan',    'contact' => '061-5557777', 'status' => 'active']);
        // Vendor Inactive (Pernah dipakai, tapi sekarang tidak aktif)
        $v7 = Vendor::create(['vendor_name' => 'UD Maju Mapan (Inactive)',         'location' => 'Malang',   'contact' => '0341-555000', 'status' => 'inactive']);


        // =====================================================================
        // 3. SKENARIO PURCHASE REQUESTS & ALUR LENGKAP
        // =====================================================================

        /* ---------------------------------------------------------------------
           TEST CASE 1: PR Normal Selesai (Goods)
           --------------------------------------------------------------------- */
        $pr1 = PurchaseRequest::create(['user_id' => $req1->id, 'document_number' => 'PR-2026-0101-001', 'title' => 'Office Supplies', 'department' => 'Operations', 'item_type' => 'goods', 'priority' => 'high', 'plant' => 'Cikarang', 'submission_date' => now()->subDays(20), 'requested_date' => now()->subDays(18), 'need_date' => now()->addDays(5), 'note' => 'Urgent for Q2', 'status' => 'completed']);
        $i1 = PurchaseRequestItem::create(['purchase_request_id' => $pr1->id, 'item_code' => 'OFF-001', 'name' => 'A4 Paper', 'item_name' => 'A4 Paper', 'quantity' => 50, 'unit' => 'Ream', 'specification' => '80 GSM', 'note' => 'Standard', 'item_notes' => 'Standard']);
        $rfq1 = Rfq::create(['purchase_request_id' => $pr1->id, 'rfq_number' => 'RFQ-2026-0102-001', 'is_sent_to_user' => true, 'sent_to_user_at' => now()->subDays(15), 'status' => 'closed']);
        QuotationPeriod::create(['rfq_id' => $rfq1->id, 'round' => 1, 'start_date' => now()->subDays(19), 'end_date' => now()->subDays(12), 'status' => 'closed']);
        VendorQuotation::create(['rfq_id' => $rfq1->id, 'vendor_id' => $v1->id, 'status' => 'submitted', 'submitted_at' => now()->subDays(17)]);
        $quot1 = Quotation::create(['rfq_id' => $rfq1->id, 'vendor_id' => $v1->id, 'total_price' => 1500000, 'status' => 'finalized']);
        $qd1 = QuotationDetail::create(['quotation_id' => $quot1->id, 'purchase_request_item_id' => $i1->id, 'offered_price_per_item' => 30000, 'offered_quantity' => 50]);
        $qs1 = QuotationSummary::create(['rfq_id' => $rfq1->id, 'quotation_detail_id' => $qd1->id, 'is_sent_to_user' => true, 'sent_to_user_at' => now()->subDays(14)]);
        $vs1 = VendorSelection::create(['rfq_id' => $rfq1->id, 'vendor_id' => $v1->id, 'quotation_id' => $quot1->id, 'decision_notes' => 'Best Price', 'decided_at' => now()->subDays(10)]);
        SelectionItem::create(['vendor_selection_id' => $vs1->id, 'quotation_summary_id' => $qs1->id, 'final_price_per_item' => 30000, 'final_quantity' => 50]);


        /* ---------------------------------------------------------------------
           TEST CASE 2: Split PO / Multi-Vendor Winner (Goods)
           Skenario: 1 PR isi 3 barang. RFQ ditutup. 
           Vendor 2 menang Barang A & B. Vendor 3 menang Barang C.
           Ini untuk tes fungsi multi-select vendor pada Quotation Final.
           --------------------------------------------------------------------- */
        $prSplit = PurchaseRequest::create(['user_id' => $req2->id, 'document_number' => 'PR-2026-0105-SPLIT', 'title' => 'Peralatan Pabrik Kompleks', 'department' => 'Engineering', 'item_type' => 'goods', 'priority' => 'high', 'plant' => 'Cibitung', 'submission_date' => now()->subDays(30), 'requested_date' => now()->subDays(29), 'need_date' => now()->addDays(10), 'note' => 'Harus dibagi ke vendor termurah per item', 'status' => 'completed']);
        
        $itemA = PurchaseRequestItem::create(['purchase_request_id' => $prSplit->id, 'item_code' => 'SPL-A', 'name' => 'Bor Listrik', 'item_name' => 'Bor Listrik', 'quantity' => 10, 'unit' => 'Pcs']);
        $itemB = PurchaseRequestItem::create(['purchase_request_id' => $prSplit->id, 'item_code' => 'SPL-B', 'name' => 'Mata Bor Set', 'item_name' => 'Mata Bor Set', 'quantity' => 20, 'unit' => 'Set']);
        $itemC = PurchaseRequestItem::create(['purchase_request_id' => $prSplit->id, 'item_code' => 'SPL-C', 'name' => 'Kabel Roll 50m', 'item_name' => 'Kabel Roll 50m', 'quantity' => 5, 'unit' => 'Roll']);
        
        $rfqSplit = Rfq::create(['purchase_request_id' => $prSplit->id, 'rfq_number' => 'RFQ-SPLIT-001', 'is_sent_to_user' => true, 'sent_to_user_at' => now()->subDays(20), 'status' => 'closed']);
        QuotationPeriod::create(['rfq_id' => $rfqSplit->id, 'round' => 1, 'start_date' => now()->subDays(28), 'end_date' => now()->subDays(22), 'status' => 'closed']);
        
        // Vendor 2 (Menang A & B, Kalah C)
        VendorQuotation::create(['rfq_id' => $rfqSplit->id, 'vendor_id' => $v2->id, 'status' => 'submitted', 'submitted_at' => now()->subDays(25)]);
        $quotV2 = Quotation::create(['rfq_id' => $rfqSplit->id, 'vendor_id' => $v2->id, 'total_price' => 2500000, 'status' => 'finalized']);
        $qdV2_A = QuotationDetail::create(['quotation_id' => $quotV2->id, 'purchase_request_item_id' => $itemA->id, 'offered_price_per_item' => 200000, 'offered_quantity' => 10]);
        $qdV2_B = QuotationDetail::create(['quotation_id' => $quotV2->id, 'purchase_request_item_id' => $itemB->id, 'offered_price_per_item' => 25000, 'offered_quantity' => 20]);
        $qdV2_C = QuotationDetail::create(['quotation_id' => $quotV2->id, 'purchase_request_item_id' => $itemC->id, 'offered_price_per_item' => 500000, 'offered_quantity' => 5]); // Mahal, kalah
        
        // Vendor 3 (Kalah A & B, Menang C)
        VendorQuotation::create(['rfq_id' => $rfqSplit->id, 'vendor_id' => $v3->id, 'status' => 'submitted', 'submitted_at' => now()->subDays(24)]);
        $quotV3 = Quotation::create(['rfq_id' => $rfqSplit->id, 'vendor_id' => $v3->id, 'total_price' => 2800000, 'status' => 'finalized']);
        $qdV3_A = QuotationDetail::create(['quotation_id' => $quotV3->id, 'purchase_request_item_id' => $itemA->id, 'offered_price_per_item' => 220000, 'offered_quantity' => 10]); // Mahal, kalah
        $qdV3_B = QuotationDetail::create(['quotation_id' => $quotV3->id, 'purchase_request_item_id' => $itemB->id, 'offered_price_per_item' => 30000, 'offered_quantity' => 20]); // Mahal, kalah
        $qdV3_C = QuotationDetail::create(['quotation_id' => $quotV3->id, 'purchase_request_item_id' => $itemC->id, 'offered_price_per_item' => 400000, 'offered_quantity' => 5]); // Murah, menang

        // Summaries
        $qsV2_A = QuotationSummary::create(['rfq_id' => $rfqSplit->id, 'quotation_detail_id' => $qdV2_A->id, 'is_sent_to_user' => true, 'sent_to_user_at' => now()->subDays(19)]);
        $qsV2_B = QuotationSummary::create(['rfq_id' => $rfqSplit->id, 'quotation_detail_id' => $qdV2_B->id, 'is_sent_to_user' => true, 'sent_to_user_at' => now()->subDays(19)]);
        $qsV3_C = QuotationSummary::create(['rfq_id' => $rfqSplit->id, 'quotation_detail_id' => $qdV3_C->id, 'is_sent_to_user' => true, 'sent_to_user_at' => now()->subDays(19)]);

        // Selections (Multiple Selections for 1 RFQ)
        $vsV2 = VendorSelection::create(['rfq_id' => $rfqSplit->id, 'vendor_id' => $v2->id, 'quotation_id' => $quotV2->id, 'decision_notes' => 'Menang untuk Bor Listrik & Mata Bor', 'decided_at' => now()->subDays(18)]);
        SelectionItem::create(['vendor_selection_id' => $vsV2->id, 'quotation_summary_id' => $qsV2_A->id, 'final_price_per_item' => 200000, 'final_quantity' => 10]);
        SelectionItem::create(['vendor_selection_id' => $vsV2->id, 'quotation_summary_id' => $qsV2_B->id, 'final_price_per_item' => 25000, 'final_quantity' => 20]);

        $vsV3 = VendorSelection::create(['rfq_id' => $rfqSplit->id, 'vendor_id' => $v3->id, 'quotation_id' => $quotV3->id, 'decision_notes' => 'Menang untuk Kabel Roll', 'decided_at' => now()->subDays(18)]);
        SelectionItem::create(['vendor_selection_id' => $vsV3->id, 'quotation_summary_id' => $qsV3_C->id, 'final_price_per_item' => 400000, 'final_quantity' => 5]);


        /* ---------------------------------------------------------------------
           TEST CASE 3: RFQ Zonk / Zero Bids / Failed Procurement (Service)
           Skenario: RFQ sudah expired, tapi tidak ada vendor yang mengumpulkan penawaran.
           --------------------------------------------------------------------- */
        $prZonk = PurchaseRequest::create(['user_id' => $req3->id, 'document_number' => 'PR-2026-0110-ZONK', 'title' => 'Sewa Genset Darurat', 'department' => 'Maintenance', 'item_type' => 'service', 'priority' => 'high', 'plant' => 'Cikarang', 'submission_date' => now()->subDays(15), 'requested_date' => now()->subDays(15), 'need_date' => now()->addDays(2), 'note' => 'Butuh cepat tapi ga ada vendor yang merespon', 'status' => 'in_process']);
        PurchaseRequestItem::create(['purchase_request_id' => $prZonk->id, 'item_code' => 'SVC-GEN-01', 'name' => 'Sewa Genset 500kVA', 'item_name' => 'Sewa Genset 500kVA', 'quantity' => 7, 'unit' => 'Hari']);
        $rfqZonk = Rfq::create(['purchase_request_id' => $prZonk->id, 'rfq_number' => 'RFQ-ZONK-001', 'is_sent_to_user' => false, 'sent_to_user_at' => null, 'status' => 'closed']);
        QuotationPeriod::create(['rfq_id' => $rfqZonk->id, 'round' => 1, 'start_date' => now()->subDays(14), 'end_date' => now()->subDays(7), 'status' => 'closed']);
        // Semua vendor hanya 'draft' (tidak dikumpulkan)
        VendorQuotation::create(['rfq_id' => $rfqZonk->id, 'vendor_id' => $v5->id, 'status' => 'draft']);
        VendorQuotation::create(['rfq_id' => $rfqZonk->id, 'vendor_id' => $v6->id, 'status' => 'draft']);


        /* ---------------------------------------------------------------------
           TEST CASE 4: PR Ditolak (Rejected) & Dibatalkan (Canceled)
           Skenario: Menguji filter status dan tampilan label status
           --------------------------------------------------------------------- */
        $prRejected = PurchaseRequest::create(['user_id' => $req1->id, 'document_number' => 'PR-2026-0115-REJ', 'title' => 'Laptop Gaming untuk Kantor', 'department' => 'Operations', 'item_type' => 'goods', 'priority' => 'normal', 'plant' => 'Jakarta HQ', 'submission_date' => now()->subDays(5), 'requested_date' => now()->subDays(5), 'need_date' => now()->addDays(15), 'note' => 'Ditolak karena tidak sesuai standar operasional', 'status' => 'rejected']);
        PurchaseRequestItem::create(['purchase_request_id' => $prRejected->id, 'item_code' => 'ITM-REJ-01', 'name' => 'Laptop ROG', 'item_name' => 'Laptop ROG', 'quantity' => 2, 'unit' => 'Unit']);

        $prCanceled = PurchaseRequest::create(['user_id' => $req2->id, 'document_number' => 'PR-2026-0117-CAN', 'title' => 'Acara Outing Divisi', 'department' => 'Engineering', 'item_type' => 'service', 'priority' => 'normal', 'plant' => 'Cibitung', 'submission_date' => now()->subDays(2), 'requested_date' => now()->subDays(2), 'need_date' => now()->addDays(30), 'note' => 'Batal diadakan karena budget cut', 'status' => 'canceled']);
        PurchaseRequestItem::create(['purchase_request_id' => $prCanceled->id, 'item_code' => 'SVC-OUT-01', 'name' => 'Sewa Bus Pariwisata', 'item_name' => 'Sewa Bus Pariwisata', 'quantity' => 2, 'unit' => 'Unit']);


        /* ---------------------------------------------------------------------
           TEST CASE 5: Stress Test UI Rupiah Milyaran (Goods)
           Skenario: Memastikan tabel tidak break ketika angkanya sangat panjang.
           --------------------------------------------------------------------- */
        $prWhale = PurchaseRequest::create(['user_id' => $req3->id, 'document_number' => 'PR-2026-0200-WHALE', 'title' => 'Pembelian Lahan & Mesin Berat', 'department' => 'Maintenance', 'item_type' => 'goods', 'priority' => 'normal', 'plant' => 'Surabaya', 'submission_date' => now()->subDays(1), 'requested_date' => now()->subDays(1), 'need_date' => now()->addDays(90), 'note' => 'Budget Ekspansi Tahunan', 'status' => 'awaiting_approval']);
        PurchaseRequestItem::create(['purchase_request_id' => $prWhale->id, 'item_code' => 'BIG-001', 'name' => 'Tanah Industri 10 Ha', 'item_name' => 'Tanah Industri 10 Ha', 'quantity' => 100000, 'unit' => 'm2', 'specification' => 'Zona Merah Industri']);
        PurchaseRequestItem::create(['purchase_request_id' => $prWhale->id, 'item_code' => 'BIG-002', 'name' => 'Excavator CAT 320', 'item_name' => 'Excavator CAT 320', 'quantity' => 15, 'unit' => 'Unit']);
        

        /* ---------------------------------------------------------------------
           TEST CASE 6: PR Sedang Menunggu Persetujuan (Awaiting Approval)
           --------------------------------------------------------------------- */
        $prAwaiting = PurchaseRequest::create(['user_id' => $req1->id, 'document_number' => 'PR-2026-0210-WAIT', 'title' => 'Tinta Printer Bulanan', 'department' => 'Operations', 'item_type' => 'goods', 'priority' => 'normal', 'plant' => 'Cikarang', 'submission_date' => now(), 'requested_date' => now(), 'need_date' => now()->addDays(7), 'note' => 'Stock limit menipis', 'status' => 'awaiting_approval']);
        PurchaseRequestItem::create(['purchase_request_id' => $prAwaiting->id, 'item_code' => 'OFF-INK', 'name' => 'Tinta Epson 003 Hitam', 'item_name' => 'Tinta Epson 003 Hitam', 'quantity' => 20, 'unit' => 'Botol']);


        // =====================================================================
        // 4. HISTORY SEEDING (Untuk Menguji Tab Timeline / Jejak Audit)
        // =====================================================================
        $histories = [
            // History PR Normal Selesai
            ['user_id' => $req1->id,  'vendor_id' => null,  'rfq_id' => null,     'vendor_selection_id' => null,   'action' => 'Purchase Request Created', 'transaction_status' => 'completed', 'notes' => 'PR Office Supplies Dibuat', 'action_date' => now()->subDays(20)],
            ['user_id' => $admin->id, 'vendor_id' => null,  'rfq_id' => $rfq1->id,'vendor_selection_id' => null,   'action' => 'RFQ Created',              'transaction_status' => 'completed', 'notes' => 'RFQ-2026-0102-001 dibuka', 'action_date' => now()->subDays(19)],
            ['user_id' => $admin->id, 'vendor_id' => $v1->id, 'rfq_id' => $rfq1->id,'vendor_selection_id' => $vs1->id, 'action' => 'Vendor Selected',        'transaction_status' => 'completed', 'notes' => 'PT Sumber Mandiri Terpilih', 'action_date' => now()->subDays(10)],

            // History PR Split PO
            ['user_id' => $req2->id,  'vendor_id' => null,  'rfq_id' => null,         'vendor_selection_id' => null,   'action' => 'Purchase Request Created', 'transaction_status' => 'completed', 'notes' => 'PR Split Peralatan Dibuat', 'action_date' => now()->subDays(30)],
            ['user_id' => $admin->id, 'vendor_id' => null,  'rfq_id' => $rfqSplit->id,'vendor_selection_id' => null,   'action' => 'RFQ Created',              'transaction_status' => 'completed', 'notes' => 'RFQ SPLIT dibuka', 'action_date' => now()->subDays(28)],
            ['user_id' => $admin->id, 'vendor_id' => $v2->id, 'rfq_id' => $rfqSplit->id,'vendor_selection_id' => $vsV2->id, 'action' => 'Vendor Selected',    'transaction_status' => 'completed', 'notes' => 'PT Prima Niaga Menang Sebagian Item', 'action_date' => now()->subDays(18)],
            ['user_id' => $admin->id, 'vendor_id' => $v3->id, 'rfq_id' => $rfqSplit->id,'vendor_selection_id' => $vsV3->id, 'action' => 'Vendor Selected',    'transaction_status' => 'completed', 'notes' => 'CV Karya Teknik Menang Sebagian Item', 'action_date' => now()->subDays(18)],

            // History PR Zonk (Tanpa Pemenang)
            ['user_id' => $req3->id,  'vendor_id' => null,  'rfq_id' => null,         'vendor_selection_id' => null,   'action' => 'Purchase Request Created', 'transaction_status' => 'completed', 'notes' => 'PR Sewa Genset Dibuat', 'action_date' => now()->subDays(15)],
            ['user_id' => $admin->id, 'vendor_id' => null,  'rfq_id' => $rfqZonk->id, 'vendor_selection_id' => null,   'action' => 'RFQ Created',              'transaction_status' => 'completed', 'notes' => 'RFQ Genset dibuka. Tidak ada vendor merespon.', 'action_date' => now()->subDays(14)],

            // History PR Rejected & Canceled
            ['user_id' => $req1->id,  'vendor_id' => null,  'rfq_id' => null,     'vendor_selection_id' => null,   'action' => 'Purchase Request Created', 'transaction_status' => 'rejected',  'notes' => 'PR Ditolak Admin: Budget Tidak Tersedia', 'action_date' => now()->subDays(4)],
            ['user_id' => $req2->id,  'vendor_id' => null,  'rfq_id' => null,     'vendor_selection_id' => null,   'action' => 'Purchase Request Created', 'transaction_status' => 'canceled',  'notes' => 'Dibatalkan oleh Requester Budi', 'action_date' => now()->subDays(1)],
        ];

        foreach ($histories as $h) {
            History::create($h);
        }
    }
}