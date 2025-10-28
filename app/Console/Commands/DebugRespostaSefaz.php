<?php
// app/Console/Commands/DebugRespostaSefaz.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Nota\SefaApiService;
use NFePHP\NFe\Tools;
use NFePHP\Common\Certificate;

class DebugRespostaSefaz extends Command
{
    protected $signature = 'sefa:debug-resposta';
    protected $description = 'Debug da resposta completa da SEFAZ';

    public function handle()
    {
        $this->info('🔍 Debug da resposta SEFAZ...');
        
        try {
            $certificatePath = storage_path('app/' . config('nfe.certificado_path'));
            $certificatePassword = config('nfe.certificado_senha');
            
            $config = [
                "atualizacao" => date('Y-m-d H:i:s'),
                "tpAmb" => 2,
                "razaosocial" => config('nfe.razao_social'),
                "cnpj" => config('nfe.cnpj'),
                "siglaUF" => 'PA',
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
            $response = $tools->sefazStatus();
            
            $this->info('✅ Resposta recebida:');
            $this->line("================================================");
            $this->line($response);
            $this->line("================================================");
            
            // Salvar em arquivo para análise
            file_put_contents(storage_path('logs/sefaz_response_completo.xml'), $response);
            $this->info('📄 Resposta salva em: storage/logs/sefaz_response_completo.xml');
            
            // Tentar parsear manualmente
            $this->parsearRespostaManual($response);
            
        } catch (\Exception $e) {
            $this->error('❌ Erro: ' . $e->getMessage());
        }
        
        return Command::SUCCESS;
    }
    
    private function parsearRespostaManual($response)
    {
        $this->info('🔧 Tentando parsear resposta...');
        
        $xml = simplexml_load_string($response);
        
        // Mostrar estrutura completa
        $this->line("Estrutura do XML:");
        $this->mostrarEstrutura($xml, 0);
    }
    
    private function mostrarEstrutura($elemento, $nivel)
    {
        $espacos = str_repeat('  ', $nivel);
        
        foreach ($elemento->children() as $filho) {
            $nome = $filho->getName();
            $valor = trim((string) $filho);
            
            if (!empty($valor) && strlen($valor) < 50) {
                $this->line("{$espacos}📄 {$nome}: {$valor}");
            } else {
                $this->line("{$espacos}📁 {$nome}");
            }
            
            $this->mostrarEstrutura($filho, $nivel + 1);
        }
    }
}