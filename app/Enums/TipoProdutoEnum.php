<?php declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static AGENDA()
 * @method static static CADERNO()
 */
final class TipoProdutoEnum extends Enum
{
    const AGENDA = 'AGENDA';
    const CADERNO = "CADERNO";
}
