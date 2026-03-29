<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('project_manager'); // admin, project_manager
            $table->foreignId('employee_id')->nullable()->constrained()->onDelete('set null');
            $table->string('clickup_user_id')->nullable()->unique(); // For OAuth
            $table->boolean('is_active')->default(true);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['employee_id']);
            $table->dropColumn(['role', 'employee_id', 'clickup_user_id', 'is_active']);
        });
    }
};
