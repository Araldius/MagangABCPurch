<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_request_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_id')->constrained('service_request_jobs')->onDelete('cascade');
            $table->string('item_name');
            $table->decimal('quantity', 10, 2);
            $table->string('unit');
            $table->string('specification')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_request_items');
    }
};