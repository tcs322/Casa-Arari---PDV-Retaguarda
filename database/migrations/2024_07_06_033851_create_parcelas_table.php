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
        Schema::create('parcela', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();
            $table->integer('numero');
            $table->integer('quantidade_total_parcelas');
            $table->integer('quantidade_real_parcelas');
            $table->foreignUuid('compra_uuid')->references('uuid')->on('compra');
            $table->foreignUuid('cliente_uuid')->references('uuid')->on('cliente');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parcela');
    }
};
