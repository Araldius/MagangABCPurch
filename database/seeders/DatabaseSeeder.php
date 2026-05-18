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

class DatabaseSeeder extends Seeder
{
    use \Illuminate\Database\Console\Seeds\WithoutModelEvents;

    public function run(): void
    {
        // ─── USERS ───────────────────────────────────────────────────────────
        $admin = User::factory()->create([
            'name'       => 'Admin Purchasing',
            'email'      => 'admin@purchasing.local',
            'password'   => bcrypt('password'),
            'role'       => 'purchasing',
            'department' => 'Procurement',
        ]);

        $req1 = User::factory()->create([
            'name'       => 'Budi Santoso',
            'email'      => 'budi@company.local',
            'password'   => bcrypt('password'),
            'role'       => 'requester',
            'department' => 'Operations',
        ]);

        $req2 = User::factory()->create([
            'name'       => 'John Smith',
            'email'      => 'john.smith@company.local',
            'password'   => bcrypt('password'),
            'role'       => 'requester',
            'department' => 'Engineering',
        ]);

        $req3 = User::factory()->create([
            'name'       => 'Sarah Johnson',
            'email'      => 'sarah.johnson@company.local',
            'password'   => bcrypt('password'),
            'role'       => 'requester',
            'department' => 'Maintenance',
        ]);

        // ─── VENDORS ─────────────────────────────────────────────────────────
        // Menambahkan properti location dan contact setelah vendor_name
        $v1 = Vendor::create(['vendor_name' => 'PT Sumber Mandiri', 'location' => 'Jakarta',  'contact' => '021-5551234', 'status' => 'active']);
        $v2 = Vendor::create(['vendor_name' => 'PT Prima Niaga',    'location' => 'Bandung',  'contact' => '022-5559876', 'status' => 'active']);
        $v3 = Vendor::create(['vendor_name' => 'CV Karya Teknik',   'location' => 'Surabaya', 'contact' => '031-5554321', 'status' => 'active']);
        $v4 = Vendor::create(['vendor_name' => 'PT Maju Bersama',   'location' => 'Medan',    'contact' => '061-5557777', 'status' => 'active']);
        $v5 = Vendor::create(['vendor_name' => 'UD Sejahtera Kimia','location' => 'Semarang', 'contact' => '024-5553333', 'status' => 'inactive']);

        // ─── PURCHASE REQUESTS ───────────────────────────────────────────────
        // Mengembalikan kolom 'document_number', 'title', 'priority', 'need_date', dan 'note' yang wajib diisi oleh migration Anda
        $pr1 = PurchaseRequest::create([
            'user_id'         => $req1->id,
            'document_number' => 'PR-2026-0101-001',
            'title'           => 'Office Supplies Procurement',
            'department'      => 'Operations',
            'priority'        => 'high',
            'plant'           => 'Cikarang',
            'submission_date' => now()->subDays(15)->toDateString(),
            'requested_date'  => now()->subDays(12)->toDateString(),
            'need_date'       => now()->addDays(5)->toDateString(),
            'note'            => 'Urgent office supplies needed for Q2 operations',
            'status'          => 'completed',
        ]);

        $pr2 = PurchaseRequest::create([
            'user_id'         => $req2->id,
            'document_number' => 'PR-2026-0108-001',
            'title'           => 'Industrial Equipment Purchase',
            'department'      => 'Engineering',
            'priority'        => 'normal',
            'plant'           => 'Cibitung',
            'submission_date' => now()->subDays(9)->toDateString(),
            'requested_date'  => now()->subDays(7)->toDateString(),
            'need_date'       => now()->addDays(21)->toDateString(),
            'note'            => 'Production line upgrade equipment',
            'status'          => 'rfq_open',
        ]);

        $pr3 = PurchaseRequest::create([
            'user_id'         => $req3->id,
            'document_number' => 'PR-2026-0110-001',
            'title'           => 'Maintenance Tools and Parts',
            'department'      => 'Maintenance',
            'priority'        => 'normal',
            'plant'           => 'Cikarang',
            'submission_date' => now()->subDays(7)->toDateString(),
            'requested_date'  => now()->subDays(5)->toDateString(),
            'need_date'       => now()->addDays(14)->toDateString(),
            'note'            => 'Replacement parts for Q2 preventive maintenance',
            'status'          => 'completed',
        ]);

        $pr4 = PurchaseRequest::create([
            'user_id'         => $req1->id,
            'document_number' => 'PR-2026-0115-001',
            'title'           => 'Safety Equipment & PPE',
            'department'      => 'Operations',
            'priority'        => 'high',
            'plant'           => 'Gresik',
            'submission_date' => now()->subDays(2)->toDateString(),
            'requested_date'  => now()->subDays(1)->toDateString(),
            'need_date'       => now()->addDays(10)->toDateString(),
            'note'            => 'PPE renewal for plant workers',
            'status'          => 'in_process',
        ]);

        $pr5 = PurchaseRequest::create([
            'user_id'         => $req2->id,
            'document_number' => 'PR-2026-0117-001',
            'title'           => 'Lab Chemicals & Reagents',
            'department'      => 'Engineering',
            'priority'        => 'normal',
            'plant'           => 'Cibitung',
            'submission_date' => now()->toDateString(),
            'requested_date'  => now()->toDateString(),
            'need_date'       => now()->addDays(30)->toDateString(),
            'note'            => 'Monthly lab consumables',
            'status'          => 'in_process',
        ]);

        // ─── PURCHASE REQUEST ITEMS ──────────────────────────────────────────
        // Mengembalikan item_code, name, unit, dan note yang diwajibkan oleh file migrasi Anda
        $i1 = PurchaseRequestItem::create([
            'purchase_request_id' => $pr1->id, 
            'item_code'           => 'OFF-001', 
            'name'                => 'A4 Paper Reams', 
            'item_name'           => 'A4 Paper Reams', 
            'quantity'            => 50, 
            'unit'                => 'Ream', 
            'specification'       => '80 GSM, White', 
            'note'                => 'Standard office paper', 
            'item_notes'          => 'Standard office paper'
        ]);

        $i2 = PurchaseRequestItem::create([
            'purchase_request_id' => $pr1->id, 
            'item_code'           => 'OFF-002', 
            'name'                => 'Ball Pen Set', 
            'item_name'           => 'Ball Pen Set', 
            'quantity'            => 100, 
            'unit'                => 'Box', 
            'specification'       => 'Blue ink, 0.7mm', 
            'note'                => 'For office use', 
            'item_notes'          => 'For office use'
        ]);

        $i3 = PurchaseRequestItem::create([
            'purchase_request_id' => $pr2->id, 
            'item_code'           => 'IND-001', 
            'name'                => 'Electric Motor 2.2kW', 
            'item_name'           => 'Electric Motor 2.2kW', 
            'quantity'            => 5, 
            'unit'                => 'Unit', 
            'specification'       => '2.2 kW, 380V, 3-phase', 
            'note'                => 'Replacement motors for production line', 
            'item_notes'          => 'Replacement motors for production line'
        ]);

        $i4 = PurchaseRequestItem::create([
            'purchase_request_id' => $pr3->id, 
            'item_code'           => 'MNT-001', 
            'name'                => 'Hydraulic Oil ISO VG 46', 
            'item_name'           => 'Hydraulic Oil ISO VG 46', 
            'quantity'            => 200, 
            'unit'                => 'Liter', 
            'specification'       => 'ISO VG 46', 
            'note'                => 'Equipment maintenance', 
            'item_notes'          => 'Equipment maintenance'
        ]);

        $i5 = PurchaseRequestItem::create([
            'purchase_request_id' => $pr3->id, 
            'item_code'           => 'MNT-002', 
            'name'                => 'SKF Bearing Set', 
            'item_name'           => 'SKF Bearing Set', 
            'quantity'            => 10, 
            'unit'                => 'Set', 
            'specification'       => 'SKF Deep Groove Ball Bearings 6205', 
            'note'                => 'For machinery repair', 
            'item_notes'          => 'For machinery repair'
        ]);

        $i6 = PurchaseRequestItem::create([
            'purchase_request_id' => $pr4->id, 
            'item_code'           => 'PPE-001', 
            'name'                => 'Safety Helmet', 
            'item_name'           => 'Safety Helmet', 
            'quantity'            => 30, 
            'unit'                => 'Unit', 
            'specification'       => 'ANSI Z89.1 Class E', 
            'note'                => 'For plant operations team', 
            'item_notes'          => 'For plant operations team'
        ]);

        $i7 = PurchaseRequestItem::create([
            'purchase_request_id' => $pr4->id, 
            'item_code'           => 'PPE-002', 
            'name'                => 'Safety Boots', 
            'item_name'           => 'Safety Boots', 
            'quantity'            => 30, 
            'unit'                => 'Pair', 
            'specification'       => 'Steel toe, Size 40-44', 
            'note'                => 'PPE renewal', 
            'item_notes'          => 'PPE renewal'
        ]);

        $i8 = PurchaseRequestItem::create([
            'purchase_request_id' => $pr5->id, 
            'item_code'           => 'LAB-001', 
            'name'                => 'Sodium Hydroxide (NaOH)', 
            'item_name'           => 'Sodium Hydroxide (NaOH)', 
            'quantity'            => 50, 
            'unit'                => 'Kg', 
            'specification'       => '99% purity, Technical grade', 
            'note'                => 'Monthly lab supply', 
            'item_notes'          => 'Monthly lab supply'
        ]);

        // ─── RFQs (RFQ_HEADERS) ──────────────────────────────────────────────
        // Disesuaikan dengan ERD: foreign key pr_id, hapus vendor_id dari header rfq
        $rfq1 = Rfq::create([
            'purchase_request_id'           => $pr1->id,
            'rfq_number'      => 'RFQ-2026-0102-001',
            'is_sent_to_user' => true,
            'sent_to_user_at' => now()->subDays(8),
            'status'          => 'closed',
        ]);

        $rfq2 = Rfq::create([
            'purchase_request_id'           => $pr2->id,
            'rfq_number'      => 'RFQ-2026-0109-001',
            'is_sent_to_user' => false,
            'sent_to_user_at' => null,
            'status'          => 'open',
        ]);

        $rfq3 = Rfq::create([
            'purchase_request_id'           => $pr3->id,
            'rfq_number'      => 'RFQ-2026-0111-001',
            'is_sent_to_user' => true,
            'sent_to_user_at' => now()->subDays(3),
            'status'          => 'closed',
        ]);

        // ─── QUOTATION PERIODS ───────────────────────────────────────────────
        QuotationPeriod::create(['rfq_id' => $rfq1->id, 'round' => 1, 'start_date' => now()->subDays(13), 'end_date' => now()->subDays(6), 'status' => 'closed']);
        QuotationPeriod::create(['rfq_id' => $rfq2->id, 'round' => 1, 'start_date' => now()->subDays(8), 'end_date' => now()->addDays(2), 'status' => 'open']);
        QuotationPeriod::create(['rfq_id' => $rfq3->id, 'round' => 1, 'start_date' => now()->subDays(6), 'end_date' => now()->subDays(1), 'status' => 'closed']);

        // ─── VENDOR QUOTATIONS ───────────────────────────────────────────────
        $vq1 = VendorQuotation::create(['rfq_id' => $rfq1->id, 'vendor_id' => $v1->id, 'quotation_file' => 'quot_sumber_mandiri_rfq1.pdf', 'notes' => 'Best price with 7-day delivery', 'status' => 'submitted', 'submitted_at' => now()->subDays(10)]);
        $vq2 = VendorQuotation::create(['rfq_id' => $rfq1->id, 'vendor_id' => $v2->id, 'quotation_file' => 'quot_prima_niaga_rfq1.pdf',   'notes' => 'Express delivery option', 'status' => 'submitted', 'submitted_at' => now()->subDays(9)]);
        $vq3 = VendorQuotation::create(['rfq_id' => $rfq2->id, 'vendor_id' => $v3->id, 'quotation_file' => 'quot_karya_teknik_rfq2.pdf',  'notes' => '2-year warranty included', 'status' => 'submitted', 'submitted_at' => now()->subDays(6)]);
        $vq4 = VendorQuotation::create(['rfq_id' => $rfq2->id, 'vendor_id' => $v4->id, 'quotation_file' => null,                           'notes' => 'Awaiting quotation document', 'status' => 'draft', 'submitted_at' => null]);
        $vq5 = VendorQuotation::create(['rfq_id' => $rfq3->id, 'vendor_id' => $v2->id, 'quotation_file' => 'quot_prima_niaga_rfq3.pdf',   'notes' => 'Competitive maintenance bundle', 'status' => 'submitted', 'submitted_at' => now()->subDays(4)]);
        $vq6 = VendorQuotation::create(['rfq_id' => $rfq3->id, 'vendor_id' => $v1->id, 'quotation_file' => 'quot_sumber_mandiri_rfq3.pdf', 'notes' => 'Standard maintenance package', 'status' => 'submitted', 'submitted_at' => now()->subDays(3)]);

        // ─── QUOTATION DETAILS ───────────────────────────────────────────────
        // Catatan: Jika entitas 'Quotation' utama/transaksional tidak ada di ERD terbaru Anda, pastikan data dummy 
        // quotation_id tetap diarahkan ke induknya, atau sesuaikan dengan table structure migrasi Anda.
        $quot1 = Quotation::create(['rfq_id' => $rfq1->id, 'vendor_id' => $v1->id, 'total_price' => 1500000.00, 'note' => 'Best price bundle', 'status' => 'finalized']);
        $quot2 = Quotation::create(['rfq_id' => $rfq3->id, 'vendor_id' => $v2->id, 'total_price' => 4800000.00, 'note' => 'Competitive pricing', 'status' => 'finalized']);
        $quot3 = Quotation::create(['rfq_id' => $rfq2->id, 'vendor_id' => $v3->id, 'total_price' => 25000000.00, 'note' => 'Equipment with full warranty', 'status' => 'submitted']);

        // Disesuaikan dengan ERD: foreign key menggunakan pr_item_id
        $qd1 = QuotationDetail::create(['quotation_id' => $quot1->id, 'purchase_request_item_id' => $i1->id, 'offered_price_per_item' => 30000.00, 'offered_quantity' => 50]);
        $qd2 = QuotationDetail::create(['quotation_id' => $quot1->id, 'purchase_request_item_id' => $i2->id, 'offered_price_per_item' =>  9000.00, 'offered_quantity' => 100]);
        $qd3 = QuotationDetail::create(['quotation_id' => $quot2->id, 'purchase_request_item_id' => $i4->id, 'offered_price_per_item' => 22000.00, 'offered_quantity' => 200]);
        $qd4 = QuotationDetail::create(['quotation_id' => $quot2->id, 'purchase_request_item_id' => $i5->id, 'offered_price_per_item' => 120000.00, 'offered_quantity' => 10]);
        $qd5 = QuotationDetail::create(['quotation_id' => $quot3->id, 'purchase_request_item_id' => $i3->id, 'offered_price_per_item' => 5000000.00, 'offered_quantity' => 5]);

        // ─── QUOTATION SUMMARIES ─────────────────────────────────────────────
        $qs1 = QuotationSummary::create(['rfq_id' => $rfq1->id, 'quotation_detail_id' => $qd1->id, 'is_sent_to_user' => true, 'sent_to_user_at' => now()->subDays(5)]);
        $qs2 = QuotationSummary::create(['rfq_id' => $rfq1->id, 'quotation_detail_id' => $qd2->id, 'is_sent_to_user' => true, 'sent_to_user_at' => now()->subDays(5)]);
        $qs3 = QuotationSummary::create(['rfq_id' => $rfq3->id, 'quotation_detail_id' => $qd3->id, 'is_sent_to_user' => true, 'sent_to_user_at' => now()->subDays(2)]);
        $qs4 = QuotationSummary::create(['rfq_id' => $rfq3->id, 'quotation_detail_id' => $qd4->id, 'is_sent_to_user' => true, 'sent_to_user_at' => now()->subDays(2)]);
        $qs5 = QuotationSummary::create(['rfq_id' => $rfq2->id, 'quotation_detail_id' => $qd5->id, 'is_sent_to_user' => false, 'sent_to_user_at' => null]);

        // ─── VENDOR SELECTIONS ───────────────────────────────────────────────
        $vs1 = VendorSelection::create(['rfq_id' => $rfq1->id, 'vendor_id' => $v1->id, 'quotation_id' => $quot1->id, 'decision_notes' => 'Best price-to-quality ratio', 'decided_at' => now()->subDays(4)]);
        $vs2 = VendorSelection::create(['rfq_id' => $rfq3->id, 'vendor_id' => $v2->id, 'quotation_id' => $quot2->id, 'decision_notes' => 'Competitive price, fast delivery track record', 'decided_at' => now()->subDays(1)]);

        // ─── SELECTION ITEMS ─────────────────────────────────────────────────
        // Disesuaikan dengan ERD: foreign key menggunakan selection_id
        SelectionItem::create(['vendor_selection_id' => $vs1->id, 'quotation_summary_id' => $qs1->id, 'final_price_per_item' => 30000.00, 'final_quantity' => 50, 'notes' => 'Delivery confirmed next week']);
        SelectionItem::create(['vendor_selection_id' => $vs1->id, 'quotation_summary_id' => $qs2->id, 'final_price_per_item' =>  9000.00, 'final_quantity' => 100, 'notes' => 'Included in same delivery']);
        SelectionItem::create(['vendor_selection_id' => $vs2->id, 'quotation_summary_id' => $qs3->id, 'final_price_per_item' => 22000.00, 'final_quantity' => 200, 'notes' => 'Hydraulic oil — bulk order']);
        SelectionItem::create(['vendor_selection_id' => $vs2->id, 'quotation_summary_id' => $qs4->id, 'final_price_per_item' => 120000.00, 'final_quantity' => 10, 'notes' => 'Bearing set — critical spare']);

        // Update PR statuses after selection
        $pr1->update(['status' => 'completed']);
        $pr3->update(['status' => 'completed']);

        // ─── HISTORY ─────────────────────────────────────────────────────────
        // Disesuaikan dengan kolom ERD: id, user_id, vendor_id, rfq_id, selection_id, action, transaction_status, notes, action_date
        $histories = [
            ['user_id' => $req1->id,  'vendor_id' => $v1->id, 'rfq_id' => $rfq1->id, 'vendor_selection_id' => $vs1->id, 'action' => 'PR Created',       'transaction_status' => 'completed', 'notes' => 'PR dibuat untuk Office Supplies',          'action_date' => now()->subDays(15)],
            ['user_id' => $admin->id, 'vendor_id' => $v1->id, 'rfq_id' => $rfq1->id, 'vendor_selection_id' => $vs1->id, 'action' => 'RFQ Created',      'transaction_status' => 'completed', 'notes' => 'RFQ-2026-0102-001 dibuat oleh Tim Procurement', 'action_date' => now()->subDays(13)],
            ['user_id' => $admin->id, 'vendor_id' => $v1->id, 'rfq_id' => $rfq1->id, 'vendor_selection_id' => $vs1->id, 'action' => 'Vendor Selected',   'transaction_status' => 'completed', 'notes' => 'PT Sumber Mandiri dipilih sebagai pemenang',             'action_date' => now()->subDays(4)],
            ['user_id' => $admin->id, 'vendor_id' => $v1->id, 'rfq_id' => $rfq1->id, 'vendor_selection_id' => $vs1->id, 'action' => 'Quotation Finalized','transaction_status' => 'completed', 'notes' => 'Final quotation disetujui — total Rp 1.500.000',        'action_date' => now()->subDays(4)],
            ['user_id' => $req2->id,  'vendor_id' => $v3->id, 'rfq_id' => $rfq2->id, 'vendor_selection_id' => null,     'action' => 'PR Created',         'transaction_status' => 'completed', 'notes' => 'PR untuk Industrial Equipment',            'action_date' => now()->subDays(9)],
            ['user_id' => $admin->id, 'vendor_id' => $v3->id, 'rfq_id' => $rfq2->id, 'vendor_selection_id' => null,     'action' => 'RFQ Created',        'transaction_status' => 'completed', 'notes' => 'RFQ-2026-0109-001 dibuat — 2 vendor diundang',          'action_date' => now()->subDays(8)],
            ['user_id' => $req3->id,  'vendor_id' => $v2->id, 'rfq_id' => $rfq3->id, 'vendor_selection_id' => $vs2->id, 'action' => 'PR Created',         'transaction_status' => 'completed', 'notes' => 'PR untuk Maintenance Tools',               'action_date' => now()->subDays(7)],
            ['user_id' => $admin->id, 'vendor_id' => $v2->id, 'rfq_id' => $rfq3->id, 'vendor_selection_id' => $vs2->id, 'action' => 'RFQ Created',        'transaction_status' => 'completed', 'notes' => 'RFQ-2026-0111-001 dibuat',                             'action_date' => now()->subDays(6)],
            ['user_id' => $admin->id, 'vendor_id' => $v2->id, 'rfq_id' => $rfq3->id, 'vendor_selection_id' => $vs2->id, 'action' => 'Vendor Selected',    'transaction_status' => 'completed', 'notes' => 'PT Prima Niaga dipilih — harga terbaik',               'action_date' => now()->subDays(1)],
            ['user_id' => $admin->id, 'vendor_id' => $v2->id, 'rfq_id' => $rfq3->id, 'vendor_selection_id' => $vs2->id, 'action' => 'Quotation Finalized','transaction_status' => 'completed', 'notes' => 'Final quotation disetujui — total Rp 4.800.000',        'action_date' => now()->subDays(1)],
        ];

        foreach ($histories as $h) {
            History::create($h);
        }
    }
}