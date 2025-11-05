<?php

namespace App\Http\Controllers\App\Caixa;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Caixa;
use App\Models\Venda;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

class CaixaController extends Controller
{
    /**
     * Abre um novo caixa para o usuário logado
     */
    public function abrirCaixa(Request $request)
    {
        $usuario = Auth::user();

        // 🔹 1. Verifica se já existe um caixa aberto para este usuário
        $caixaAberto = Caixa::where('usuario_uuid', $usuario->uuid)
            ->whereNull('data_fechamento')
            ->first();

        if ($caixaAberto) {
            return back()->with('warning', [
                'title' => 'Caixa já aberto',
                'message' => 'Você já possui um caixa aberto iniciado em ' . $caixaAberto->data_abertura->format('d/m/Y H:i') . '.'
            ]);
        }

        // 🔹 2. Cria novo registro de caixa
        $saldoInicial = $request->input('saldo_inicial', 0.00);
        $observacoes = $request->input('observacoes', null);

        $novoCaixa = Caixa::create([
            'usuario_uuid' => $usuario->uuid,
            'data_abertura' => Carbon::now(),
            'saldo_inicial' => $saldoInicial,
            'observacoes' => $observacoes,
        ]);

        Log::info("💵 Novo caixa aberto pelo usuário {$usuario->name} (ID: {$usuario->uuid})");

        return redirect()
            ->route('frente-caixa')
            ->with('message', 'Caixa aberto com sucesso às ' . $novoCaixa->data_abertura->format('H:i:s') . '.' );
    }

    public function fecharCaixa(Request $request)
    {
        // 🔍 Verifica se há um caixa aberto
        $caixaAberto = Caixa::whereNull('data_fechamento')->first();

        if (!$caixaAberto) {
            return redirect()
                ->route('dashboard.index')
                ->with('error', '⚠️ Não há nenhum caixa aberto no momento para ser fechado.');
        }
        
        $dataInicio = now()->startOfDay();
        $dataFim = now()->endOfDay();

        $vendas = Venda::finalizadas()
            ->doPeriodo($dataInicio, $dataFim)
            ->get();

        if ($vendas->isEmpty()) {
            return response("<pre>⚠️ Nenhuma venda encontrada para o período de hoje.</pre>");
        }

        // --- Totais e agrupamentos ---
        $totalBruto = $vendas->sum('valor_total');
        $totalDescontos = 0;
        $totalCanceladas = Venda::canceladas()->doPeriodo($dataInicio, $dataFim)->sum('valor_total');
        $totalLiquido = $totalBruto - $totalDescontos - $totalCanceladas;

        $pagamentos = $vendas->groupBy('forma_pagamento')->map(fn($grupo) =>
            $grupo->sum('valor_recebido')
        )->toArray();

        $dados = [
            'operador' => auth()->user()->name ?? 'Operador Desconhecido',
            'caixa_id' => 'PDV-01',
            'abertura' => $dataInicio->format('d/m/Y H:i'),
            'fechamento' => $dataFim->format('d/m/Y H:i'),

            'vendas' => [
                'bruto' => $totalBruto,
                'descontos' => $totalDescontos,
                'cancelamentos' => $totalCanceladas,
                'liquido' => $totalLiquido,
            ],

            'pagamentos' => $pagamentos,

            'totais' => [
                'recebido' => array_sum($pagamentos),
            ],

            'movimentos' => [
                'saldo_inicial' => 0.00,
                'suprimentos' => 0.00,
                'sangrias' => 0.00,
                'saldo_final_esperado' => array_sum($pagamentos),
            ],

            'conferencia' => [
                'valor_contado' => array_sum($pagamentos),
                'diferenca' => 0.00,
            ],

            'observacoes' => "Relatório gerado automaticamente às " . now()->format('H:i:s'),
        ];

            // --- Atualiza o saldo_final do caixa aberto ---
        $caixaAberto = Caixa::whereNull('data_fechamento')->latest()->first();

        if ($caixaAberto) {
            $caixaAberto->update([
                'saldo_final' => $totalLiquido,
                'data_fechamento' => now(),
            ]);
        }

        // --- Gera o texto do relatório ---
        $texto = $this->gerarTextoFechamentoCaixa($dados);

        // --- Converte encoding para impressora térmica ---
        $texto = iconv('UTF-8', 'ASCII//TRANSLIT', $texto);

        // --- Loga o conteúdo ---
        Log::info("📋 Relatório de Fechamento de Caixa\n" . $texto);

        // --- Tenta imprimir ---
        try {
            $printerServerUrl = "http://host.docker.internal:8081";
            $payload = [
                'texto' => $texto,
                'impressora' => '71840', // ID da impressora padrão
            ];

            $response = Http::post($printerServerUrl, $payload);

            if ($response->successful()) {
                Log::info("🖨️ Relatório de fechamento enviado para impressão.");
                return redirect()->route('dashboard.index')->with("message", "✅ Fechamento realizado e relatório impresso com sucesso!");
            } else {
                Log::error("❌ Falha ao imprimir relatório", ['response' => $response->body()]);
                return redirect()->route('dashboard.index')->with("message", "⚠️ Fechamento realizado, mas houve erro ao imprimir");
            }

        } catch (\Exception $e) {
            Log::error("Erro ao enviar relatório ao servidor de impressão", ['error' => $e->getMessage()]);
            return response("<pre>⚠️ Fechamento gerado, mas não foi possível imprimir.\n{$e->getMessage()}</pre>");
        }
    }

