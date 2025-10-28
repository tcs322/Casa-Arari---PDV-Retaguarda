<?php
// app/Console/Commands/DiagnosticarCertificado.php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class DiagnosticarCertificado extends Command
{
    protected $signature = 'sefa:diagnosticar-certificado';
    protected $description = 'Diagnosticar localização do certificado';

    public function handle()
    {
        $this->info('🔍 Diagnosticando localização do certificado...');
        
        $configPath = config('nfe.certificado_path');
        $storagePath = storage_path($configPath);
        $appPath = storage_path('app/' . $configPath);
        $basePath = base_path($configPath);
        
        $this->line("📋 Configuração nfe.certificado_path: \"{$configPath}\"");
        $this->line("");
        $this->line("📍 Caminhos sendo procurados:");
        $this->line("   1. storage_path(): {$storagePath}");
        $this->line("   2. storage_path('app/'): {$appPath}"); 
        $this->line("   3. base_path(): {$basePath}");
        $this->line("");
        
        // Testar cada caminho
        $caminhos = [
            'storage_path()' => $storagePath,
            "storage_path('app/')" => $appPath,
            'base_path()' => $basePath,
        ];
        
        foreach ($caminhos as $tipo => $caminho) {
            $existe = file_exists($caminho);
            $legivel = $existe && is_readable($caminho);
            
            $this->line("   {$tipo}:");
            $this->line("      Existe: " . ($existe ? '✅ SIM' : '❌ NÃO'));
            if ($existe) {
                $this->line("      Legível: " . ($legivel ? '✅ SIM' : '❌ NÃO'));
                $this->line("      Tamanho: " . filesize($caminho) . " bytes");
            }
        }
        
        // Recomendação final
        $this->line("");
        $this->info("💡 RECOMENDAÇÃO:");
        $this->line("   Coloque o certificado em: " . storage_path('app/certificates/seu_certificado.pfx'));
        
        return Command::SUCCESS;
    }
}