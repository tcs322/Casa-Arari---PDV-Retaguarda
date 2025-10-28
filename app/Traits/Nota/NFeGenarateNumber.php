<?php

namespace App\Traits\Nota;

use App\Models\Venda;

trait NFeGenerateNumber
{
    /**
     * Retorna o próximo número disponível para NF-e
     * Baseado no último número de nota fiscal utilizado
     * 
     * @return int
     */
    public function proximoNumeroNota(): int
    {
        $ultimaNFe = Venda::whereNotNull('numero_nota_fiscal')
                          ->orderBy('created_at', 'desc')
                          ->first();
        
        return $ultimaNFe ? intval($ultimaNFe->numero_nota_fiscal) + 1 : 1;
    }

    /**
     * Retorna o número formatado com 9 dígitos para chave de acesso
     * 
     * @param int $numero
     * @return string
     */
    private function numeroFormatado(int $numero): string
    {
        return str_pad($numero, 9, '0', STR_PAD_LEFT);
    }

    /**
     * Verifica se um número específico está disponível para uso
     * 
     * @param int $numero
     * @param string $serie
     * @return bool
     */
    private function numeroDisponivel(int $numero, string $serie = '1'): bool
    {
        return !Venda::where('serie_nfe', $serie)
                    ->where('numero_nfe', $numero)
                    ->exists();
    }

    /**
     * Retorna o último número utilizado para uma série específica
     * 
     * @param string $serie
     * @return int
     */
    private function ultimoNumeroPorSerie(string $serie = '1'): int
    {
        $ultimaNFe = Venda::where('serie_nfe', $serie)
                         ->whereNotNull('numero_nfe')
                         ->orderBy('numero_nfe', 'desc')
                         ->first();

        return $ultimaNFe ? intval($ultimaNFe->numero_nfe) : 0;
    }

    /**
     * Valida se o número está dentro do limite permitido
     * 
     * @param int $numero
     * @return bool
     */
    private function numeroValido(int $numero): bool
    {
        return $numero >= 1 && $numero <= 999999999;
    }
}