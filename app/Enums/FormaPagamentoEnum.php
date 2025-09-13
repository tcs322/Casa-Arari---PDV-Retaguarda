<?php declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static DINHEIRO()
 * @method static static PIX()
 * @method static static CARTAO_CREDITO()
 * @method static static CARTAO_DEBITO()
 */
final class FormaPagamentoEnum extends Enum
{
    const DINHEIRO = 'DINHEIRO';
    const PIX = "PIX";
    const CARTAO_CREDITO = "CARTAO DE CRÉDITO";
    const CARTAO_DEBITO = "CARTAO DE DÉBITO";
}
