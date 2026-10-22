<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendors', function (Blueprint $table) {

            $table->id();

            $table->string('name');

            // Sama dengan PurchaseRequest::CATEGORIES.
            $table->string('category');

            $table->string('contact')
                ->nullable();

            $table->string('approval_status')
                ->default('To Evaluate');

            $table->text('remarks')
                ->nullable();

            $table->timestamps();
        });

        Schema::create('vendor_evaluations', function (Blueprint $table) {

            $table->id();

            $table->foreignId('vendor_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->date('review_date');

            $table->string('reviewer')
                ->nullable();

            // Skor 1–5 per kriteria (VendorEvaluation::CRITERIA).
            $table->unsignedTinyInteger('quality');

            $table->unsignedTinyInteger('delivery');

            $table->unsignedTinyInteger('price');

            $table->unsignedTinyInteger('service');

            $table->text('remarks')
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
        Schema::dropIfExists('vendor_evaluations');
        Schema::dropIfExists('vendors');
    }
};
