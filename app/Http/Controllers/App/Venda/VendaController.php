<?php

namespace App\Http\Controllers\App\Venda;

use App\Actions\Venda\VendaAction;
use App\DTO\Venda\VendaShowDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\App\Venda\VendaShowRequest;
use App\Models\User;
use App\Models\Venda;
use App\Services\Nota\SefaApiService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class VendaController extends Controller
{
    public function __construct(
        protected VendaAction $action
    ) {}

    public function create()
    {
        return view ('app.venda.create');
    }

    public function pagamento()
    {
        return view('app.venda.pagamento');
    }

    public function index(Request $request)
    {
        $vendas = $this->action->paginate(
            page: $request->get('page', 1),
            totalPerPage: $request->get('per_page', 6),
            filter: $request->get('filter'),
        );

        $filters = ['filter' => $request->get('filter', '')];
        
        return view('app.venda.index', compact('vendas', 'filters'));
    }

    public function show(string $uuid, VendaShowRequest $request)
    {
        $request->merge([
            "uuid" => $uuid
        ]);

        $venda = $this->action->show(VendaShowDTO::makeFromRequest($request));

        return view('app.venda.show', ["venda" => $venda]);
    }

    public function cancelarVenda(string $vendaUuid)
    {
        
        $venda = Venda::with(['itens', 'cliente'])->where('uuid', $vendaUuid)->first();
        $sefaz = new SefaApiService();
    
        $resultado = $sefaz->cancelarNFe(
            $venda->chave_acesso_nfe,
            $venda->protocolo_nfe,
            'Cliente desistiu da compra',
            $venda->data_autorizacao_nfe
        );
    
        if ($resultado['success']) {
            $venda->update([
                'status' => 'cancelada',
                'status_nfe' => 'cancelada',
                'protocolo_cancelamento_nfe' => $resultado['numero_protocolo_cancelamento'],
                'data_cancelamento_nfe' => $resultado['data_cancelamento']
            ]);
    
            // ✅ Monta os dados do cupom de cancelamento
            $dadosCupom = [
                'venda' => [
                    'numero' => $venda->id,
                    'data' => $venda->created_at->format('d/m/Y H:i:s'),
                    'cliente' => $venda->cliente->nome ?? null,
                    'cpf_cnpj' => $venda->cliente->cpf_cnpj ?? null,
                ],
                'itens' => [], // geralmente o cupom de cancelamento não lista produtos
                'totais' => [
                    'subtotal' => $venda->total,
                    'desconto' => $venda->desconto ?? 0,
                    'total' => $venda->total,
                ],
                'pagamentos' => [],
                'nfe' => [
                    'chave_acesso' => $venda->chave_acesso_nfe,
                    'numero' => $venda->numero_nota_fiscal ?? 'N/A',
                    'serie' => $venda->serie_nfe ?? 'N/A',
                    'protocolo_cancelamento' => $resultado['protocolo_cancelamento_nfe'] ?? 'N/A',
                    'qrcode' => $venda->qrcode_url ?? null,
                ],
                'cancelamento' => [
                    'data' => $resultado['data_cancelamento'],
                    'motivo' => 'Cliente desistiu da compra',
                ],
            ];
    
            // ✅ Chama a impressão do cupom de cancelamento
            try {
                $this->imprimirCancelamento($venda->id, $dadosCupom);
            } catch (\Throwable $e) {
                Log::error("Erro ao imprimir cupom de cancelamento: " . $e->getMessage());
            }
    
            return redirect()->route('venda.index')->with('message', 'Nota cancelada e cupom impresso com sucesso!');
        } else {
            return redirect()->route('venda.index')->with('error', 'Erro ao cancelar nota: ' . $resultado['erro']);
        }
    }
    
    public function imprimirCancelamento(int $vendaId, array $dadosCupom, string $impressora = '71840'): bool
    {
        // Montar o texto do cupom de cancelamento
        $texto = $this->gerarTextoCancelamento($dadosCupom);
        
        // --- Converte encoding para impressora térmica ---
        $texto = iconv('UTF-8', 'ASCII//TRANSLIT', $texto);

        try {
            $response = Http::timeout(5)->post('http://host.docker.internal:8050/api/imprimir-direto', [
                'texto'     => $texto,
                'venda_id'  => $vendaId,
                'impressora'=> $impressora,
            ]);

            if ($response->successful() && $response->json('success')) {
                Log::info("✅ Cupom de CANCELAMENTO enviado para impressão venda #{$vendaId}");
                return true;
            } else {
                Log::warning("❌ Falha na impressão do cancelamento venda #{$vendaId}", [
                    'response'   => $response->body(),
                    'venda_id'   => $vendaId
                ]);
                return false;
            }
        } catch (\Exception $e) {
            Log::error("❌ Erro ao imprimir cancelamento venda #{$vendaId}", [
                'error'      => $e->getMessage(),
                'venda_id'   => $vendaId
            ]);
            return false;
        }
    }

    private function gerarTextoCancelamento(array $dadosCupom): string
    {
        $texto = "";

        // Carregue dados do emitente do config/nfe.php
        $nfeConfig = config('nfe');

        $razaoSocial  = $nfeConfig['razao_social'] ?? 'EMPRESA';
        $nomeFantasia = $nfeConfig['nome_fantasia'] ?? '';
        $cnpj         = $nfeConfig['cnpj'] ?? '';
        $ie           = $nfeConfig['ie'] ?? '';
        $logradouro   = $nfeConfig['logradouro'] ?? '';
        $numero       = $nfeConfig['numero'] ?? '';
        $bairro       = $nfeConfig['bairro'] ?? '';
        $municipio    = $nfeConfig['municipio'] ?? '';
        $uf           = $nfeConfig['uf'] ?? '';
        $cep          = $nfeConfig['cep'] ?? '';
        $telefone     = $nfeConfig['telefone'] ?? '';

        // Cabeçalho (empresa)
        $texto .= "      {$nomeFantasia}      \n";
        $texto .= "{$razaoSocial}\n";
        $texto .= "CNPJ: {$cnpj}\n";
        $texto .= "IE: {$ie}\n";
        $texto .= "{$logradouro}, {$numero} - {$bairro}\n";
        $texto .= "{$municipio} - {$uf}  CEP: {$cep}\n";
        if (!empty($telefone)) {
            $texto .= "TEL: {$telefone}\n";
        }
        $texto .= "--------------------------------\n";
        $texto .= "      CANCELAMENTO DE VENDA      \n";
        $texto .= "================================\n";

        // Dados da venda / cancelamento
        $texto .= "Nº VENDA: {$dadosCupom['venda']['numero']}\n";
        $texto .= "DATA CANCELAMENTO: {$dadosCupom['cancelamento']['data']}\n";
        if (!empty($dadosCupom['venda']['cliente'])) {
            $texto .= "CLIENTE: {$dadosCupom['venda']['cliente']}\n";
        }
        $texto .= "--------------------------------\n";

        // Dados fiscais
        if (!empty($dadosCupom['nfe']['chave_acesso'])) {
            $texto .= "CHAVE ACESSO: {$dadosCupom['nfe']['chave_acesso']}\n";
        }
        if (!empty($dadosCupom['nfe']['protocolo_cancelamento'])) {
            $texto .= "PROTOC. CANCELAMENTO: {$dadosCupom['nfe']['protocolo_cancelamento']}\n";
        }
        if (!empty($dadosCupom['cancelamento']['motivo'])) {
            $texto .= "JUSTIFICATIVA: {$dadosCupom['cancelamento']['motivo']}\n";
        }
        $texto .= "--------------------------------\n";

        $texto .= "ESTA VENDA FOI CANCELADA\n";
        $texto .= "SERVIÇO ANULADO PELO EMITENTE\n";
        $texto .= "================================\n\n\n";

        return $texto;
    }

    public function search()
    {
        $users = User::orderBy('name')->get(); // ajuste o campo nome se for diferente
        // formas de pagamento — ajuste se você tiver uma tabela ou consts
        $formas = [
            'DINHEIRO' => 'Dinheiro',
            'CARTAO' => 'Cartão',
            'PIX' => 'PIX',
            'OUTRO' => 'Outro',
        ];

        return view('app.venda.search', [
            'users' => $users,
            'formas' => $formas,
        ]);
    }

    // Resultado da busca (lista de vendas no período)
    public function getByPeriod(Request $request)
    {
        $request->validate([
            'data_inicio' => 'required|date',
            'data_fim' => 'required|date|after_or_equal:data_inicio',
            'usuario_uuid' => 'nullable|string',
            'forma_pagamento' => 'nullable|string',
        ]);

        $dataInicio = $request->data_inicio . ' 00:00:00';
        $dataFim = $request->data_fim . ' 23:59:59';

        $query = Venda::with(['usuario', 'itens'])
            ->whereBetween('data_venda', [$dataInicio, $dataFim]);

        if ($request->usuario_uuid) {
            $query->where('usuario_uuid', $request->usuario_uuid);
        }

        if ($request->forma_pagamento) {
            $query->where('forma_pagamento', $request->forma_pagamento);
        }

        $vendas = $query->orderBy('data_venda', 'asc')->get();

        // Para cada venda, calcular subtotal exato somando os subtotais dos itens (registro a registro)
        $vendas = $vendas->map(function ($venda) {
            $subtotal = (float) $venda->itens->sum(function ($i) {
                return (float) $i->subtotal;
            });

            // opcional: formato de exibição da data, se quiser
            $venda->calculated_subtotal = $subtotal;
            return $venda;
        });

        // totalGeral = soma dos subtotais calculados de cada venda
        $totalGeral = $vendas->sum(function ($v) {
            return (float) $v->calculated_subtotal;
        });

        return view('app.venda.show-by-period', [
            'vendas' => $vendas,
            'data_inicio' => $request->data_inicio,
            'data_fim' => $request->data_fim,
            'usuario_uuid' => $request->usuario_uuid,
            'forma_pagamento' => $request->forma_pagamento,
            'totalGeral' => $totalGeral,
        ]);
    }

    // Exportar PDF com a mesma lógica
    public function exportPdf(Request $request)
    {
        $request->validate([
            'data_inicio' => 'required|date',
            'data_fim' => 'required|date|after_or_equal:data_inicio',
            'usuario_uuid' => 'nullable|string',
            'forma_pagamento' => 'nullable|string',
        ]);

        $dataInicio = $request->data_inicio . ' 00:00:00';
        $dataFim = $request->data_fim . ' 23:59:59';

        $query = Venda::with(['usuario', 'itens'])
            ->whereBetween('data_venda', [$dataInicio, $dataFim]);

        if ($request->usuario_uuid) {
            $query->where('usuario_uuid', $request->usuario_uuid);
        }

        if ($request->forma_pagamento) {
            $query->where('forma_pagamento', $request->forma_pagamento);
        }

        $vendas = $query->orderBy('data_venda', 'asc')->get();

        $vendas = $vendas->map(function ($venda) {
            $subtotal = (float) $venda->itens->sum(function ($i) {
                return (float) $i->subtotal;
            });
            $venda->calculated_subtotal = $subtotal;
            return $venda;
        });

        $totalGeral = $vendas->sum(function ($v) {
            return (float) $v->calculated_subtotal;
        });

        $pdf = Pdf::loadView('app.venda.pdf', [
            'vendas' => $vendas,
            'data_inicio' => $request->data_inicio,
            'data_fim' => $request->data_fim,
            'usuario_uuid' => $request->usuario_uuid,
            'forma_pagamento' => $request->forma_pagamento,
            'totalGeral' => $totalGeral,
        ]);

        return $pdf->download('relatorio-vendas-'.date('Ymd_His').'.pdf');
    }
}