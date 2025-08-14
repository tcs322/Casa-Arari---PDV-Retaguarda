<?php declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static YES()
 * @method static static NO()
 */
final class MustChangePasswordEnum extends Enum
{
    const YES = true;
    const NO = false;
}
