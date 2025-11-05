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
    Schema::create('caixas', function (Blueprint $table) {
        $table->id();
        $table->foreignUuid('usuario_uuid')->references('uuid')->on('users');
        $table->timestamp('data_abertura');
        $table->timestamp('data_fechamento')->nullable();
        $table->decimal('saldo_inicial', 10, 2)->default(0);
        $table->decimal('saldo_final', 10, 2)->nullable();
        $table->text('observacoes')->nullable();
        $table->timestamps();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('caixas');
    }
};
