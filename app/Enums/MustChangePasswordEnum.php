<?php declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static YES()
 * @method static static NO()
 */
final class MustChangePasswordEnum extends Enum
{
    const YES = '1';
    const NO = '0';
}
