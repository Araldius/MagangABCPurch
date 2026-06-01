<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_request_jobs', function (Blueprint $table) {
            $table->string('job_code')->nullable()->after('service_request_id');
        });
    }
    public function down(): void
    {
        Schema::table('service_request_jobs', function (Blueprint $table) {
            $table->dropColumn('job_code');
        });
    }
};
