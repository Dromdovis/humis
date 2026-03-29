<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('clickup_space_id')->nullable();
            $table->string('clickup_folder_id')->nullable();
            $table->string('clickup_list_id')->nullable();
            $table->string('name');
            $table->string('client_name')->nullable(); // Hobiverse, etc.
            $table->text('description')->nullable();
            $table->string('status')->default('active'); // active, on_hold, completed
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Pivot table for project skills (tech stack)
        Schema::create('project_skills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->foreignId('skill_id')->constrained()->onDelete('cascade');
            $table->tinyInteger('required_level')->default(1); // Minimum skill level needed
            $table->boolean('is_primary')->default(false); // Main technology for project
            $table->timestamps();

            $table->unique(['project_id', 'skill_id']);
        });

        // Pivot table for project team members (main assignees)
        Schema::create('project_employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->string('role')->default('member'); // lead, member
            $table->timestamps();

            $table->unique(['project_id', 'employee_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_employees');
        Schema::dropIfExists('project_skills');
        Schema::dropIfExists('projects');
    }
};
