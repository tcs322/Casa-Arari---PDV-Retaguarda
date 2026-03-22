<?php

namespace App\Actions\Venda;

use App\DTO\Venda\VendaShowDTO;
use App\Models\Venda;
use App\Repositories\Interfaces\PaginationInterface;
use App\Repositories\Venda\VendaEloquentRepository;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class VendaAction
{
    public function __construct(
        protected VendaEloquentRepository $repository
    ) {}

    public function paginate(int $page = 1, int $totalPerPage = 15, string $filter = null): PaginationInterface
    {
        return $this->repository->paginate(page: $page, totalPerPage: $totalPerPage, filter: $filter,
        );
    }

    public function show(VendaShowDTO $dto): Venda
    {
        return $this->repository->find($dto->uuid);
    }

    public function reimprimirNota(int $vendaId, string $texto, bool $contingencia = false, string $impressora = '71840'): bool
    {
        try {
            $response = Http::timeout(5)->post('http://host.docker.internal:8050/api/imprimir-direto', [
                'texto' => $texto,
                'venda_id' => $vendaId,
                'impressora' => $impressora,
            ]);

            if ($response->successful() && $response->json('success')) {
                Log::info("✅ Cupom enviado para impressão venda #{$vendaId}" . ($contingencia ? " (Contingência)" : ""));
                return true;
            } else {
                Log::warning("❌ Falha na impressão venda #{$vendaId}", [
                    'response' => $response->body(),
                    'contingencia' => $contingencia
                ]);
                return false;
            }
        } catch (\Exception $e) {
            Log::error("❌ Erro ao imprimir venda #{$vendaId}", [
                'error' => $e->getMessage(),
                'contingencia' => $contingencia
            ]);
            return false;
        }
    }

    public function gerarTextoReimpressaoCupom(array $dadosCupom, bool $contingencia = false): string
    {
        $texto = "";

        // Carrega dados do emitente do config/nfe.php
        $nfeConfig = config('nfe');

        $razaoSocial = $nfeConfig['razao_social'] ?? 'EMPRESA';
        $nomeFantasia = $nfeConfig['nome_fantasia'] ?? '';
        $cnpj = $nfeConfig['cnpj'] ?? '';
        $ie = $nfeConfig['ie'] ?? '';
        $logradouro = $nfeConfig['logradouro'] ?? '';
        $numero = $nfeConfig['numero'] ?? '';
        $bairro = $nfeConfig['bairro'] ?? '';
        $municipio = $nfeConfig['municipio'] ?? '';
        $uf = $nfeConfig['uf'] ?? '';
        $cep = $nfeConfig['cep'] ?? '';
        $telefone = $nfeConfig['telefone'] ?? '';

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
        $texto .= "          CUPOM FISCAL          \n";
        $texto .= "================================\n";

        // Dados da venda
        $texto .= "Nº VENDA: {$dadosCupom['venda']['numero']}\n";
        $texto .= "DATA: {$dadosCupom['venda']['data']}\n";
        if (!empty($dadosCupom['venda']['cliente'])) {
            $texto .= "CLIENTE: {$dadosCupom['venda']['cliente']}\n";
        }
        if (!empty($dadosCupom['venda']['cpf_cnpj'])) {
            $texto .= "CPF/CNPJ: {$dadosCupom['venda']['cpf_cnpj']}\n";
        }
        $texto .= "--------------------------------\n";
        $texto .= "CÓD  DESCRIÇÃO           QTD  VL UN  VL TOT\n";
        $texto .= "--------------------------------\n";

        // Itens
        foreach ($dadosCupom['itens'] as $i => $item) {
            $codigo = str_pad($item['codigo'] ?? ($i + 1), 4, '0', STR_PAD_LEFT);
            $descricao = mb_strimwidth($item['descricao'], 0, 18, '');
            $qtd = number_format($item['quantidade'], 2, ',', '');
            $vlUnit = number_format($item['valor_unitario'], 2, ',', '');
            $vlTotal = number_format($item['valor_total'], 2, ',', '');

            $texto .= sprintf("%-4s %-18s %4s %6s %7s\n",
                $codigo, $descricao, $qtd, $vlUnit, $vlTotal
            );
        }

        $texto .= "--------------------------------\n";

        // Totais
        $subtotal = number_format($dadosCupom['totais']['subtotal'], 2, ',', '');
        $desconto = number_format($dadosCupom['totais']['desconto'], 2, ',', '');
        $total = number_format($dadosCupom['totais']['total'], 2, ',', '');

        $texto .= "SUBTOTAL.............: R$ {$subtotal}\n";
        if ($dadosCupom['totais']['desconto'] > 0) {
            $texto .= "DESCONTO.............: R$ {$desconto}\n";
        }
        $texto .= "================================\n";
        $texto .= "TOTAL A PAGAR........: R$ {$total}\n";
        $texto .= "================================\n";

        // Pagamentos
        $texto .= "FORMA(S) DE PAGAMENTO:\n";
        foreach ($dadosCupom['pagamentos'] as $pagamento) {
            $valor = number_format($pagamento['valor'], 2, ',', '');
            $texto .= "  {$pagamento['forma']}: R$ {$valor}\n";
        }

        $texto .= "--------------------------------\n";

        // NFC-e / Dados fiscais
        if (!empty($dadosCupom['nfe']['numero']) && $dadosCupom['nfe']['numero'] !== 'N/A') {
            $texto .= "NFC-e Nº: {$dadosCupom['nfe']['numero']}\n";
            $texto .= "SÉRIE: {$dadosCupom['nfe']['serie']}\n";
            if (!empty($dadosCupom['nfe']['protocolo']) && $dadosCupom['nfe']['protocolo'] !== 'N/A') {
                $texto .= "PROTOCOLO: {$dadosCupom['nfe']['protocolo']}\n";
            }
        }

        // Contingência / Homologação
        if ($contingencia) {
            $texto .= "\n*** EMITIDA EM CONTINGÊNCIA ***\n";
            $texto .= "SEM COMUNICAÇÃO COM A SEFAZ\n";
        }

        if (!empty($nfeConfig['ambiente']) && $nfeConfig['ambiente'] == 2) {
            $texto .= "\n*** AMBIENTE DE HOMOLOGAÇÃO ***\n";
        }

        $texto .= "\n--------------------------------\n";

        // QR Code (texto substituto)
        if (!empty($dadosCupom['nfe']['qrcode'])) {
            $texto .= "Consulta via QR Code:\n";
            $texto .= "{$dadosCupom['nfe']['qrcode']}\n";
        }

        $texto .= "\n--------------------------------\n";
        $texto .= "OBRIGADO PELA PREFERÊNCIA!\n";
        $texto .= "Volte sempre :)\n";
        $texto .= "================================\n\n\n";

        return $texto;
    }

    public function getDadosImpressao(Venda $venda)
    {
        return [
            'success' => true,
            'print_data' => [
                'empresa' => [
                    'nome' => config('nfe.razao_social', 'Casa Arari LTDA'),
                    'endereco' => config('nfe.logradouro', 'Rua Exemplo, nfe.numero'),
                    'cidade' => config('nfe.municipio', 'Belém/PA'),
                    'cnpj' => config('nfe.cnpj', '00.000.000/0001-00'),
                    'telefone' => config('nfe.telefone', '(91) 9999-9999')
                ],
                'venda' => [
                    'numero' => $venda->id,
                    'uuid' => $venda->uuid,
                    'data' => $venda->created_at->format('d/m/Y H:i:s'),
                    'cliente' => $venda->cliente ? $venda->cliente->nome : 'CONSUMIDOR FINAL',
                    'cpf_cnpj' => $venda->cliente ? $venda->cliente->cpf : '',
                ],
                'itens' => $venda->itens->map(function($item) {
                    return [
                        'descricao' => $item->produto->nome_titulo,
                        'quantidade' => $item->quantidade,
                        'valor_unitario' => $item->preco_unitario,
                        'valor_total' => $item->preco_total
                    ];
                }),
                'totais' => [
                    'subtotal' => $venda->valor_total, // Ou calcule o subtreal se tiver desconto
                    'desconto' => 0, // Ajuste conforme sua lógica
                    'total' => $venda->valor_total
                ],
                'pagamentos' => [[
                    'forma' => $venda->forma_pagamento,
                    'valor' => $venda->valor_total
                ]],
                'contingencia' => request('contingencia', false),
                'nfe' => [
                    'numero' => $venda->numero_nota_fiscal,
                    'serie' => $venda->serie_nfe,
                    'chave' => $venda->chave_acesso // Se tiver este campo
                ]
            ]
        ];
    }
}