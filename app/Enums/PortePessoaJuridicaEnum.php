<?php declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static ME()
 * @method static static EPP()
 * @method static static MEI()
 * @method static static SA()
 * @method static static EIRELI()
 */
final class PortePessoaJuridicaEnum extends Enum
{
    const ME = 'Empresa de Médio Porte';
    const EPP = 'Empresa de Pequeno Porte';
    const MEI = 'Microempreendedor Individual';
    const SA = 'Sociedade Anonima';
    const EIRELI = 'Eireli';
}
