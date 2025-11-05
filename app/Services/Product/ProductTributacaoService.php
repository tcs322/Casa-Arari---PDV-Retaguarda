<?php

namespace App\Services\Product;

use App\DTO\Product\ProductStoreDTO;
use App\Enums\TipoProducaoProdutoEnum;
use App\Enums\TipoProdutoEnum;

class ProductTributacaoService
{
    /**
     * Aplica tributação automática ao DTO
     */
    public function aplicarTributacaoAutomatica(ProductStoreDTO $dto): ProductStoreDTO
    {
        // Se já tem dados fiscais fornecidos, retorna o DTO original
        if ($this->temDadosFiscaisFornecidos($dto)) {
            return $dto;
        }

        // Aplica tributação automática baseada no tipo e produção
        $tributacaoAuto = $this->definirTributacaoAutomatica(
            $dto->tipo,
            $dto->tipo_producao
        );

        // Cria nova instância do DTO com os dados fiscais aplicados
        return new ProductStoreDTO(
            $dto->codigo,
            $dto->nome_titulo,
            $dto->preco_compra,
            $dto->preco_venda,
            $dto->estoque,
            $dto->autor,
            $dto->edicao,
            $dto->tipo,
            $dto->tipo_producao,
            $dto->nota_uuid,
            $dto->fornecedor_uuid,
            $tributacaoAuto['ncm'] ?? $dto->ncm,
            $tributacaoAuto['cest'] ?? $dto->cest,
            $tributacaoAuto['codigo_barras'] ?? $dto->codigo_barras,
            $tributacaoAuto['unidade_medida'] ?? $dto->unidade_medida,
            $tributacaoAuto['aliquota_icms'] ?? $dto->aliquota_icms,
            $tributacaoAuto['cst_icms'] ?? $dto->cst_icms,
            $tributacaoAuto['cst_pis'] ?? $dto->cst_pis,
            $tributacaoAuto['cst_cofins'] ?? $dto->cst_cofins,
            $tributacaoAuto['cfop'] ?? $dto->cfop,
            $tributacaoAuto['origem'] ?? $dto->origem
        );
    }

    /**
     * Verifica se o DTO já tem dados fiscais fornecidos manualmente
     */
    private function temDadosFiscaisFornecidos(ProductStoreDTO $dto): bool
    {
        return !empty($dto->ncm) && !empty($dto->cst_icms);
    }

    /**
     * Define tributação automática baseada no tipo e produção
     */
    private function definirTributacaoAutomatica(string $tipo, ?string $tipoProducao = null): array
    {
        return match($tipo) {
            TipoProdutoEnum::LIVRARIA => $this->getTributacaoLivraria(),
            TipoProdutoEnum::CAFETERIA => $this->getTributacaoCafeteria($tipoProducao),
            TipoProdutoEnum::PAPELARIA => $this->getTributacaoPapelaria(),
            default => $this->getTributacaoPadrao()
        };
    }

    /**
     * Tributação para produtos de livraria
     */
    private function getTributacaoLivraria(): array
    {
        return [
            'ncm' => '49019900',
            'cest' => '2800300',
            'codigo_barras' => null,
            'unidade_medida' => 'UN',
            'aliquota_icms' => 0.0,
            'cst_icms' => '00',
            'cst_pis' => '07',
            'cst_cofins' => '07',
            'cfop' => '5102',
            'origem' => '0'
        ];
    }

    /**
     * Tributação para produtos de cafeteria
     */
    private function getTributacaoCafeteria(?string $tipoProducao): array
    {
        $base = [
            'codigo_barras' => null,
            'unidade_medida' => 'UN',
            'cfop' => '5102',
            'origem' => '0'
        ];

        // Define com base no tipo de produção
        return match($tipoProducao) {
            TipoProducaoProdutoEnum::ARTESANAL => array_merge($base, [
                'ncm' => '19059000', // Produtos de padaria/pastelaria
                'cest' => '0400300',
                'aliquota_icms' => 0.0, // Isenta para artesanal
                'cst_icms' => '40', // Isenta
                'cst_pis' => '07', // Isenta
                'cst_cofins' => '07' // Isenta
            ]),
            
            TipoProducaoProdutoEnum::INDUSTRIAL => array_merge($base, [
                'ncm' => '09012100', // Café torrado
                'cest' => '0300800',
                'aliquota_icms' => 17.0,
                'cst_icms' => '00', // Tributada integralmente
                'cst_pis' => '01', // Tributada
                'cst_cofins' => '01' // Tributada
            ]),
            
            default => array_merge($base, [
                'ncm' => '19059000', // Default para alimentos
                'cest' => '0400300',
                'aliquota_icms' => 17.0,
                'cst_icms' => '00',
                'cst_pis' => '01',
                'cst_cofins' => '01'
            ])
        };
    }

