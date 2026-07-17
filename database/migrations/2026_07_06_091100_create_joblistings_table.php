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
        Schema::create('joblistings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->text('description');
            $table->string('category', 255);
            $table->string('location', 255);
            $table->enum('work_mode', ['remote', 'on-site', 'hybrid'])->default('remote');
            $table->enum('job_type', ['full-time', 'part-time', 'internship', 'contract'])->default('full-time');
            $table->enum('experience_level', ['entry', 'mid', 'senior'])->default('entry');
            $table->string('salary', 255)->nullable();
            $table->text('requirements')->nullable();
            $table->text('responsibilities')->nullable();
            $table->text('benefits')->nullable();
            $table->date('application_deadline')->nullable();
             $table->enum('status', ['open', 'closed'])->default('open');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('joblistings');
    }
};
