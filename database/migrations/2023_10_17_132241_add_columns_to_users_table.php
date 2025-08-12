<?php

use App\Enums\SituacaoUsuarioEnum;
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
        Schema::table('users', function (Blueprint $table) {
            $table->uuid();
            $table->enum('situacao', SituacaoUsuarioEnum::getValues())->default(SituacaoUsuarioEnum::ATIVO());
            $table->string('password')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('uuid');
            $table->dropColumn('situacao');
            $table->string('password')->nullable(false)->change();
        });
    }
};
