<?php declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static LIVRARIA()
 * @method static static CAFETERIA()
 * @method static static PAPELARIA()
 */
final class TipoProdutoEnum extends Enum
{
    const LIVRARIA = 'LIVRARIA';
    const CAFETERIA = "CAFETERIA";
    const PAPELARIA = 'PAPELARIA';
}
