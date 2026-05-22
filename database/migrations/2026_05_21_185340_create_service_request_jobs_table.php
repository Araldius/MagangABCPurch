<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_request_jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sr_id')->constrained('service_requests')->onDelete('cascade');
            $table->string('job_description');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_request_jobs');
    }
};