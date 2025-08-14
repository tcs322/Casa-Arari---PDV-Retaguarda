<?php

use App\Enums\SituacaoUsuarioEnum;
use App\Enums\TipoUsuarioEnum;
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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique()->nullable(false);
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->enum('role', TipoUsuarioEnum::getValues())->default(TipoUsuarioEnum::OPERADOR());
            $table->boolean('must_change_password')->default(true);
            $table->enum('situacao', SituacaoUsuarioEnum::getValues())->default(SituacaoUsuarioEnum::ATIVO());
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
