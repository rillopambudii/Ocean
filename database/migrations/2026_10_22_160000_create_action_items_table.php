<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Satu action tracker lintas departemen (Weekly Operations / Vessel Follow Up List).
        Schema::create('action_items', function (Blueprint $table) {

            $table->id();

            $table->foreignId('master_location_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('title');

            $table->string('department');

            $table->string('lead_pic')
                ->nullable();

            $table->string('supporting_department')
                ->nullable();

            $table->string('priority');

            $table->date('target_date')
                ->nullable();

            $table->date('actual_date')
                ->nullable();

            $table->string('status')
                ->default('Open');

            $table->text('next_action')
                ->nullable();

            $table->string('evidence_path')
                ->nullable();

            $table->string('evidence_name')
                ->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('action_items');
    }
};
