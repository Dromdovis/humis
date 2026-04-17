<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('vacation_task_assignments', function (Blueprint $table) {
            $table->string('task_status')->nullable()->after('priority');
            $table->string('task_status_color')->nullable()->after('task_status');
            $table->string('task_url')->nullable()->after('task_status_color');
            $table->json('task_tags')->nullable()->after('task_url');
            $table->date('start_date')->nullable()->after('due_date');
        });
    }

    public function down(): void
    {
        Schema::table('vacation_task_assignments', function (Blueprint $table) {
            $table->dropColumn(['task_status', 'task_status_color', 'task_url', 'task_tags', 'start_date']);
        });
    }
};
