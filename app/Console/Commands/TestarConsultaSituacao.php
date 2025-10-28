<?php
// app/Console/Commands/TestarConsultaSituacao.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Nota\SefaApiService;

class TestarConsultaSituacao extends Command
{
    protected $signature = 'sefa:testar-consulta';
    protected $description = 'Testar consulta de situação na SEFAZ';

    public function handle()
    {
        $this->info('🔍 Testando consulta de situação na SEFAZ...');
        
        try {
            $sefazService = new SefaApiService();
            
            // Usar uma chave de NFe de teste (44 primeiros dígitos + DV)
            $chaveTeste = '15102562000159000163550010000000010000000010'; // Chave genérica de teste
            
            $resultado = $sefazService->consultarSituacao($chaveTeste);
            
            if ($resultado['success']) {
                $this->info('✅ Consulta funcionando!');
                $this->line("Situação: {$resultado['situacao']}");
                $this->line("Motivo: {$resultado['motivo']}");
            } else {
                $this->warn('⚠️ Consulta retornou erro (esperado para chave de teste):');
                $this->line("Erro: {$resultado['erro']}");
                $this->line("Isso é NORMAL para uma chave que não existe!");
            }
            
        } catch (\Exception $e) {
            $this->error('❌ Erro na consulta: ' . $e->getMessage());
        }
        
        return Command::SUCCESS;
    }
}