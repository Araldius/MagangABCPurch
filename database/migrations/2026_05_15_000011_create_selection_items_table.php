<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('selection_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_selection_id')->constrained()->cascadeOnDelete();
            $table->foreignId('quotation_summary_id')->constrained()->cascadeOnDelete();
            $table->decimal('final_price_per_item', 16, 2);
            $table->integer('final_quantity');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('selection_items');
    }
};
