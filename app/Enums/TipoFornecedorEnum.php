<?php declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static Livraria()
 * @method static static Insumos()
 */
final class TipoFornecedorEnum extends Enum
{
    const Livraria = 'Livraria';
    const Insumos = 'Insumos para cozinha';
}
