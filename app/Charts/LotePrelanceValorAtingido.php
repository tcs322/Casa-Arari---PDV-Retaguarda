<?php

namespace App\Charts;

use App\Models\Leilao;
use App\Models\Lote;
use ArielMejiaDev\LarapexCharts\LarapexChart;

class LotePrelanceValorAtingido
{
    protected $chart;

    public function __construct(LarapexChart $chart)
    {
        $this->chart = $chart;
    }

    public function build(Leilao $leilao): \ArielMejiaDev\LarapexCharts\HorizontalBar
    {
        $lotes = $leilao->lotes()->get();

        return $this->chart->horizontalBarChart()
            ->addData('Valor Estimado', $lotes->pluck('valor_estimado')->toArray())
            ->addData('Valor Atingido', $lotes->pluck('valor_prelance')->toArray())
            ->setHeight(900)
            ->setTitle('valor estimado x Valor atingido')
            ->setXAxis($lotes->pluck('id')->toArray());
    }
}
