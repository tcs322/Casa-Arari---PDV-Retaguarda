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
            $table->integer('quantidade_parcelas')->nullable();
            $table->decimal('valor_total', 10, 2);
            
            // Novos campos adicionados
            $table->decimal('valor_recebido', 10, 2)->default(0);
            $table->decimal('troco', 10, 2)->default(0);
            $table->string('numero_nota_fiscal')->nullable();
            $table->enum('status', ['pendente', 'finalizada', 'cancelada'])->default('pendente');
            $table->text('observacoes')->nullable();
            $table->timestamp('data_venda')->useCurrent();

            $table->string('chave_acesso_nfe', 44)->nullable();
            $table->text('xml_nfe')->nullable();
            $table->string('status_nfe')->default('pendente');
            $table->text('erro_nfe')->nullable();
            
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
