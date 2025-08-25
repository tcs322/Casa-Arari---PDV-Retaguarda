<?php

use App\Enums\TipoDocumentoPessoaJuridicaEnum;
use App\Enums\TipoFornecedorEnum;
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
        Schema::create('fornecedores', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();
            $table->string("razao_social");
            $table->string("nome_fantasia");
            $table->string("documento");
            $table->enum('tipo_documento', TipoDocumentoPessoaJuridicaEnum::getValues())
                ->default(TipoDocumentoPessoaJuridicaEnum::CNPJ());
            $table->enum('tipo', TipoFornecedorEnum::getValues())
                ->default(TipoFornecedorEnum::Livraria());
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fornecedors');
    }
};
