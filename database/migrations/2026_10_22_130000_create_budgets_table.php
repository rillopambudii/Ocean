<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('budgets', function (Blueprint $table) {

            $table->id();

            $table->foreignId('master_location_id')
                ->constrained()
                ->cascadeOnDelete();

            // Sama dengan PurchaseRequest::CATEGORIES supaya realisasi bisa dicocokkan.
            $table->string('category');

            $table->unsignedSmallInteger('year');

            $table->unsignedTinyInteger('month');

            $table->decimal('amount', 15, 2);

            $table->timestamps();

            $table->unique(['master_location_id', 'category', 'year', 'month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('budgets');
    }
};
