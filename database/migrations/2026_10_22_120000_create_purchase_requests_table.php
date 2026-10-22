<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_requests', function (Blueprint $table) {

            $table->id();

            // Diisi setelah insert: PR-{tahun}-{id}.
            $table->string('request_number')
                ->nullable()
                ->unique();

            $table->foreignId('master_location_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('title');

            $table->string('category');

            $table->date('request_date');

            $table->string('status')
                ->default('Diajukan');

            $table->string('vendor')
                ->nullable();

            $table->string('po_number')
                ->nullable();

            $table->date('received_date')
                ->nullable();

            $table->string('attachment_path')
                ->nullable();

            $table->string('attachment_name')
                ->nullable();

            $table->text('remarks')
                ->nullable();

            $table->foreignId('requested_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();
        });

        Schema::create('purchase_request_items', function (Blueprint $table) {

            $table->id();

            $table->foreignId('purchase_request_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('description');

            $table->decimal('quantity', 12, 2);

            $table->string('unit', 30);

            $table->decimal('unit_price', 15, 2)
                ->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_request_items');
        Schema::dropIfExists('purchase_requests');
    }
};
