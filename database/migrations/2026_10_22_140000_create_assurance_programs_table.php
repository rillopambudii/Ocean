<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assurance_programs', function (Blueprint $table) {

            $table->id();

            // Subject: kapal atau office (Master Data).
            $table->foreignId('master_location_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('assurance_type');

            $table->date('planned_date');

            $table->date('actual_date')
                ->nullable();

            $table->text('remarks')
                ->nullable();

            $table->unsignedInteger('nc_open')
                ->default(0);

            $table->unsignedInteger('nc_closed')
                ->default(0);

            $table->string('evidence_path')
                ->nullable();

            $table->string('evidence_name')
                ->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assurance_programs');
    }
};