    /**
     * Tributação para papelaria
     */
    private function getTributacaoPapelaria(): array
    {
        return [
            'ncm' => '48201000',
            'cest' => '2805300',
            'codigo_barras' => null,
            'unidade_medida' => 'UN',
            'aliquota_icms' => 17.0,
            'cst_icms' => '00',
            'cst_pis' => '01',
            'cst_cofins' => '01',
            'cfop' => '5102',
            'origem' => '0'
        ];
    }

    /**
     * Tributação padrão (fallback)
     */
    private function getTributacaoPadrao(): array
    {
        return [
            'ncm' => '49019900',
            'cest' => '2800300',
            'codigo_barras' => null,
            'unidade_medida' => 'UN',
            'aliquota_icms' => 17.0,
            'cst_icms' => '00',
            'cst_pis' => '01',
            'cst_cofins' => '01',
            'cfop' => '5102',
            'origem' => '0'
        ];
    }

    /**
     * Método auxiliar para obter descrição da tributação (para logs ou debug)
     */
    public function getDescricaoTributacao(string $tipo, ?string $tipoProducao = null): string
    {
        return match($tipo) {
            TipoProdutoEnum::LIVRARIA => 'Livro - Isento de ICMS, PIS e COFINS',
            TipoProdutoEnum::CAFETERIA => match($tipoProducao) {
                TipoProducaoProdutoEnum::ARTESANAL => 'Cafeteria Artesanal - Isento',
                TipoProducaoProdutoEnum::INDUSTRIAL => 'Cafeteria Industrial - Tributado',
                default => 'Cafeteria - Tributação padrão'
            },
            TipoProdutoEnum::PAPELARIA => 'Papelaria - Tributado integralmente',
            default => 'Tributação padrão'
        };
    }

    /**
     * Valida se a tributação aplicada está consistente
     */
    public function validarTributacao(ProductStoreDTO $dto): array
    {
        $erros = [];

        // Valida NCM
        if (empty($dto->ncm)) {
            $erros[] = 'NCM não definido';
        } elseif (strlen($dto->ncm) !== 8) {
            $erros[] = 'NCM deve ter 8 dígitos';
        }

        // Valida CST ICMS
        if (empty($dto->cst_icms)) {
            $erros[] = 'CST ICMS não definido';
        }

        // Valida consistência entre alíquota e CST
        if ($dto->aliquota_icms == 0 && !in_array($dto->cst_icms, ['40', '41', '50'])) {
            $erros[] = 'CST ICMS incompatível com alíquota zero';
        }

        // Valida CEST para produtos que exigem
        if (in_array($dto->ncm, ['49019900', '09012100', '19059000']) && empty($dto->cest)) {
            $erros[] = 'CEST recomendado para o NCM informado';
        }

        return [
            'valido' => empty($erros),
            'erros' => $erros
        ];
    }

    /**
     * ✅ NOVO MÉTODO PÚBLICO: Aplica tributação automática a um array de dados
     */
    public function aplicarTributacaoArray(array $dadosProduto): array
    {
        // Se já tem dados fiscais fornecidos, retorna os dados originais
        if ($this->temDadosFiscaisFornecidosArray($dadosProduto)) {
            return $dadosProduto;
        }

        // Aplica tributação automática
        $tributacaoAuto = $this->definirTributacaoAutomatica(
            $dadosProduto['tipo'],
            $dadosProduto['tipo_producao'] ?? null
        );

        return array_merge($dadosProduto, $tributacaoAuto);
    }

    /**
     * ✅ Verifica se o array já tem dados fiscais fornecidos
     */
    private function temDadosFiscaisFornecidosArray(array $dados): bool
    {
        return !empty($dados['ncm']) && !empty($dados['cst_icms']);
    }
}