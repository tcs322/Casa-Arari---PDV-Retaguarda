<?php

use App\Enums\BandeiraCartaoEnum;
use App\Enums\FormaPagamentoEnum;
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
        Schema::create('vendas', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignUuid('usuario_uuid')->references('uuid')->on('users');
            $table->enum('forma_pagamento', FormaPagamentoEnum::getValues());
            $table->enum('bandeira_cartao', BandeiraCartaoEnum::getValues())->nullable();
            $table->decimal('valor_total', 10, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('venda');
    }
};
