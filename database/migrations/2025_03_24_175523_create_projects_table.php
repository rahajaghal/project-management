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
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('project_type');
            $table->string('project_description');
            $table->string('requirements');
            $table->string('document')->nullable();
            $table->string('cooperation_type');
            $table->string('contact_time');
            $table->foreignId('client_id')->constrained('users');
            $table->foreignId('team_id')->nullable()->constrained('teams');
            $table->boolean('team_approved')->default(0);
            $table->enum('status',['pending','under_study','request_to_edit','rejected','approved','creating_contract','signed_by_client','signed_by_manager','developing','completed'])->default('pending');
            $table->boolean('private')->default(0);
            $table->date('start')->nullable();
            $table->date('end')->nullable();
            $table->double('review')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
