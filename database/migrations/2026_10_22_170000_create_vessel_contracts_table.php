<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Kontrak charter per kapal; kontrak berikutnya = baris dengan start di masa depan.
        Schema::create('vessel_contracts', function (Blueprint $table) {

            $table->id();

            $table->foreignId('master_location_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('charterer');

            $table->date('start_date');

            $table->date('end_date');

            $table->text('remarks')
                ->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vessel_contracts');
    }
};
