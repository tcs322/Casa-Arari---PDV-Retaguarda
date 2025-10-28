<?php
// app/Console/Commands/TestarSenhaCertificado.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use NFePHP\Common\Certificate;

class TestarSenhaCertificado extends Command
{
    protected $signature = 'sefa:testar-senha {senha?}';
    protected $description = 'Testar diferentes senhas para o certificado';

    public function handle()
    {
        $senhaTeste = $this->argument('senha') ?: config('nfe.certificado_senha');
        
        $this->info("🔐 Testando senha: " . ($senhaTeste ? str_repeat('*', strlen($senhaTeste)) : '[Vazia]'));
        
        $certificatePath = storage_path('app/' . config('nfe.certificado_path'));
        
        try {
            $certificate = Certificate::readPfx(
                file_get_contents($certificatePath), 
                $senhaTeste
            );
            
            $this->info('✅ ✅ ✅ SENHA CORRETA! ✅ ✅ ✅');
            $this->line("🏢 CNPJ: " . $certificate->getCnpj());
            $this->line("📅 Válido até: " . $certificate->getValidTo()->format('d/m/Y'));
            
            $this->line("");
            $this->info('💡 ATUALIZE SEU .ENV:');
            $this->line("NFE_CERTIFICADO_SENHA={$senhaTeste}");
            
        } catch (\Exception $e) {
            $this->error('❌ Senha incorreta ou certificado inválido');
            
            $this->line("");
            $this->warn("💡 Tente estas senhas comuns:");
            $this->line("   php artisan sefa:testar-senha 1234");
            $this->line("   php artisan sefa:testar-senha 123456");
            $this->line("   php artisan sefa:testar-senha 12345678");
            $this->line("   php artisan sefa:testar-senha senha");
            $this->line("   php artisan sefa:testar-senha password");
            $this->line("   php artisan sefa:testar-senha [CNPJ sem pontuação]");
            $this->line("   php artisan sefa:testar-senha [Nome da empresa]");
        }
        
        return Command::SUCCESS;
    }
}