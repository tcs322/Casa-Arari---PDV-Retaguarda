<?php
// app/Console/Commands/TestarConexaoSefaz.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Nota\SefaApiService;

class TestarConexaoSefa extends Command
{
    protected $signature = 'sefa:testar-conexao';
    protected $description = 'Testar conexão com SEFAZ homologação';

    public function handle()
    {
        $this->info('🌐 Testando conexão com SEFAZ Homologação...');
        
        try {
            $sefazService = new SefaApiService();
            $resultado = $sefazService->consultarStatusServico();
            
            if ($resultado['success']) {
                $this->info('✅ CONEXÃO COM SEFAZ: OK!');
                $this->line("📡 Status: {$resultado['mensagem']}");
                $this->line("🏭 Ambiente: {$resultado['ambiente']}");
                $this->line("📊 Versão: {$resultado['versao']}");
                $this->line("⏱️ Tempo médio: {$resultado['tempo_medio']} segundos");
                
                $this->line("");
                $this->info('🎉 PRONTO PARA EMITIR NOTAS!');
            } else {
                $this->error('❌ SEFAZ indisponível: ' . $resultado['erro']);
                return Command::FAILURE;
            }
            
        } catch (\Exception $e) {
            $this->error('❌ Erro na conexão: ' . $e->getMessage());
            return Command::FAILURE;
        }
        
        return Command::SUCCESS;
    }
}