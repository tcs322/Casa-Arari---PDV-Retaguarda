<?php declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static VISA()
 * @method static static MASTERCARD()
 * @method static static AMEX()
 * @method static static ELO()
 * @method static static OTHER()
 */
final class NumeroBandeiraCartaoEnum extends Enum
{
    const VISA = "01";
    const MASTERCARD = "02";
    const AMEX = "03";
    const ELO = "06";
    const OTHER = "99";
}