    /**
     * Gera o texto do relatório de fechamento de caixa.
     */
    private function gerarTextoFechamentoCaixa(array $dados): string
    {
        $empresa = config('nfe');

        $texto = "";
        $texto .= "     {$empresa['nome_fantasia']}     \n";
        $texto .= "{$empresa['razao_social']}\n";
        $texto .= "CNPJ: {$empresa['cnpj']}\n";
        $texto .= "{$empresa['municipio']} - {$empresa['uf']}\n";
        $texto .= "--------------------------------\n";
        $texto .= "      FECHAMENTO DE CAIXA       \n";
        $texto .= "================================\n";

        $texto .= "Operador: {$dados['operador']}\n";
        $texto .= "Caixa: {$dados['caixa_id']}\n";
        $texto .= "Abertura: {$dados['abertura']}\n";
        $texto .= "Fechamento: {$dados['fechamento']}\n";
        $texto .= "--------------------------------\n";

        $texto .= "TOTAL DE VENDAS........: R$ " . number_format($dados['vendas']['bruto'], 2, ',', '') . "\n";
        $texto .= "DESCONTOS..............: R$ " . number_format($dados['vendas']['descontos'], 2, ',', '') . "\n";
        $texto .= "CANCELAMENTOS..........: R$ " . number_format($dados['vendas']['cancelamentos'], 2, ',', '') . "\n";
        $texto .= "--------------------------------\n";
        $texto .= "VENDAS LÍQUIDAS........: R$ " . number_format($dados['vendas']['liquido'], 2, ',', '') . "\n";
        $texto .= "================================\n";

        $texto .= "FORMAS DE PAGAMENTO:\n";
        foreach ($dados['pagamentos'] as $forma => $valor) {
            $texto .= sprintf("  %-20s R$ %8s\n", ucfirst($forma), number_format($valor, 2, ',', ''));
        }
        $texto .= "--------------------------------\n";
        $texto .= "TOTAL RECEBIDO.........: R$ " . number_format($dados['totais']['recebido'], 2, ',', '') . "\n";
        $texto .= "================================\n";

        $texto .= "MOVIMENTAÇÕES:\n";
        $texto .= "  Saldo inicial........: R$ " . number_format($dados['movimentos']['saldo_inicial'], 2, ',', '') . "\n";
        $texto .= "  Suprimentos..........: R$ " . number_format($dados['movimentos']['suprimentos'], 2, ',', '') . "\n";
        $texto .= "  Sangrias.............: R$ " . number_format($dados['movimentos']['sangrias'], 2, ',', '') . "\n";
        $texto .= "--------------------------------\n";
        $texto .= "SALDO FINAL ESPERADO...: R$ " . number_format($dados['movimentos']['saldo_final_esperado'], 2, ',', '') . "\n";
        $texto .= "================================\n";

        $texto .= "VALOR CONTADO..........: R$ " . number_format($dados['conferencia']['valor_contado'], 2, ',', '') . "\n";
        $texto .= "DIFERENÇA..............: R$ " . number_format($dados['conferencia']['diferenca'], 2, ',', '') . "\n";
        $texto .= "================================\n";

        if (!empty($dados['observacoes'])) {
            $texto .= "OBSERVAÇÕES:\n";
            $texto .= "{$dados['observacoes']}\n";
            $texto .= "--------------------------------\n";
        }

        $texto .= "\nAss. Operador: ___________\n";
        $texto .= "Ass. Gerente:  ___________\n";
        $texto .= "================================\n";
        $texto .= "        FIM DO RELATÓRIO        \n";
        $texto .= "================================\n\n\n";

        return $texto;
    }
}
