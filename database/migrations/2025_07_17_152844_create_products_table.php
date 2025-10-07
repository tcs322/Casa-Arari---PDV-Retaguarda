<?php

use App\Enums\TipoProdutoEnum;
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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();
            $table->string('codigo')->unique();
            $table->string('nome_titulo');
            $table->decimal('preco');
            $table->integer('estoque');
            $table->string('autor')->nullable();
            $table->integer('edicao')->nullable();
            $table->enum('tipo', TipoProdutoEnum::getValues())->default(TipoProdutoEnum::LIVRARIA());
            $table->string('nota_uuid')->nullable();
            $table->foreignUuid('fornecedor_uuid')->references('uuid')->on('fornecedores');

            // Campos fiscais obrigatórios
            $table->string('ncm', 8)->default('49019900');
            $table->string('cest', 7)->nullable();
            $table->string('codigo_barras', 14)->nullable();
            $table->string('unidade_medida', 6)->default('UN');
            
            // Campos para classificação tributária
            $table->decimal('aliquota_icms', 5, 2)->default(0);
            $table->string('cst_icms', 3)->default('00');
            $table->string('cst_pis', 2)->default('07');
            $table->string('cst_cofins', 2)->default('07');
            
            // CFOP (Código Fiscal de Operações)
            $table->string('cfop', 4)->default('5102');
            
            // Origem da mercadoria (0-Nacional, 1-Estrangeira, etc.)
            $table->string('origem', 1)->default('0');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
