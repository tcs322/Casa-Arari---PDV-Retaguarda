<?php declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static ADMIN()
 * @method static static OPERADOR()
 */
final class TipoUsuarioEnum extends Enum
{
    const ADMIN = 'ADMIN';
    const OPERADOR = 'OPERADOR';
}
