<?php
// app/Console/Commands/TestarCertificadoSefaz.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use NFePHP\Common\Certificate;
use Exception;

class TestarCertificadoSefa extends Command
{
    protected $signature = 'sefa:testar-certificado';
    protected $description = 'Testar certificado digital e conexão com SEFAZ';

    public function handle()
    {
        $this->info('🔐 Testando certificado digital...');
        
        try {
            // Testar certificado
            $certificate = $this->carregarCertificado();
            $this->info('✅ Certificado carregado com sucesso!');
            
            // Mostrar informações CORRETAS do certificado
            $validTo = $certificate->getValidTo();
            $cnpj = $certificate->getCnpj();
            
            $this->line("🏢 CNPJ: {$cnpj}");
            $this->line("📅 Válido até: " . $validTo->format('d/m/Y'));
            
            // Verificar validade
            if ($validTo < now()) {
                $this->error('❌ Certificado EXPIRADO!');
                return Command::FAILURE;
            }
            
            $diasRestantes = $validTo->diff(now())->days;
            $this->info("✅ Certificado válido ({$diasRestantes} dias restantes)");
            
            // Verificar se é o CNPJ correto
            $cnpjConfig = config('nfe.cnpj');
            if ($cnpj !== $cnpjConfig) {
                $this->warn("⚠️  ATENÇÃO: CNPJ do certificado ({$cnpj}) difere do configurado ({$cnpjConfig})");
            } else {
                $this->info('✅ CNPJ do certificado confere com configuração');
            }
            
            $this->line("");
            $this->info('🎉 Certificado testado com SUCESSO!');
            
        } catch (Exception $e) {
            $this->error('❌ Erro ao carregar certificado: ' . $e->getMessage());
            $this->line('💡 Possíveis causas:');
            $this->line('   - Senha incorreta no .env (NFE_CERTIFICADO_SENHA)');
            $this->line('   - Certificado corrompido');
            $this->line('   - Formato inválido (deve ser .pfx ou .p12)');
            return Command::FAILURE;
        }
        
        return Command::SUCCESS;
    }
    
    private function carregarCertificado(): Certificate
    {
        $certificatePath = storage_path('app/' . config('nfe.certificado_path'));
        $certificatePassword = config('nfe.certificado_senha');
        
        if (!file_exists($certificatePath)) {
            throw new Exception("Certificado não encontrado: {$certificatePath}");
        }
        
        // Verificar se a senha foi configurada
        if (empty($certificatePassword)) {
            throw new Exception("Senha do certificado não configurada no .env (NFE_CERTIFICADO_SENHA)");
        }
        
        return Certificate::readPfx(
            file_get_contents($certificatePath), 
            $certificatePassword
        );
    }
}