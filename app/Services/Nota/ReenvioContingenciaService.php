<?php

namespace App\Services\Nota;

use App\Models\Venda;
use Illuminate\Support\Facades\Log;
use Exception;

class ReenvioContingenciaService
{
    protected $sefaz;

    public function __construct()
    {
        $this->sefaz = new SefaApiService();
    }

    /**
     * Reenvia todas as NF-es em contingência para a SEFAZ.
     */
    public function reenviarPendentes(): void
    {
        Log::info("🔁 Iniciando verificação de NF-es em contingência...");

        $vendas = Venda::where('status_nfe', 'contingencia')
            ->get();

        if ($vendas->isEmpty()) {
            Log::info("✅ Nenhuma NF-e em contingência encontrada para reenvio.");
            return;
        }

        Log::info("📦 Encontradas {$vendas->count()} NF-es em contingência para reenviar.");

        foreach ($vendas as $venda) {
            try {
                Log::info("➡️ Tentando reenviar NF-e da venda #{$venda->id} | Chave anterior: {$venda->chave_acesso_nfe}");

                $xmlAssinado = $venda->xml_nfe;

                // 🔄 Tenta reenviar para a SEFAZ
                $resultado = $this->sefaz->autorizarNFe($xmlAssinado);

                if (!isset($resultado['success'])) {
                    throw new Exception('Retorno inesperado da SEFAZ');
                }

                if ($resultado['success'] === true) {
                    $venda->update([
                        'status_nfe' => 'autorizada',
                        'status' => 'finalizada',
                        'chave_acesso_nfe' => $resultado['chave_acesso'] ?? $venda->chave_acesso_nfe,
                        'protocolo_nfe' => $resultado['numero_protocolo'] ?? null,
                        'data_autorizacao_nfe' => $resultado['data_autorizacao'] ?? now(),
                        'erro_reenvio_nfe' => null,
                        'ultima_tentativa_reenvio' => now(),
                    ]);

                    Log::info("✅ NF-e da venda #{$venda->id} reenviada e autorizada com sucesso. Protocolo: {$resultado['numero_protocolo']}");
                } else {
                    $erro = $resultado['erro'] ?? 'Erro desconhecido';
                    $codigo = $resultado['codigo_erro'] ?? '---';

                    Log::warning("⚠️ NF-e da venda #{$venda->id} ainda rejeitada [{$codigo}]: {$erro}");

                    $venda->update([
                        'ultima_tentativa_reenvio' => now(),
                        'erro_reenvio_nfe' => "{$codigo} - {$erro}",
                    ]);
                }
            } catch (Exception $e) {
                Log::error("❌ Erro ao reenviar NF-e da venda #{$venda->id}: " . $e->getMessage());

                $venda->update([
                    'ultima_tentativa_reenvio' => now(),
                    'erro_reenvio_nfe' => $e->getMessage(),
                ]);
            }
        }

        Log::info("🏁 Reenvio automático de NF-es finalizado.");
    }
}
