<?php
// app/Console/Commands/DebugSefazCompleto.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Nota\SefaApiService;
use NFePHP\NFe\Tools;
use NFePHP\Common\Certificate;

class DebugSefaz extends Command
{
    protected $signature = 'sefa:debug-completo';
    protected $description = 'Debug completo da conexão SEFAZ';

    public function handle()
    {
        $this->info('🔧 Debug completo da conexão SEFAZ...');
        
        try {
            // 1. Testar certificado
            $this->testarCertificado();
            
            // 2. Testar configuração
            $this->testarConfiguracao();
            
            // 3. Testar conexão manualmente
            $this->testarConexaoManual();
            
        } catch (\Exception $e) {
            $this->error('❌ Erro: ' . $e->getMessage());
            $this->line('💡 Stack trace: ' . $e->getTraceAsString());
        }
        
        return Command::SUCCESS;
    }
    
    private function testarCertificado()
    {
        $this->info('🔐 Testando certificado...');
        
        $certificatePath = storage_path('app/' . config('nfe.certificado_path'));
        $certificatePassword = config('nfe.certificado_senha');
        
        $certificate = Certificate::readPfx(
            file_get_contents($certificatePath), 
            $certificatePassword
        );
        
        $this->line('✅ Certificado válido');
        $this->line('   CNPJ: ' . $certificate->getCnpj());
        $this->line('   Válido até: ' . $certificate->getValidTo()->format('d/m/Y'));
    }
    
    private function testarConfiguracao()
    {
        $this->info('📋 Testando configuração...');
        
        $config = [
            "atualizacao" => date('Y-m-d H:i:s'),
            "tpAmb" => (int) config('nfe.ambiente', 2),
            "razaosocial" => config('nfe.razao_social'),
            "cnpj" => config('nfe.cnpj'),
            "siglaUF" => config('nfe.uf'),
            "schemes" => "PL_009_V4",
            "versao" => "4.00",
            "tokenIBPT" => "",
            "CSC" => config('nfe.csc', ''),
            "CSCid" => config('nfe.csc_id', ''),
        ];
        
        foreach ($config as $key => $value) {
            $status = empty($value) ? '❌' : '✅';
            $this->line("   {$status} {$key}: " . (is_array($value) ? json_encode($value) : $value));
        }
    }
    
    private function testarConexaoManual()
    {
        $this->info('🌐 Testando conexão manual...');
        
        $certificatePath = storage_path('app/' . config('nfe.certificado_path'));
        $certificatePassword = config('nfe.certificado_senha');
        
        $config = [
            "atualizacao" => date('Y-m-d H:i:s'),
            "tpAmb" => 2, // Forçar homologação
            "razaosocial" => config('nfe.razao_social'),
            "cnpj" => config('nfe.cnpj'),
            "siglaUF" => 'PA', // Forçar Pará
            "schemes" => "PL_009_V4",
            "versao" => "4.00",
            "tokenIBPT" => "",
            "CSC" => config('nfe.csc', ''),
            "CSCid" => config('nfe.csc_id', ''),
        ];
        
        $certificate = Certificate::readPfx(
            file_get_contents($certificatePath), 
            $certificatePassword
        );
        
        $tools = new Tools(json_encode($config), $certificate);
        
        try {
            // Tentar com timeout maior e debug
            $response = $tools->sefazStatus();
            $this->info('✅ Resposta recebida da SEFAZ');
            $this->line('📄 Resposta: ' . substr($response, 0, 200) . '...');
            
        } catch (\Exception $e) {
            $this->error('❌ Erro na chamada: ' . $e->getMessage());
            
            // Log detalhado do erro
            $this->line('🔍 Detalhes do erro:');
            $this->line('   Código: ' . $e->getCode());
            $this->line('   Arquivo: ' . $e->getFile());
            $this->line('   Linha: ' . $e->getLine());
        }
    }
}