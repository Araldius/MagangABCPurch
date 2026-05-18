<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_request_items', function (Blueprint $table) {
            // ERD uses item_name (alias for name) and item_notes (alias for note)
            $table->string('item_name')->nullable()->after('item_code');
            $table->text('item_notes')->nullable()->after('note');
        });
    }

    public function down(): void
    {
        Schema::table('purchase_request_items', function (Blueprint $table) {
            $table->dropColumn(['item_name', 'item_notes']);
        });
    }
};