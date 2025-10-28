<?php

namespace App\Traits\Nota;

use App\Models\Venda;

trait NFeGenerateSerie
{
    /**
     * Retorna a série padrão para NF-e
     * 
     * @return string
     */
    public function seriePadrao(): string
    {
        return '1'; // Série principal
    }

    /**
     * Retorna a série formatada com 3 dígitos para chave de acesso
     * 
     * @param string $serie
     * @return string
     */
    private function serieFormatada(string $serie = null): string
    {
        $serie = $serie ?? $this->seriePadrao();
        return str_pad($serie, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Determina a série apropriada baseada no tipo de venda
     * 
     * @param Venda $venda
     * @return string
     */
    private function determinarSerie(Venda $venda = null): string
    {
        // Lógica para determinar a série baseada na venda
        if ($venda) {
            // Exemplos de lógica condicional:
            
            // if ($venda->tipo === 'devolucao') {
            //     return '2'; // Série para devoluções
            // }
            
            // if ($venda->filial_id === 2) {
            //     return '2'; // Segunda filial
            // }
            
            // if ($venda->canal_venda === 'online') {
            //     return '3'; // Vendas online
            // }
        }

        return $this->seriePadrao(); // Série padrão
    }

    /**
     * Retorna todas as séries disponíveis no sistema
     * 
     * @return array
     */
    private function seriesDisponiveis(): array
    {
        return [
            '1' => 'Série Principal',
            '2' => 'Série Secundária',
            '3' => 'Série Terciária',
            // '900' => 'Contingência SCAN',
            // '901' => 'Contingência DPEC', 
            // '902' => 'Contingência FS-DA'
        ];
    }

    /**
     * Verifica se uma série é válida
     * 
     * @param string $serie
     * @return bool
     */
    private function serieValida(string $serie): bool
    {
        $series = $this->seriesDisponiveis();
        return isset($series[$serie]) && strlen($serie) <= 3;
    }

    /**
     * Retorna a próxima série disponível para contingência
     * 
     * @return string
     */
    private function serieContingencia(): string
    {
        // Série 900+ para contingência
        return '900';
    }

    /**
     * Retorna séries utilizadas no sistema
     * 
     * @return array
     */
    private function seriesUtilizadas(): array
    {
        return Venda::whereNotNull('serie_nfe')
                   ->select('serie_nfe')
                   ->distinct()
                   ->pluck('serie_nfe')
                   ->toArray();
    }

    /**
     * Valida se pode usar uma série específica
     * 
     * @param string $serie
     * @return bool
     */
    private function podeUsarSerie(string $serie): bool
    {
        return $this->serieValida($serie) && in_array($serie, array_keys($this->seriesDisponiveis()));
    }

    /**
     * Retorna a descrição de uma série
     * 
     * @param string $serie
     * @return string
     */
    private function descricaoSerie(string $serie): string
    {
        $series = $this->seriesDisponiveis();
        return $series[$serie] ?? 'Série Desconhecida';
    }
}