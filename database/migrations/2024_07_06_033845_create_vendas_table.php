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
            $table->foreignUuid('cliente_uuid')->references('uuid')->on('clientes');
            $table->enum('forma_pagamento', FormaPagamentoEnum::getValues());
            $table->enum('bandeira_cartao', BandeiraCartaoEnum::getValues())->nullable();
            $table->integer('quantidade_parcelas')->nullable();
            $table->decimal('valor_total', 10, 2);
            
            // Campos de pagamento
            $table->decimal('valor_recebido', 10, 2)->default(0);
            $table->decimal('troco', 10, 2)->default(0);
            
            // Campos de numeração NF-e
            $table->string('numero_nota_fiscal')->nullable();
            $table->string('serie_nfe')->nullable();
            
            // Status da venda
            $table->enum('status', ['pendente', 'finalizada', 'cancelada'])->default('pendente');
            $table->text('observacoes')->nullable();
            $table->timestamp('data_venda')->useCurrent();

            // ✅ CAMPOS NF-e COMPLETOS E CORRETOS
            $table->string('chave_acesso_nfe', 44)->nullable()->comment('Chave de acesso da NF-e (44 dígitos)');
            $table->string('protocolo_nfe', 50)->nullable()->comment('Número do protocolo de autorização');
            $table->timestamp('data_autorizacao_nfe')->nullable()->comment('Data/hora da autorização');
            $table->string('protocolo_cancelamento_nfe', 50)->nullable()->comment('Número do protocolo de cancelamento');
            $table->timestamp('data_cancelamento_nfe')->nullable()->comment('Data/hora do cancelamento');
            $table->text('xml_nfe')->nullable()->comment('XML original enviado');
            $table->text('xml_autorizado')->nullable()->comment('XML autorizado com protocolo');
            $table->enum('status_nfe', ['pendente', 'contingencia', 'autorizada', 'rejeitada', 'cancelada', 'erro'])->default('pendente');
            $table->text('erro_nfe')->nullable()->comment('Mensagem de erro em caso de rejeição');
            $table->timestamp('ultima_tentativa_reenvio')->nullable();
            $table->string('erro_reenvio_nfe')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendas');
    }
};
