<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\ServiceRequest;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_requests', function (Blueprint $table) {
            $table->string('document_number')->nullable()->unique()->after('id');
        });

        // Back-fill existing rows that have no document_number
        ServiceRequest::whereNull('document_number')
            ->orderBy('id')
            ->each(function (ServiceRequest $sr) {
                $sr->update([
                    'document_number' => 'SR-' . $sr->created_at->format('Y') . '-'
                                       . str_pad($sr->id, 4, '0', STR_PAD_LEFT),
                ]);
            });
    }

    public function down(): void
    {
        Schema::table('service_requests', function (Blueprint $table) {
            $table->dropColumn('document_number');
        });
    }
};