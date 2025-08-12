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
        Schema::create('contato', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();
            $table->string('descricao');
            $table->string('telefone_comercial');
            $table->string('telefone_residencial');
            $table->string('celular_pessoal');
            $table->string('celular_comercial');
            $table->string('email');
            $table->timestamps();

            $table->foreignUuid('cliente_uuid')->references('uuid')->on('cliente');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contato');
    }
};
