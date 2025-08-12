<?php

namespace App\Charts;

use App\Models\Leilao;
use ArielMejiaDev\LarapexCharts\LarapexChart;

class LotePrelanceRadial
{
    protected $chart;

    public function __construct(LarapexChart $chart)
    {
        $this->chart = $chart;
    }

    public function build(Leilao $leilao): \ArielMejiaDev\LarapexCharts\RadarChart
    {
        $lotes = $leilao->lotes()->get();

        return $this->chart->radarChart()
            ->addData('Quantidade de lances', $lotes->pluck('quantidade_lances')->toArray())
            ->setHeight(700)
            ->setXAxis($lotes->pluck('id')->toArray())
            ->setMarkers(['#303F9F'], 7, 10);
    }
}
