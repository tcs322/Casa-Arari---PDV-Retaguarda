<?php declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static MACHO()
 * @method static static FEMEA()
 * @method static static OUTRO()
 */
final class GeneroLoteItemEnum extends Enum
{
    const MACHO = 1; // Macho
    const FEMEA = 2; // Fêmea
    const OUTRO = 3; // Outro
}
