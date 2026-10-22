<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('safety_observations', function (Blueprint $table) {

            $table->id();

            $table->foreignId('master_location_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->date('observed_date');

            $table->string('category');

            $table->text('description');

            $table->text('immediate_action')
                ->nullable();

            $table->string('pic')
                ->nullable();

            $table->date('due_date')
                ->nullable();

            $table->string('status')
                ->default('Open');

            $table->date('closed_date')
                ->nullable();

            $table->string('evidence_path')
                ->nullable();

            $table->string('evidence_name')
                ->nullable();

            $table->timestamps();
        });

        Schema::create('training_activities', function (Blueprint $table) {

            $table->id();

            $table->foreignId('master_location_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('activity_type');

            $table->string('topic');

            $table->date('planned_date');

            $table->date('actual_date')
                ->nullable();

            $table->unsignedInteger('participants')
                ->nullable();

            $table->string('instructor')
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
        Schema::dropIfExists('training_activities');
        Schema::dropIfExists('safety_observations');
    }
};
