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
        Schema::create('notas', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('numero_nota')->unique();
            $table->decimal('valor_total');
            $table->foreignUuid('fornecedor_uuid')->references('uuid')->on('fornecedores');
            $table->enum('tipo_nota', TipoProdutoEnum::getValues())->default(TipoProdutoEnum::LIVRARIA());
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notas');
    }
};
