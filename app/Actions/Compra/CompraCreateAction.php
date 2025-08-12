<?php

namespace App\Actions\Compra;

use App\Repositories\Leilao\LeilaoRepositoryInterface;
use App\Repositories\Lote\LoteRepositoryInterface;

class CompraCreateAction
{
    protected $leilaoRepository;
    protected $loteRepository;

    public function __construct(
        LeilaoRepositoryInterface $leilaoRepository,
        LoteRepositoryInterface $loteRepository
    )
    {
        $this->loteRepository = $loteRepository;
        $this->leilaoRepository = $leilaoRepository;
    }

    public function execute(string $loteUuid) : array
    {
        $lote = $this->loteRepository->find($loteUuid);
        $leilao = $this->leilaoRepository->find($lote->leilao_uuid);

        return [
            'leilao' => $leilao,
            'lote' => $lote
        ];
    }
}
