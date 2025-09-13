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
final class BandeiraCartaoEnum extends Enum
{
    const VISA = "VISA";
    const MASTERCARD = "MASTERCARD";
    const AMEX = "AMEX";
    const ELO = "ELO";
    const OTHER = "OTHER";
}
