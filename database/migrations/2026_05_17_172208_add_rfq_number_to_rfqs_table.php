<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rfqs', function (Blueprint $table) {
            $table->string('rfq_number')->nullable()->unique()->after('id');
            $table->boolean('is_sent_to_user')->default(false)->after('status');
            $table->timestamp('sent_to_user_at')->nullable()->after('is_sent_to_user');
        });
    }

    public function down(): void
    {
        Schema::table('rfqs', function (Blueprint $table) {
            $table->dropColumn(['rfq_number', 'is_sent_to_user', 'sent_to_user_at']);
        });
    }
};