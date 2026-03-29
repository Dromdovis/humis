<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('clickup_user_id')->unique();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('role')->default('developer'); // developer, designer, pm, admin
            $table->string('position')->nullable(); // Junior Developer, Senior Developer, etc.
            $table->integer('max_weekly_hours')->default(40); // Max workload hours per week
            $table->string('color')->nullable(); // ClickUp color
            $table->string('profile_picture')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Pivot table for employee skills
        Schema::create('employee_skills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->foreignId('skill_id')->constrained()->onDelete('cascade');
            $table->tinyInteger('level')->default(1); // 1-5 skill level
            $table->timestamps();

            $table->unique(['employee_id', 'skill_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_skills');
        Schema::dropIfExists('employees');
    }
};
