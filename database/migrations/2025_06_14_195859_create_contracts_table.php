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
        Schema::create('contracts', function (Blueprint $table) {
            $table->id();
            $table->string('contract');
            $table->foreignId('project_id')->constrained('projects');
            $table->boolean('contract_manager_status')->default(0);
            $table->boolean('project_manager_status')->default(0);
            $table->string('client_sign')->nullable();
            $table->boolean('status')->default(0);
            $table->string('client_edit_request')->nullable();
            $table->boolean('need_edit')->default(0);
            $table->boolean('admin_sign')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contracts');
    }
};
