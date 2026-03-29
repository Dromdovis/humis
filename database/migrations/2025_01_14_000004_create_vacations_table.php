<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vacations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->foreignId('default_substitute_id')->nullable()->constrained('employees')->onDelete('set null');
            $table->date('start_date');
            $table->date('end_date');
            $table->string('status')->default('pending'); // pending, approved, processed, completed
            $table->string('bss_reference')->nullable(); // Reference from BSS system
            $table->text('notes')->nullable();
            $table->boolean('tasks_reassigned')->default(false);
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });

        // Individual task assignments for vacation (task-by-task flexibility)
        Schema::create('vacation_task_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vacation_id')->constrained()->onDelete('cascade');
            $table->string('clickup_task_id');
            $table->string('task_name');
            $table->foreignId('substitute_id')->nullable()->constrained('employees')->onDelete('set null');
            $table->boolean('is_excluded')->default(false); // True = don't reassign this task
            $table->string('exclude_reason')->nullable(); // Why excluded
            $table->integer('time_estimate_hours')->nullable(); // From ClickUp
            $table->date('due_date')->nullable();
            $table->string('priority')->nullable(); // urgent, high, normal, low
            $table->boolean('is_processed')->default(false); // Has been synced to ClickUp
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vacation_task_assignments');
        Schema::dropIfExists('vacations');
    }
};
