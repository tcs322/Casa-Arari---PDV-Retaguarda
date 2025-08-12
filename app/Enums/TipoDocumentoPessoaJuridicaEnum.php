<?php declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static CPF()
 * @method static static CNPJ()
 * @method static static ESTRANGEIRO()
 */
final class TipoDocumentoPessoaJuridicaEnum extends Enum
{
    const CPF = 'CPF';
    const CNPJ = 'CNPJ';
    const ESTRANGEIRO = 'Estrangeiro';
}
