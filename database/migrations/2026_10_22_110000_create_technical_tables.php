<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vessel_surveys', function (Blueprint $table) {

            $table->id();

            $table->foreignId('master_location_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('survey_type');

            $table->date('planned_date');

            $table->date('actual_date')
                ->nullable();

            $table->text('remarks')
                ->nullable();

            $table->timestamps();
        });

        Schema::create('damage_reports', function (Blueprint $table) {

            $table->id();

            $table->foreignId('master_location_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->date('reported_date');

            $table->string('title');

            $table->text('description')
                ->nullable();

            $table->string('priority');

            $table->string('status')
                ->default('Open');

            $table->date('closed_date')
                ->nullable();

            $table->text('action_taken')
                ->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('damage_reports');
        Schema::dropIfExists('vessel_surveys');
    }
};
