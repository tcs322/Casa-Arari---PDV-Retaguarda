<?php declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static ARTESANAL()
 * @method static static INDUSTRIAL()
 */
final class TipoProducaoProdutoEnum extends Enum
{
    const ARTESANAL = 'ARTESANAL';
    const INDUSTRIAL = 'INDUSTRIAL';
}
