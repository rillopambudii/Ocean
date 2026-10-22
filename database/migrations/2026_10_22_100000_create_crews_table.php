<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crews', function (Blueprint $table) {

            $table->id();

            // Kapal tempat kru bertugas; kosong = sedang di darat / standby.
            $table->foreignId('master_location_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->string('name');

            $table->string('rank');

            $table->string('seaman_book_number')
                ->nullable();

            $table->date('sign_on_date')
                ->nullable();

            $table->date('sign_off_date')
                ->nullable();

            $table->text('remarks')
                ->nullable();

            $table->timestamps();
        });

        Schema::create('crew_certificates', function (Blueprint $table) {

            $table->id();

            $table->foreignId('crew_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('name');

            $table->string('certificate_number')
                ->nullable();

            $table->date('expired_date')
                ->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crew_certificates');
        Schema::dropIfExists('crews');
    }
};
